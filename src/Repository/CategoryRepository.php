<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class CategoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** Categories with the number of active products. */
    public function all(): array
    {
        return $this->pdo->query(
            'SELECT c.*, COUNT(p.id) AS product_count
             FROM categories c LEFT JOIN products p ON p.category_id = c.id AND p.active = 1
             GROUP BY c.id, c.name, c.slug, c.icon ORDER BY c.name'
        )->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE slug = ?');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $icon = 'box'): void
    {
        $slug = self::slugify($name);
        $stmt = $this->pdo->prepare('INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)');
        $stmt->execute([trim($name), $slug, $icon]);
    }

    public function delete(int $id): bool
    {
        $used = $this->pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $used->execute([$id]);
        if ((int) $used->fetchColumn() > 0) {
            return false; // keep products consistent: a used category cannot be deleted
        }
        $this->pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
        return true;
    }

    public static function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = strtr($text, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'â' => 'a', 'ç' => 'c', 'ô' => 'o', 'î' => 'i', 'û' => 'u']);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        return trim($text, '-') ?: 'categorie';
    }
}
