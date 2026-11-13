<?php /** @var array $product */ $stock = (int) $product['stock']; ?>
<article class="product-card">
    <a href="/products/<?= (int) $product['id'] ?>" class="product-media">
        <img src="<?= e(product_image($product)) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
        <?php if ($stock === 0): ?>
            <span class="tag tag-muted">Rupture</span>
        <?php elseif ($stock <= 5): ?>
            <span class="tag tag-warn">Plus que <?= $stock ?></span>
        <?php elseif (!empty($product['featured'])): ?>
            <span class="tag tag-best">Best-seller</span>
        <?php endif; ?>
    </a>
    <div class="product-body">
        <span class="product-cat"><?= e($product['brand']) ?></span>
        <h3><a href="/products/<?= (int) $product['id'] ?>"><?= e($product['name']) ?></a></h3>
        <span class="stock-line <?= $stock > 0 ? 'in' : 'out' ?>"><?= $stock > 0 ? 'En stock · livré sous 48 h' : 'Indisponible' ?></span>
        <div class="product-foot">
            <strong class="price"><?= money((int) $product['price_cents']) ?></strong>
            <?php if ($stock > 0): ?>
                <form action="/cart/add" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
                    <button class="add-btn" aria-label="Ajouter <?= e($product['name']) ?> au panier">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><use href="/assets/img/icons.svg#cart"/></svg>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</article>
