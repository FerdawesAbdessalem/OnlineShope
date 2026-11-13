<?php
/** @var App\View $view @var array $result @var array $categories @var ?array $category @var string $query @var string $sort */
$base = ['q' => $query, 'category' => $category['slug'] ?? null, 'sort' => $sort, 'in_stock' => isset($_GET['in_stock']) ? 1 : null];
?>
<div class="catalog">
    <aside class="filters">
        <h2>Catégories</h2>
        <a class="<?= $category ? '' : 'active' ?>" href="<?= e(url('/products', [...$base, 'category' => null])) ?>">Toutes</a>
        <?php foreach ($categories as $c): ?>
            <a class="<?= ($category['id'] ?? null) == $c['id'] ? 'active' : '' ?>"
               href="<?= e(url('/products', [...$base, 'category' => $c['slug']])) ?>">
                <?= e($c['name']) ?> <small><?= (int) $c['product_count'] ?></small>
            </a>
        <?php endforeach; ?>
    </aside>

    <section>
        <div class="catalog-head">
            <div>
                <h1><?= e($title) ?></h1>
                <p class="muted"><?= (int) $result['total'] ?> produit(s)</p>
            </div>
            <form method="get" action="/products" class="sort">
                <?php if ($query !== ''): ?><input type="hidden" name="q" value="<?= e($query) ?>"><?php endif; ?>
                <?php if ($category): ?><input type="hidden" name="category" value="<?= e($category['slug']) ?>"><?php endif; ?>
                <label class="check"><input type="checkbox" name="in_stock" value="1" <?= isset($_GET['in_stock']) ? 'checked' : '' ?> onchange="this.form.submit()"> En stock</label>
                <select name="sort" onchange="this.form.submit()" aria-label="Trier">
                    <?php foreach (['new' => 'Nouveautés', 'price_asc' => 'Prix croissant', 'price_desc' => 'Prix décroissant', 'name' => 'Nom (A-Z)'] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $sort === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <noscript><button class="btn btn-sm">Trier</button></noscript>
            </form>
        </div>

        <?php if (!$result['items']): ?>
            <div class="empty">
                <h3>Aucun produit ne correspond à votre recherche.</h3>
                <a class="btn" href="/products">Voir tout le catalogue</a>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($result['items'] as $product): ?>
                    <?= $view->partial('partials/product_card', ['product' => $product]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($result['pages'] > 1): ?>
            <nav class="pagination" aria-label="Pagination">
                <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                    <a class="<?= $p === $result['page'] ? 'active' : '' ?>" href="<?= e(url('/products', [...$base, 'page' => $p])) ?>"><?= $p ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
</div>
