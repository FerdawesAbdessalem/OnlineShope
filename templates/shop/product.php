<?php /** @var App\View $view @var array $product @var array $related */ $stock = (int) $product['stock']; ?>
<nav class="breadcrumb">
    <a href="/">Accueil</a> › <a href="/products?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a> › <span><?= e($product['name']) ?></span>
</nav>

<div class="product-page">
    <div class="product-gallery">
        <img src="<?= e(product_image($product)) ?>" alt="<?= e($product['name']) ?>">
    </div>
    <div class="product-info">
        <span class="product-cat"><?= e($product['brand']) ?></span>
        <h1><?= e($product['name']) ?></h1>
        <p class="price price-lg"><?= money((int) $product['price_cents']) ?></p>

        <?php if ($stock === 0): ?>
            <p class="stock stock-out">Rupture de stock</p>
        <?php elseif ($stock <= 5): ?>
            <p class="stock stock-low">Plus que <?= $stock ?> en stock — commandez vite</p>
        <?php else: ?>
            <p class="stock stock-ok">En stock, expédié sous 48 h</p>
        <?php endif; ?>

        <p class="description"><?= nl2br(e($product['description'])) ?></p>

        <?php if ($stock > 0): ?>
            <form action="/cart/add" method="post" class="add-to-cart">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <label for="qty">Quantité</label>
                <input id="qty" type="number" name="quantity" value="1" min="1" max="<?= min($stock, App\Cart::MAX_QUANTITY) ?>">
                <button class="btn btn-lg">Ajouter au panier</button>
            </form>
        <?php endif; ?>

        <ul class="assurances">
            <li>Paiement à la livraison</li>
            <li>Garantie constructeur</li>
            <li>Retour sous 14 jours</li>
        </ul>
    </div>
</div>

<?php if ($related): ?>
<section>
    <div class="section-head"><h2>Dans la même catégorie</h2></div>
    <div class="grid">
        <?php foreach ($related as $p): ?>
            <?= $view->partial('partials/product_card', ['product' => $p]) ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
