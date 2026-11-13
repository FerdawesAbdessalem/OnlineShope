<?php /** @var ?array $product @var array $categories @var array $errors */
$value = static fn (string $key, mixed $default = '') => old($key, (string) $default);
$err = static fn (string $f): string => isset($errors[$f]) ? '<span class="error">' . e($errors[$f]) . '</span>' : '';
$hasOld = isset($_SESSION['old']);
$checked = static fn (string $key, bool $default) => ($hasOld ? isset($_SESSION['old'][$key]) : $default) ? 'checked' : '';
$action = $product ? '/admin/products/' . (int) $product['id'] : '/admin/products';
$selectedCategory = (int) $value('category_id', $product['category_id'] ?? 0);
?>
<a href="/admin/products" class="back">← Produits</a>
<h1><?= e($title) ?></h1>

<form action="<?= $action ?>" method="post" enctype="multipart/form-data" class="card form form-wide" novalidate>
    <?= csrf_field() ?>
    <div class="field-row">
        <div class="field <?= isset($errors['name']) ? 'has-error' : '' ?>">
            <label for="name">Nom</label>
            <input id="name" name="name" value="<?= e($value('name', $product['name'] ?? '')) ?>" required>
            <?= $err('name') ?>
        </div>
        <div class="field">
            <label for="brand">Marque</label>
            <input id="brand" name="brand" value="<?= e($value('brand', $product['brand'] ?? '')) ?>">
        </div>
    </div>
    <div class="field-row">
        <div class="field <?= isset($errors['category_id']) ? 'has-error' : '' ?>">
            <label for="category_id">Catégorie</label>
            <select id="category_id" name="category_id" required>
                <option value="">— Choisir —</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= $selectedCategory === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?= $err('category_id') ?>
        </div>
        <div class="field <?= isset($errors['price']) ? 'has-error' : '' ?>">
            <label for="price">Prix (DT)</label>
            <input id="price" name="price" inputmode="decimal" value="<?= e($value('price', isset($product['price_cents']) ? number_format($product['price_cents'] / 100, 2, '.', '') : '')) ?>" required>
            <?= $err('price') ?>
        </div>
        <div class="field <?= isset($errors['stock']) ? 'has-error' : '' ?>">
            <label for="stock">Stock</label>
            <input id="stock" name="stock" type="number" min="0" value="<?= e($value('stock', $product['stock'] ?? 0)) ?>" required>
            <?= $err('stock') ?>
        </div>
    </div>
    <div class="field <?= isset($errors['description']) ? 'has-error' : '' ?>">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5" required><?= e($value('description', $product['description'] ?? '')) ?></textarea>
        <?= $err('description') ?>
    </div>
    <div class="field <?= isset($errors['image']) ? 'has-error' : '' ?>">
        <label for="image">Image <small class="muted">(JPG, PNG ou WebP, 2 Mo max.)</small></label>
        <?php if (!empty($product['image'])): ?><img class="thumb-lg" src="<?= e(product_image($product)) ?>" alt=""><?php endif; ?>
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
        <?= $err('image') ?>
    </div>
    <div class="checks">
        <label class="check"><input type="checkbox" name="active" value="1" <?= $checked('active', (bool) ($product['active'] ?? true)) ?>> Visible dans la boutique</label>
        <label class="check"><input type="checkbox" name="featured" value="1" <?= $checked('featured', (bool) ($product['featured'] ?? false)) ?>> Mettre en vedette sur l'accueil</label>
    </div>
    <button class="btn btn-lg"><?= $product ? 'Enregistrer' : 'Ajouter le produit' ?></button>
</form>
<?php unset($_SESSION['old']); ?>
