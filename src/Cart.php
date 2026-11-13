<?php

declare(strict_types=1);

namespace App;

/**
 * Shopping cart: product id → quantity, kept in a storage array
 * (the session in the app, a plain array in the unit tests).
 */
final class Cart
{
    public const MAX_QUANTITY = 99;

    /** @var array<int,int> */
    private array $items;

    /** @param array<int,int> $items reference to the storage (e.g. $_SESSION['cart']) */
    public function __construct(array &$items)
    {
        $this->items = &$items;
    }

    public function add(int $productId, int $quantity = 1): void
    {
        if ($quantity < 1) {
            return;
        }
        $this->set($productId, ($this->items[$productId] ?? 0) + $quantity);
    }

    /** Sets the quantity; 0 removes the line. */
    public function set(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            unset($this->items[$productId]);
            return;
        }
        $this->items[$productId] = min($quantity, self::MAX_QUANTITY);
    }

    public function remove(int $productId): void
    {
        unset($this->items[$productId]);
    }

    public function clear(): void
    {
        $this->items = [];
    }

    /** @return array<int,int> */
    public function items(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return array_sum($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /**
     * Joins the cart with product rows and computes the lines and total.
     * Quantities are capped by the available stock.
     *
     * @param array<int,array> $products product rows indexed by id
     * @return array{lines: list<array>, total: int, adjusted: bool}
     */
    public function summary(array $products): array
    {
        $lines = [];
        $total = 0;
        $adjusted = false;
        foreach ($this->items as $id => $quantity) {
            $product = $products[$id] ?? null;
            if (!$product || (int) $product['stock'] <= 0) {
                $this->remove($id);
                $adjusted = true;
                continue;
            }
            if ($quantity > (int) $product['stock']) {
                $quantity = (int) $product['stock'];
                $this->set($id, $quantity);
                $adjusted = true;
            }
            $subtotal = (int) $product['price_cents'] * $quantity;
            $lines[] = ['product' => $product, 'quantity' => $quantity, 'subtotal' => $subtotal];
            $total += $subtotal;
        }
        return ['lines' => $lines, 'total' => $total, 'adjusted' => $adjusted];
    }
}
