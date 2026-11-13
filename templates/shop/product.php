<?php /** @var App\View $view @var array $product @var array $related */ $stock = (int) $product['stock']; ?>
<nav class="breadcrumb">
    <a href="/">Accueil</a><span>/</span><a href="/products?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a><span>/</span><span><?= e($product['name']) ?></span>
</nav>

<div class="product-page">
    <div class="product-gallery">
        <img src="<?= e(product_image($product)) ?>" alt="<?= e($product['name']) ?>">
    </div>
    <div class="product-info">
        <span class="brand-chip"><?= e($product['brand']) ?></span>
        <h1><?= e($product['name']) ?></h1>
        <p class="description"><?= nl2br(e($product['description'])) ?></p>

        <div class="buy-box">
            <div class="buy-price">
                <span class="price price-lg"><?= money((int) $product['price_cents']) ?></span>
                <span class="muted small">TTC · livraison offerte</span>
            </div>
            <?php if ($stock === 0): ?>
                <p class="stock stock-out"><span class="dot"></span>Rupture de stock</p>
            <?php elseif ($stock <= 5): ?>
                <p class="stock stock-low"><span class="dot"></span>Plus que <?= $stock ?> en stock</p>
            <?php else: ?>
                <p class="stock stock-ok"><span class="dot"></span>En stock — expédié sous 48 h</p>
            <?php endif; ?>
            <?php if ($stock > 0): ?>
                <form action="/cart/add" method="post" class="add-to-cart">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <label class="qty"><span class="sr">Quantité</span>
                        <input type="number" name="quantity" value="1" min="1" max="<?= min($stock, App\Cart::MAX_QUANTITY) ?>" aria-label="Quantité">
                    </label>
                    <button class="btn btn-lg btn-grow"><svg class="i" viewBox="0 0 24 24"><use href="/assets/img/icons.svg#cart"/></svg> Ajouter au panier</button>
                </form>
            <?php endif; ?>
            <ul class="assurances">
                <li><svg class="i" viewBox="0 0 24 24"><use href="/assets/img/icons.svg#truck"/></svg>Livraison offerte en 48 h</li>
                <li><svg class="i" viewBox="0 0 24 24"><use href="/assets/img/icons.svg#cash"/></svg>Paiement à la livraison</li>
                <li><svg class="i" viewBox="0 0 24 24"><use href="/assets/img/icons.svg#refresh"/></svg>Retour gratuit sous 14 jours</li>
            </ul>
        </div>

        <table class="specs">
            <tr><th>Marque</th><td><?= e($product['brand']) ?></td></tr>
            <tr><th>Catégorie</th><td><?= e($product['category_name']) ?></td></tr>
            <tr><th>Référence</th><td>MF-<?= str_pad((string) $product['id'], 5, '0', STR_PAD_LEFT) ?></td></tr>
            <tr><th>Garantie</th><td>2 ans constructeur</td></tr>
        </table>
    </div>
</div>

<?php if ($related): ?>
<section>
    <div class="section-head"><div><span class="kicker">À découvrir</span><h2>Dans la même catégorie</h2></div></div>
    <div class="grid">
        <?php foreach ($related as $p): ?>
            <?= $view->partial('partials/product_card', ['product' => $p]) ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
