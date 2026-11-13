<?php

declare(strict_types=1);

namespace Tests;

use App\Cart;
use App\Database;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Schema;
use App\Service\CheckoutService;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Integration tests against an in-memory SQLite database seeded with the demo catalogue. */
final class CheckoutServiceTest extends TestCase
{
    private PDO $pdo;
    private ProductRepository $products;
    private CheckoutService $checkout;
    private array $storage = [];
    private Cart $cart;
    private int $userId;

    private const SHIPPING = ['name' => 'Client Test', 'address' => '12 rue de la Paix', 'city' => 'Sousse', 'phone' => '+216 20 000 000'];

    protected function setUp(): void
    {
        $this->pdo = Database::connect('sqlite::memory:');
        Schema::create($this->pdo);
        Schema::seed($this->pdo, 'admin-pass', 'client-pass');
        $this->products = new ProductRepository($this->pdo);
        $this->checkout = new CheckoutService($this->pdo, $this->products);
        $this->storage = [];
        $this->cart = new Cart($this->storage);
        $this->userId = (int) $this->pdo->query("SELECT id FROM users WHERE role = 'customer'")->fetchColumn();
    }

    private function productWithStock(int $minStock): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE stock >= ? ORDER BY id LIMIT 1');
        $stmt->execute([$minStock]);
        return $stmt->fetch();
    }

    public function testPlacingAnOrderCreatesItemsDecrementsStockAndEmptiesTheCart(): void
    {
        $product = $this->productWithStock(3);
        $this->cart->add((int) $product['id'], 2);

        $orderId = $this->checkout->placeOrder($this->userId, $this->cart, self::SHIPPING);

        $order = (new OrderRepository($this->pdo))->find($orderId);
        self::assertSame('pending', $order['status']);
        self::assertSame(2 * (int) $product['price_cents'], (int) $order['total_cents']);
        self::assertCount(1, $order['items']);
        self::assertSame((int) $product['stock'] - 2, (int) $this->products->find((int) $product['id'])['stock']);
        self::assertTrue($this->cart->isEmpty());
    }

    public function testInsufficientStockRollsBackEverything(): void
    {
        $product = $this->productWithStock(1);
        $this->cart->add((int) $product['id'], (int) $product['stock'] + 1);

        try {
            $this->checkout->placeOrder($this->userId, $this->cart, self::SHIPPING);
            self::fail('An exception was expected');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('Stock insuffisant', $e->getMessage());
        }

        self::assertSame(0, (int) $this->pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn());
        self::assertSame((int) $product['stock'], (int) $this->products->find((int) $product['id'])['stock']);
        self::assertFalse($this->cart->isEmpty(), 'the cart is kept so the customer can fix it');
    }

    public function testShippingValidation(): void
    {
        self::assertSame([], CheckoutService::validateShipping(self::SHIPPING));
        $errors = CheckoutService::validateShipping(['name' => '', 'address' => 'x', 'city' => 'Tunis', 'phone' => 'abc']);
        self::assertArrayHasKey('name', $errors);
        self::assertArrayHasKey('address', $errors);
        self::assertArrayHasKey('phone', $errors);
    }

    public function testSearchIsSafeAgainstSqlInjectionAndPaginates(): void
    {
        $result = $this->products->search("' OR 1=1 --", null);
        self::assertSame(0, $result['total']);

        $all = $this->products->search(null, null, 'price_asc');
        self::assertGreaterThan(ProductRepository::PER_PAGE, $all['total']);
        self::assertCount(ProductRepository::PER_PAGE, $all['items']);
        self::assertLessThanOrEqual($all['items'][1]['price_cents'], $all['items'][0]['price_cents']);
    }
}
