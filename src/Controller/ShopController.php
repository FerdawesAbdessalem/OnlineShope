<?php

declare(strict_types=1);

namespace App\Controller;

use App\App;
use App\Service\CheckoutService;
use RuntimeException;

/** Public catalogue, cart, checkout and customer orders. */
final class ShopController
{
    public function __construct(private readonly App $app)
    {
    }

    public function home(): string
    {
        return $this->app->render('shop/home', [
            'title' => 'Matériel réseau pour pros et particuliers',
            'featured' => $this->app->products->featured(4),
            'categories' => $this->app->categories->all(),
        ]);
    }

    public function catalog(): string
    {
        $slug = $_GET['category'] ?? null;
        $category = $slug ? $this->app->categories->findBySlug((string) $slug) : null;
        $query = trim((string) ($_GET['q'] ?? ''));
        $sort = (string) ($_GET['sort'] ?? 'new');
        $result = $this->app->products->search(
            $query,
            $category ? (int) $category['id'] : null,
            $sort,
            (int) ($_GET['page'] ?? 1),
            isset($_GET['in_stock']),
        );

        return $this->app->render('shop/catalog', [
            'title' => $category['name'] ?? ($query !== '' ? "Recherche « $query »" : 'Catalogue'),
            'result' => $result,
            'categories' => $this->app->categories->all(),
            'category' => $category,
            'query' => $query,
            'sort' => $sort,
        ]);
    }

    public function product(array $params): string
    {
        $product = $this->app->products->find((int) $params['id']);
        if (!$product) {
            return $this->app->render('errors/404', ['title' => 'Produit introuvable'], 404);
        }
        $related = array_filter(
            $this->app->products->search(null, (int) $product['category_id'], 'new', 1)['items'],
            static fn ($p) => (int) $p['id'] !== (int) $product['id'],
        );
        return $this->app->render('shop/product', [
            'title' => $product['name'],
            'product' => $product,
            'related' => array_slice($related, 0, 3),
        ]);
    }

    // ------------------------------------------------------------------ cart
    public function cart(): string
    {
        $summary = $this->app->cart->summary($this->app->products->findMany(array_keys($this->app->cart->items())));
        if ($summary['adjusted']) {
            flash('info', 'Certaines quantités ont été ajustées au stock disponible.');
        }
        return $this->app->render('cart/index', ['title' => 'Mon panier', 'summary' => $summary]);
    }

    public function addToCart(): never
    {
        $this->app->verifyCsrf();
        $product = $this->app->products->find((int) ($_POST['product_id'] ?? 0));
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        if (!$product || (int) $product['stock'] <= 0) {
            flash('error', 'Ce produit n\'est pas disponible.');
            redirect('/products');
        }
        $this->app->cart->add((int) $product['id'], $quantity);
        flash('success', sprintf('« %s » a été ajouté au panier.', $product['name']));
        redirect(safe_path($_POST['redirect'] ?? null, '/cart'));
    }

    public function updateCart(): never
    {
        $this->app->verifyCsrf();
        foreach ((array) ($_POST['quantities'] ?? []) as $id => $quantity) {
            $this->app->cart->set((int) $id, (int) $quantity);
        }
        flash('success', 'Panier mis à jour.');
        redirect('/cart');
    }

    public function removeFromCart(): never
    {
        $this->app->verifyCsrf();
        $this->app->cart->remove((int) ($_POST['product_id'] ?? 0));
        redirect('/cart');
    }

    // -------------------------------------------------------------- checkout
    public function checkoutForm(): string
    {
        $user = $this->app->requireUser();
        $summary = $this->app->cart->summary($this->app->products->findMany(array_keys($this->app->cart->items())));
        if (!$summary['lines']) {
            flash('info', 'Votre panier est vide.');
            redirect('/cart');
        }
        return $this->app->render('cart/checkout', [
            'title' => 'Finaliser la commande',
            'summary' => $summary,
            'user' => $user,
            'errors' => $_SESSION['errors'] ?? [],
        ]) . $this->clearErrors();
    }

    public function placeOrder(): never
    {
        $this->app->verifyCsrf();
        $user = $this->app->requireUser();
        $shipping = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
        ];
        $errors = CheckoutService::validateShipping($shipping);
        if ($errors) {
            $_SESSION['errors'] = $errors;
            $this->app->keepInput($shipping);
            redirect('/checkout');
        }
        try {
            $orderId = $this->app->checkout->placeOrder((int) $user['id'], $this->app->cart, $shipping);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/cart');
        }
        $this->app->forgetInput();
        flash('success', 'Merci ! Votre commande a bien été enregistrée.');
        redirect('/orders/' . $orderId);
    }

    // ---------------------------------------------------------------- orders
    public function myOrders(): string
    {
        $user = $this->app->requireUser();
        return $this->app->render('account/orders', [
            'title' => 'Mes commandes',
            'orders' => $this->app->orders->forUser((int) $user['id']),
        ]);
    }

    public function order(array $params): string
    {
        $user = $this->app->requireUser();
        $order = $this->app->orders->find((int) $params['id']);
        // A customer can only see their own orders (no IDOR).
        if (!$order || ((int) $order['user_id'] !== (int) $user['id'] && $user['role'] !== 'admin')) {
            return $this->app->render('errors/404', ['title' => 'Commande introuvable'], 404);
        }
        return $this->app->render('account/order', ['title' => 'Commande n° ' . $order['id'], 'order' => $order]);
    }

    private function clearErrors(): string
    {
        unset($_SESSION['errors'], $_SESSION['old']);
        return '';
    }
}
