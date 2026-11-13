<?php /** @var array $product */ ?>
<article class="product-card">
    <a href="/products/<?= (int) $product['id'] ?>" class="product-media">
        <img src="<?= e(product_image($product)) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
        <?php if ((int) $product['stock'] === 0): ?>
            <span class="tag tag-muted">Rupture</span>
        <?php elseif ((int) $product['stock'] <= 5): ?>
            <span class="tag tag-warn">Plus que <?= (int) $product['stock'] ?></span>
        <?php endif; ?>
    </a>
    <div class="product-body">
        <span class="product-cat"><?= e($product['category_name']) ?> · <?= e($product['brand']) ?></span>
        <h3><a href="/products/<?= (int) $product['id'] ?>"><?= e($product['name']) ?></a></h3>
        <div class="product-foot">
            <strong class="price"><?= money((int) $product['price_cents']) ?></strong>
            <?php if ((int) $product['stock'] > 0): ?>
                <form action="/cart/add" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
                    <button class="btn btn-sm" aria-label="Ajouter <?= e($product['name']) ?> au panier">Ajouter</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</article>
