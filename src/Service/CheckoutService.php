<?php

declare(strict_types=1);

namespace App\Service;

use App\Cart;
use App\Repository\ProductRepository;
use PDO;
use RuntimeException;

/**
 * Turns the cart into an order, atomically:
 * stock is re-checked and decremented in the same transaction, so two
 * customers can never buy the last item at the same time.
 */
final class CheckoutService
{
    public function __construct(private readonly PDO $pdo, private readonly ProductRepository $products)
    {
    }

    /**
     * @param array{name:string,address:string,city:string,phone:string} $shipping
     * @throws RuntimeException with a user-facing message
     */
    public function placeOrder(int $userId, Cart $cart, array $shipping): int
    {
        if ($cart->isEmpty()) {
            throw new RuntimeException('Votre panier est vide.');
        }

        $this->pdo->beginTransaction();
        try {
            $products = $this->products->findMany(array_keys($cart->items()));
            $lines = [];
            $total = 0;
            foreach ($cart->items() as $productId => $quantity) {
                $product = $products[$productId] ?? null;
                if (!$product) {
                    throw new RuntimeException('Un produit de votre panier n\'est plus disponible.');
                }
                if ((int) $product['stock'] < $quantity) {
                    throw new RuntimeException(sprintf('Stock insuffisant pour « %s » (%d disponible).', $product['name'], $product['stock']));
                }
                $lines[] = [$product, $quantity];
                $total += (int) $product['price_cents'] * $quantity;
            }

            $order = $this->pdo->prepare(
                'INSERT INTO orders (user_id, status, total_cents, shipping_name, shipping_address, shipping_city, phone)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $order->execute([$userId, 'pending', $total, $shipping['name'], $shipping['address'], $shipping['city'], $shipping['phone']]);
            $orderId = (int) $this->pdo->lastInsertId();

            $item = $this->pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, unit_price_cents, quantity) VALUES (?, ?, ?, ?, ?)'
            );
            // The WHERE clause makes the decrement fail if another order took the stock meanwhile.
            $decrement = $this->pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            foreach ($lines as [$product, $quantity]) {
                $item->execute([$orderId, $product['id'], $product['name'], $product['price_cents'], $quantity]);
                $decrement->execute([$quantity, $product['id'], $quantity]);
                if ($decrement->rowCount() !== 1) {
                    throw new RuntimeException(sprintf('« %s » vient d\'être épuisé.', $product['name']));
                }
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e instanceof RuntimeException ? $e : new RuntimeException('La commande n\'a pas pu être enregistrée.', 0, $e);
        }

        $cart->clear();
        return $orderId;
    }

    /** @return array<string,string> field => error message */
    public static function validateShipping(array $input): array
    {
        $errors = [];
        foreach (['name' => 'le nom', 'address' => 'l\'adresse', 'city' => 'la ville'] as $field => $label) {
            if (mb_strlen(trim((string) ($input[$field] ?? ''))) < 2) {
                $errors[$field] = "Veuillez indiquer $label.";
            }
        }
        if (!preg_match('/^\+?[0-9 ]{8,15}$/', trim((string) ($input['phone'] ?? '')))) {
            $errors['phone'] = 'Numéro de téléphone invalide.';
        }
        return $errors;
    }
}
