<?php

declare(strict_types=1);

/**
 * Creates the database tables and the demo catalogue.
 *
 *   php bin/setup.php            (uses DB_DSN from .env, SQLite by default)
 */

use App\Config;
use App\Database;
use App\Schema;

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
Config::load($root);

$pdo = Database::fromConfig($root);
Schema::create($pdo);
Schema::seed(
    $pdo,
    Config::get('ADMIN_PASSWORD', 'Admin@2025'),
    Config::get('DEMO_CUSTOMER_PASSWORD', 'Client@2025'),
);

echo "Base de données prête (" . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . ").\n";
echo "Admin  : admin@mfbuy.test  / " . Config::get('ADMIN_PASSWORD', 'Admin@2025') . "\n";
echo "Client : client@mfbuy.test / " . Config::get('DEMO_CUSTOMER_PASSWORD', 'Client@2025') . "\n";
