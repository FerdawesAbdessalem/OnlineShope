<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class ProductRepository
{
    public const PER_PAGE = 9;

    private const SELECT = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon
                            FROM products p JOIN categories c ON c.id = p.category_id';

    private const SORTS = [
        'new' => 'p.created_at DESC, p.id DESC',
        'price_asc' => 'p.price_cents ASC',
        'price_desc' => 'p.price_cents DESC',
        'name' => 'p.name ASC',
    ];

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function find(int $id, bool $onlyActive = true): ?array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE p.id = ?' . ($onlyActive ? ' AND p.active = 1' : ''));
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** @return array<int,array> rows indexed by id */
    public function findMany(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare(self::SELECT . " WHERE p.id IN ($placeholders) AND p.active = 1");
        $stmt->execute($ids);
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[(int) $row['id']] = $row;
        }
        return $rows;
    }

    public function featured(int $limit = 4): array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE p.active = 1 AND p.featured = 1 ORDER BY p.id LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Catalogue search with filters, sorting and pagination.
     *
     * @return array{items: array, total: int, pages: int, page: int}
     */
    public function search(?string $query, ?int $categoryId, string $sort = 'new', int $page = 1, bool $inStockOnly = false): array
    {
        $where = ['p.active = 1'];
        $params = [];
        if ($query !== null && trim($query) !== '') {
            // Hyphens are ignored so that "wifi" also finds "Wi-Fi"; the category name is searched too.
            $where[] = "(REPLACE(LOWER(p.name), '-', '') LIKE ? OR LOWER(p.brand) LIKE ?
                         OR LOWER(p.description) LIKE ? OR p.category_id IN (SELECT id FROM categories WHERE REPLACE(LOWER(name), '-', '') LIKE ?))";
            $like = '%' . str_replace('-', '', mb_strtolower(trim($query))) . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if ($categoryId !== null) {
            $where[] = 'p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($inStockOnly) {
            $where[] = 'p.stock > 0';
        }
        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $count = $this->pdo->prepare('SELECT COUNT(*) FROM products p' . $whereSql);
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $page), $pages);

        $order = self::SORTS[$sort] ?? self::SORTS['new']; // whitelist: never interpolate user input
        $stmt = $this->pdo->prepare(self::SELECT . $whereSql . " ORDER BY $order LIMIT " . self::PER_PAGE . ' OFFSET ' . (($page - 1) * self::PER_PAGE));
        $stmt->execute($params);

        return ['items' => $stmt->fetchAll(), 'total' => $total, 'pages' => $pages, 'page' => $page];
    }

    /** Admin list (includes inactive products). */
    public function allForAdmin(): array
    {
        return $this->pdo->query(self::SELECT . ' ORDER BY p.active DESC, p.name')->fetchAll();
    }

    public function lowStock(int $threshold = 5): array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE p.active = 1 AND p.stock <= ? ORDER BY p.stock');
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }

    public function save(?int $id, array $data): int
    {
        $fields = [
            (int) $data['category_id'],
            trim($data['name']),
            trim($data['brand']),
            trim($data['description']),
            (int) $data['price_cents'],
            (int) $data['stock'],
            !empty($data['featured']) ? 1 : 0,
            !empty($data['active']) ? 1 : 0,
        ];
        if ($id === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO products (category_id, name, brand, description, price_cents, stock, featured, active, image)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([...$fields, $data['image'] ?? null]);
            return (int) $this->pdo->lastInsertId();
        }
        $sql = 'UPDATE products SET category_id = ?, name = ?, brand = ?, description = ?, price_cents = ?, stock = ?, featured = ?, active = ?';
        $params = $fields;
        if (array_key_exists('image', $data) && $data['image'] !== null) {
            $sql .= ', image = ?';
            $params[] = $data['image'];
        }
        $stmt = $this->pdo->prepare($sql . ' WHERE id = ?');
        $stmt->execute([...$params, $id]);
        return $id;
    }

    /** Products already ordered are deactivated instead of deleted (order history stays valid). */
    public function delete(int $id): void
    {
        $used = $this->pdo->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = ?');
        $used->execute([$id]);
        if ((int) $used->fetchColumn() > 0) {
            $this->pdo->prepare('UPDATE products SET active = 0 WHERE id = ?')->execute([$id]);
            return;
        }
        $this->pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    }
}
