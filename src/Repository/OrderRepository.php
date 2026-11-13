<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class OrderRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, u.name AS customer_name, u.email AS customer_email
             FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?'
        );
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }
        $items = $this->pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
        $items->execute([$id]);
        $order['items'] = $items->fetchAll();
        return $order;
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, (SELECT SUM(quantity) FROM order_items i WHERE i.order_id = o.id) AS item_count
             FROM orders o WHERE o.user_id = ? ORDER BY o.id DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function all(?string $status = null): array
    {
        $sql = 'SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id = o.user_id';
        $params = [];
        if ($status !== null && $status !== '') {
            $sql .= ' WHERE o.status = ?';
            $params[] = $status;
        }
        $stmt = $this->pdo->prepare($sql . ' ORDER BY o.id DESC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
    }

    /** Revenue per day for the last $days days (oldest first), cancelled orders excluded. */
    public function dailyRevenue(int $days = 7): array
    {
        $start = (new \DateTimeImmutable('today'))->modify('-' . ($days - 1) . ' days');
        $stmt = $this->pdo->prepare("SELECT created_at, total_cents FROM orders WHERE status <> 'cancelled' AND created_at >= ?");
        $stmt->execute([$start->format('Y-m-d 00:00:00')]);
        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $series[$start->modify("+$i days")->format('Y-m-d')] = 0;
        }
        foreach ($stmt->fetchAll() as $row) {
            $day = substr((string) $row['created_at'], 0, 10);
            if (isset($series[$day])) {
                $series[$day] += (int) $row['total_cents'];
            }
        }
        return $series;
    }

    /** @return array<string,int> status => number of orders */
    public function countByStatus(): array
    {
        $counts = array_fill_keys(array_keys(ORDER_STATUSES), 0);
        foreach ($this->pdo->query('SELECT status, COUNT(*) AS n FROM orders GROUP BY status')->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['n'];
        }
        return $counts;
    }

    /** @return array{orders: int, revenue: int, pending: int} */
    public function stats(): array
    {
        $row = $this->pdo->query(
            "SELECT COUNT(*) AS orders,
                    COALESCE(SUM(CASE WHEN status <> 'cancelled' THEN total_cents ELSE 0 END), 0) AS revenue,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) AS pending
             FROM orders"
        )->fetch();
        return ['orders' => (int) $row['orders'], 'revenue' => (int) $row['revenue'], 'pending' => (int) $row['pending']];
    }
}
