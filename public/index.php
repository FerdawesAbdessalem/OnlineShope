<?php

declare(strict_types=1);

/**
 * Front controller: every request goes through here (see .htaccess / the PHP
 * built-in server), so access rules and security headers apply everywhere.
 */

use App\App;
use App\Config;
use App\Controller\AdminController;
use App\Controller\AuthController;
use App\Controller\ShopController;
use App\Database;
use App\Router;

$root = dirname(__DIR__);

// Let the PHP built-in server serve static files (CSS, images…) directly.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file)) {
        return false;
    }
}

require $root . '/vendor/autoload.php';
Config::load($root);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']),
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

$debug = Config::get('APP_DEBUG') === '1';

try {
    $app = new App(Database::fromConfig($root), $root . '/templates', __DIR__ . '/uploads');
} catch (PDOException $e) {
    http_response_code(500);
    echo $debug ? 'Database error: ' . e($e->getMessage()) : 'Service momentanément indisponible.';
    exit;
}

$shop = new ShopController($app);
$auth = new AuthController($app);
$admin = new AdminController($app);
$router = new Router();

// Shop
$router->get('/', [$shop, 'home']);
$router->get('/products', [$shop, 'catalog']);
$router->get('/products/{id}', [$shop, 'product']);
$router->get('/cart', [$shop, 'cart']);
$router->post('/cart/add', [$shop, 'addToCart']);
$router->post('/cart/update', [$shop, 'updateCart']);
$router->post('/cart/remove', [$shop, 'removeFromCart']);
$router->get('/checkout', [$shop, 'checkoutForm']);
$router->post('/checkout', [$shop, 'placeOrder']);
$router->get('/orders', [$shop, 'myOrders']);
$router->get('/orders/{id}', [$shop, 'order']);

// Accounts
$router->get('/login', [$auth, 'loginForm']);
$router->post('/login', [$auth, 'login']);
$router->get('/register', [$auth, 'registerForm']);
$router->post('/register', [$auth, 'register']);
$router->post('/logout', [$auth, 'logout']);

// Back office
$router->get('/admin', [$admin, 'dashboard']);
$router->get('/admin/products', [$admin, 'products']);
$router->get('/admin/products/new', [$admin, 'productForm']);
$router->post('/admin/products', [$admin, 'saveProduct']);
$router->get('/admin/products/{id}/edit', [$admin, 'productForm']);
$router->post('/admin/products/{id}', [$admin, 'saveProduct']);
$router->post('/admin/products/{id}/delete', [$admin, 'deleteProduct']);
$router->get('/admin/categories', [$admin, 'categories']);
$router->post('/admin/categories', [$admin, 'addCategory']);
$router->post('/admin/categories/{id}/delete', [$admin, 'deleteCategory']);
$router->get('/admin/orders', [$admin, 'orders']);
$router->get('/admin/orders/{id}', [$admin, 'order']);
$router->post('/admin/orders/{id}/status', [$admin, 'updateOrderStatus']);

[$handler, $params, $methodNotAllowed] = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

try {
    if ($handler === null) {
        echo $app->render('errors/404', ['title' => 'Page introuvable'], $methodNotAllowed ? 405 : 404);
    } else {
        echo $params ? $handler($params) : $handler();
    }
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    echo $debug ? '<pre>' . e((string) $e) . '</pre>' : $app->render('errors/500', ['title' => 'Erreur'], 500);
}
