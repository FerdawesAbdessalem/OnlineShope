<?php

declare(strict_types=1);

namespace App;

use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\CheckoutService;
use PDO;

/**
 * Tiny service container: builds each dependency once and wires them together
 * (controllers receive what they need instead of including config.php).
 */
final class App
{
    public readonly UserRepository $users;
    public readonly ProductRepository $products;
    public readonly CategoryRepository $categories;
    public readonly OrderRepository $orders;
    public readonly Auth $auth;
    public readonly Cart $cart;
    public readonly CheckoutService $checkout;
    public readonly View $view;

    public function __construct(public readonly PDO $pdo, string $templates, public readonly string $uploadDir)
    {
        $this->users = new UserRepository($pdo);
        $this->products = new ProductRepository($pdo);
        $this->categories = new CategoryRepository($pdo);
        $this->orders = new OrderRepository($pdo);
        $this->auth = new Auth($this->users);
        $_SESSION['cart'] ??= [];
        $this->cart = new Cart($_SESSION['cart']);
        $this->checkout = new CheckoutService($pdo, $this->products);
        $this->view = new View($templates, ['app' => $this]);
    }

    public function render(string $template, array $data = [], int $status = 200): string
    {
        http_response_code($status);
        return $this->view->render($template, $data);
    }

    /** Rejects POST requests without a valid CSRF token. */
    public function verifyCsrf(): void
    {
        if (!Csrf::check($_POST['_token'] ?? null)) {
            http_response_code(419);
            flash('error', 'La session a expiré, veuillez réessayer.');
            $referer = parse_url((string) ($_SERVER['HTTP_REFERER'] ?? '/'), PHP_URL_PATH);
            redirect(safe_path(is_string($referer) ? $referer : '/'));
        }
    }

    public function requireUser(): array
    {
        $user = $this->auth->user();
        if (!$user) {
            $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '/';
            flash('info', 'Connectez-vous pour continuer.');
            redirect('/login');
        }
        return $user;
    }

    public function requireAdmin(): array
    {
        $user = $this->requireUser();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo $this->render('errors/403', [], 403);
            exit;
        }
        return $user;
    }

    /** Saves form input for redisplay after a validation error. */
    public function keepInput(array $input): void
    {
        unset($input['_token'], $input['password'], $input['password_confirmation']);
        $_SESSION['old'] = $input;
    }

    public function forgetInput(): void
    {
        unset($_SESSION['old']);
    }
}
