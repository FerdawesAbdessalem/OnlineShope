<?php

declare(strict_types=1);

namespace App\Controller;

use App\App;

/** Back office: dashboard, products (with image upload), categories, orders. */
final class AdminController
{
    private const MAX_IMAGE_BYTES = 2 * 1024 * 1024;
    private const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public function __construct(private readonly App $app)
    {
    }

    public function dashboard(): string
    {
        $this->app->requireAdmin();
        return $this->app->render('admin/dashboard', [
            'title' => 'Tableau de bord',
            'stats' => $this->app->orders->stats(),
            'customers' => $this->app->users->countCustomers(),
            'lowStock' => $this->app->products->lowStock(),
            'latest' => array_slice($this->app->orders->all(), 0, 6),
        ]);
    }

    // -------------------------------------------------------------- products
    public function products(): string
    {
        $this->app->requireAdmin();
        return $this->app->render('admin/products', ['title' => 'Produits', 'products' => $this->app->products->allForAdmin()]);
    }

    public function productForm(array $params = []): string
    {
        $this->app->requireAdmin();
        $product = isset($params['id']) ? $this->app->products->find((int) $params['id'], false) : null;
        if (isset($params['id']) && !$product) {
            return $this->app->render('errors/404', ['title' => 'Produit introuvable'], 404);
        }
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        return $this->app->render('admin/product_form', [
            'title' => $product ? 'Modifier le produit' : 'Nouveau produit',
            'product' => $product,
            'categories' => $this->app->categories->all(),
            'errors' => $errors,
        ]);
    }

    public function saveProduct(array $params = []): never
    {
        $this->app->requireAdmin();
        $this->app->verifyCsrf();
        $id = isset($params['id']) ? (int) $params['id'] : null;
        $back = $id ? "/admin/products/$id/edit" : '/admin/products/new';

        $price = str_replace([' ', ','], ['', '.'], (string) ($_POST['price'] ?? ''));
        $data = [
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'brand' => trim((string) ($_POST['brand'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'price_cents' => is_numeric($price) ? (int) round((float) $price * 100) : -1,
            'stock' => (int) ($_POST['stock'] ?? -1),
            'featured' => isset($_POST['featured']),
            'active' => isset($_POST['active']),
        ];

        $errors = [];
        if (mb_strlen($data['name']) < 3) {
            $errors['name'] = 'Le nom doit contenir au moins 3 caractères.';
        }
        if ($data['category_id'] <= 0) {
            $errors['category_id'] = 'Choisissez une catégorie.';
        }
        if ($data['price_cents'] <= 0) {
            $errors['price'] = 'Prix invalide.';
        }
        if ($data['stock'] < 0) {
            $errors['stock'] = 'Le stock ne peut pas être négatif.';
        }
        if ($data['description'] === '') {
            $errors['description'] = 'Ajoutez une description.';
        }

        $upload = $_FILES['image'] ?? null;
        if ($upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
            [$filename, $error] = $this->storeImage($upload);
            if ($error) {
                $errors['image'] = $error;
            } else {
                $data['image'] = $filename;
            }
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            $this->app->keepInput($_POST);
            redirect($back);
        }

        $this->app->products->save($id, $data);
        $this->app->forgetInput();
        flash('success', $id ? 'Produit mis à jour.' : 'Produit ajouté au catalogue.');
        redirect('/admin/products');
    }

    public function deleteProduct(array $params): never
    {
        $this->app->requireAdmin();
        $this->app->verifyCsrf();
        $this->app->products->delete((int) $params['id']);
        flash('success', 'Produit supprimé (ou désactivé s\'il figure dans des commandes).');
        redirect('/admin/products');
    }

    /**
     * Validates the real file type (not the extension sent by the browser),
     * the size, and stores it under a random name.
     *
     * @return array{0: ?string, 1: ?string} [filename, error]
     */
    private function storeImage(array $upload): array
    {
        if ($upload['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) {
            return [null, 'Le téléversement de l\'image a échoué.'];
        }
        if ($upload['size'] > self::MAX_IMAGE_BYTES) {
            return [null, 'Image trop lourde (2 Mo maximum).'];
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
        $extension = self::IMAGE_TYPES[$mime] ?? null;
        if (!$extension) {
            return [null, 'Format accepté : JPG, PNG ou WebP.'];
        }
        $filename = bin2hex(random_bytes(12)) . '.' . $extension;
        if (!move_uploaded_file($upload['tmp_name'], $this->app->uploadDir . '/' . $filename)) {
            return [null, 'Impossible d\'enregistrer l\'image.'];
        }
        return [$filename, null];
    }

    // ------------------------------------------------------------ categories
    public function categories(): string
    {
        $this->app->requireAdmin();
        return $this->app->render('admin/categories', ['title' => 'Catégories', 'categories' => $this->app->categories->all()]);
    }

    public function addCategory(): never
    {
        $this->app->requireAdmin();
        $this->app->verifyCsrf();
        $name = trim((string) ($_POST['name'] ?? ''));
        if (mb_strlen($name) < 2) {
            flash('error', 'Nom de catégorie trop court.');
        } else {
            try {
                $this->app->categories->create($name, (string) ($_POST['icon'] ?? 'box'));
                flash('success', 'Catégorie ajoutée.');
            } catch (\PDOException) {
                flash('error', 'Cette catégorie existe déjà.');
            }
        }
        redirect('/admin/categories');
    }

    public function deleteCategory(array $params): never
    {
        $this->app->requireAdmin();
        $this->app->verifyCsrf();
        $deleted = $this->app->categories->delete((int) $params['id']);
        flash($deleted ? 'success' : 'error', $deleted ? 'Catégorie supprimée.' : 'Impossible : des produits utilisent cette catégorie.');
        redirect('/admin/categories');
    }

    // ---------------------------------------------------------------- orders
    public function orders(): string
    {
        $this->app->requireAdmin();
        $status = $_GET['status'] ?? null;
        return $this->app->render('admin/orders', [
            'title' => 'Commandes',
            'orders' => $this->app->orders->all(is_string($status) && isset(ORDER_STATUSES[$status]) ? $status : null),
            'status' => $status,
        ]);
    }

    public function order(array $params): string
    {
        $this->app->requireAdmin();
        $order = $this->app->orders->find((int) $params['id']);
        if (!$order) {
            return $this->app->render('errors/404', ['title' => 'Commande introuvable'], 404);
        }
        return $this->app->render('admin/order', ['title' => 'Commande n° ' . $order['id'], 'order' => $order]);
    }

    public function updateOrderStatus(array $params): never
    {
        $this->app->requireAdmin();
        $this->app->verifyCsrf();
        $status = (string) ($_POST['status'] ?? '');
        if (isset(ORDER_STATUSES[$status])) {
            $this->app->orders->updateStatus((int) $params['id'], $status);
            flash('success', 'Statut mis à jour : ' . status_label($status) . '.');
        }
        redirect('/admin/orders/' . (int) $params['id']);
    }
}
