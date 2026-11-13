<?php

declare(strict_types=1);

namespace Tests;

use App\Cart;
use PHPUnit\Framework\TestCase;

final class CartTest extends TestCase
{
    private array $storage = [];
    private Cart $cart;

    protected function setUp(): void
    {
        $this->storage = [];
        $this->cart = new Cart($this->storage);
    }

    public function testAddAccumulatesQuantities(): void
    {
        $this->cart->add(1, 2);
        $this->cart->add(1, 3);
        $this->cart->add(2);
        self::assertSame([1 => 5, 2 => 1], $this->cart->items());
        self::assertSame(6, $this->cart->count());
        self::assertSame([1 => 5, 2 => 1], $this->storage, 'the cart writes through to its storage (session)');
    }

    public function testSetZeroRemovesAndQuantityIsCapped(): void
    {
        $this->cart->set(1, 500);
        self::assertSame(Cart::MAX_QUANTITY, $this->cart->items()[1]);
        $this->cart->set(1, 0);
        self::assertTrue($this->cart->isEmpty());
    }

    public function testSummaryComputesTotalAndAdjustsToStock(): void
    {
        $this->cart->add(1, 3);
        $this->cart->add(2, 10);
        $this->cart->add(3, 1); // product no longer exists

        $summary = $this->cart->summary([
            1 => ['id' => 1, 'price_cents' => 1000, 'stock' => 10],
            2 => ['id' => 2, 'price_cents' => 250, 'stock' => 4],
        ]);

        self::assertSame(3 * 1000 + 4 * 250, $summary['total']);
        self::assertTrue($summary['adjusted']);
        self::assertSame([1 => 3, 2 => 4], $this->cart->items());
    }
}
