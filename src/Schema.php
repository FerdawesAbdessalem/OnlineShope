<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Creates the tables (MySQL or SQLite) and the demo catalogue.
 */
final class Schema
{
    public static function create(PDO $pdo): void
    {
        $mysql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
        $id = $mysql ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
        $engine = $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';
        $now = 'CURRENT_TIMESTAMP';

        $statements = [
            "CREATE TABLE IF NOT EXISTS users (
                id $id,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'customer',
                created_at TIMESTAMP NOT NULL DEFAULT $now
            )$engine",
            "CREATE TABLE IF NOT EXISTS categories (
                id $id,
                name VARCHAR(120) NOT NULL,
                slug VARCHAR(120) NOT NULL UNIQUE,
                icon VARCHAR(40) NOT NULL DEFAULT 'box'
            )$engine",
            "CREATE TABLE IF NOT EXISTS products (
                id $id,
                category_id INT NOT NULL,
                name VARCHAR(190) NOT NULL,
                brand VARCHAR(80) NOT NULL DEFAULT '',
                description TEXT NOT NULL,
                price_cents INT NOT NULL,
                stock INT NOT NULL DEFAULT 0,
                image VARCHAR(255) NULL,
                featured INT NOT NULL DEFAULT 0,
                active INT NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT $now,
                FOREIGN KEY (category_id) REFERENCES categories(id)
            )$engine",
            "CREATE TABLE IF NOT EXISTS orders (
                id $id,
                user_id INT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                total_cents INT NOT NULL,
                shipping_name VARCHAR(120) NOT NULL,
                shipping_address VARCHAR(255) NOT NULL,
                shipping_city VARCHAR(120) NOT NULL,
                phone VARCHAR(40) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT $now,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )$engine",
            "CREATE TABLE IF NOT EXISTS order_items (
                id $id,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                product_name VARCHAR(190) NOT NULL,
                unit_price_cents INT NOT NULL,
                quantity INT NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id)
            )$engine",
        ];
        foreach ($statements as $sql) {
            $pdo->exec($sql);
        }
    }

    /**
     * Demo orders spread over the last 7 days, so the back-office dashboard
     * shows a realistic sales chart. Only used by `php bin/setup.php --demo-orders`.
     */
    public static function seedDemoOrders(PDO $pdo): void
    {
        if ((int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn() > 0) {
            return;
        }
        $customer = (int) $pdo->query("SELECT id FROM users WHERE role = 'customer' ORDER BY id LIMIT 1")->fetchColumn();
        $products = $pdo->query('SELECT id, name, price_cents FROM products ORDER BY id')->fetchAll();
        $plan = [ // [days ago, status, [product index => quantity]]
            [6, 'delivered', [0 => 1, 9 => 4]], [6, 'delivered', [3 => 2]],
            [5, 'delivered', [6 => 1, 9 => 2]], [4, 'shipped', [4 => 1]],
            [4, 'delivered', [1 => 1]], [3, 'shipped', [7 => 1, 11 => 2]],
            [2, 'paid', [12 => 1]], [2, 'cancelled', [5 => 1]],
            [1, 'paid', [0 => 2, 3 => 1]], [0, 'pending', [6 => 2]], [0, 'pending', [13 => 1, 9 => 3]],
        ];
        $cities = ['Tunis', 'Sousse', 'Sfax', 'Monastir', 'Nabeul', 'Bizerte'];
        $order = $pdo->prepare('INSERT INTO orders (user_id, status, total_cents, shipping_name, shipping_address, shipping_city, phone, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $item = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price_cents, quantity) VALUES (?, ?, ?, ?, ?)');
        foreach ($plan as $n => [$daysAgo, $status, $lines]) {
            $total = 0;
            foreach ($lines as $index => $qty) {
                $total += (int) $products[$index]['price_cents'] * $qty;
            }
            $date = (new \DateTimeImmutable('today'))->modify("-$daysAgo days")->setTime(9 + $n % 9, 10 + $n * 3)->format('Y-m-d H:i:s');
            $order->execute([$customer, $status, $total, 'Client Démo', ($n + 3) . ' avenue de la République', $cities[$n % count($cities)], '+216 20 000 0' . str_pad((string) $n, 2, '0', STR_PAD_LEFT), $date]);
            $orderId = (int) $pdo->lastInsertId();
            foreach ($lines as $index => $qty) {
                $p = $products[$index];
                $item->execute([$orderId, $p['id'], $p['name'], $p['price_cents'], $qty]);
            }
        }
    }

    /** Demo catalogue + an admin and a customer account. */
    public static function seed(PDO $pdo, string $adminPassword, string $customerPassword): void
    {
        if ((int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() > 0) {
            return;
        }

        $categories = [
            ['Routeurs', 'routeurs', 'router'],
            ['Switches', 'switches', 'switch'],
            ['Wi-Fi', 'wifi', 'wifi'],
            ['Câbles & connectique', 'cables', 'cable'],
            ['Sécurité réseau', 'securite', 'shield'],
        ];
        $insertCategory = $pdo->prepare('INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)');
        $ids = [];
        foreach ($categories as [$name, $slug, $icon]) {
            $insertCategory->execute([$name, $slug, $icon]);
            $ids[$slug] = (int) $pdo->lastInsertId();
        }

        $products = [
            ['routeurs', 'Routeur VPN gigabit 5 ports', 'TP-Link', 'Routeur filaire avec VPN IPsec/OpenVPN, 5 ports gigabit, idéal pour les PME.', 18900, 14, 1],
            ['routeurs', 'Routeur 4G LTE double SIM', 'Huawei', 'Connexion de secours 4G, double SIM, basculement automatique en cas de coupure.', 32900, 6, 0],
            ['routeurs', 'Routeur d\'entreprise 8 ports', 'Cisco', 'Routage avancé, QoS, VLAN et gestion à distance pour les réseaux d\'entreprise.', 74900, 3, 1],
            ['switches', 'Switch 8 ports gigabit non manageable', 'Netgear', 'Boîtier métal, plug-and-play, silencieux (sans ventilateur).', 6900, 40, 0],
            ['switches', 'Switch PoE+ 16 ports manageable', 'Ubiquiti', '16 ports PoE+ (150 W), VLAN, supervision via contrôleur.', 41900, 5, 1],
            ['switches', 'Switch 24 ports rackable', 'D-Link', '24 ports gigabit + 4 SFP, montage en baie 19 pouces.', 28900, 8, 0],
            ['wifi', 'Point d\'accès Wi-Fi 6 plafonnier', 'Ubiquiti', 'Wi-Fi 6 double bande jusqu\'à 3 Gb/s, alimentation PoE.', 21900, 12, 1],
            ['wifi', 'Kit Wi-Fi mesh 3 unités', 'TP-Link', 'Couverture jusqu\'à 500 m², itinérance transparente entre les bornes.', 35900, 7, 0],
            ['wifi', 'Répéteur Wi-Fi AC1200', 'Tenda', 'Étend la couverture Wi-Fi existante, port Ethernet intégré.', 4900, 0, 0],
            ['cables', 'Câble Ethernet Cat6 – 5 m', 'Generic', 'Câble RJ45 Cat6 blindé, jusqu\'à 10 Gb/s sur courte distance.', 900, 150, 0],
            ['cables', 'Bobine Cat6 – 305 m', 'Legrand', 'Câble rigide pour installation murale et baies de brassage.', 39900, 4, 0],
            ['cables', 'Module SFP+ 10G multimode', 'Cisco', 'Transceiver 10GBASE-SR, connecteur LC duplex.', 5900, 25, 0],
            ['securite', 'Pare-feu matériel nouvelle génération', 'Fortinet', 'Filtrage applicatif, IPS, VPN SSL et inspection du trafic chiffré.', 129900, 2, 1],
            ['securite', 'Caméra IP PoE 4 MP', 'Hikvision', 'Vision nocturne 30 m, détection de mouvement, alimentation PoE.', 15900, 18, 0],
        ];
        $insertProduct = $pdo->prepare(
            'INSERT INTO products (category_id, name, brand, description, price_cents, stock, featured) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($products as [$slug, $name, $brand, $description, $price, $stock, $featured]) {
            $insertProduct->execute([$ids[$slug], $name, $brand, $description, $price, $stock, $featured]);
        }

        $insertUser = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $insertUser->execute(['Administrateur', 'admin@mfbuy.test', password_hash($adminPassword, PASSWORD_DEFAULT), 'admin']);
        $insertUser->execute(['Client Démo', 'client@mfbuy.test', password_hash($customerPassword, PASSWORD_DEFAULT), 'customer']);
    }
}
