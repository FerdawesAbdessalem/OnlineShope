<?php /** @var array $summary */ ?>
<h1>Mon panier</h1>

<?php if (!$summary['lines']): ?>
    <div class="empty">
        <h3>Votre panier est vide.</h3>
        <a class="btn" href="/products">Découvrir le catalogue</a>
    </div>
<?php else: ?>
<div class="cart-layout">
    <form action="/cart/update" method="post" class="card cart-lines">
        <?= csrf_field() ?>
        <?php foreach ($summary['lines'] as $line): $p = $line['product']; ?>
            <div class="cart-line">
                <img src="<?= e(product_image($p)) ?>" alt="">
                <div class="cart-line-info">
                    <a href="/products/<?= (int) $p['id'] ?>"><strong><?= e($p['name']) ?></strong></a>
                    <span class="muted"><?= money((int) $p['price_cents']) ?> · <?= (int) $p['stock'] ?> en stock</span>
                </div>
                <input type="number" name="quantities[<?= (int) $p['id'] ?>]" value="<?= (int) $line['quantity'] ?>"
                       min="0" max="<?= min((int) $p['stock'], App\Cart::MAX_QUANTITY) ?>" aria-label="Quantité">
                <strong class="cart-line-total"><?= money($line['subtotal']) ?></strong>
                <button class="icon-btn" formaction="/cart/remove" name="product_id" value="<?= (int) $p['id'] ?>" aria-label="Retirer <?= e($p['name']) ?>">✕</button>
            </div>
        <?php endforeach; ?>
        <div class="cart-actions">
            <a href="/products">← Continuer mes achats</a>
            <button class="btn btn-ghost">Mettre à jour</button>
        </div>
    </form>

    <aside class="card summary">
        <h2>Récapitulatif</h2>
        <div class="summary-row"><span>Sous-total</span><span><?= money($summary['total']) ?></span></div>
        <div class="summary-row"><span>Livraison</span><span>Offerte</span></div>
        <div class="summary-row total"><span>Total</span><span><?= money($summary['total']) ?></span></div>
        <a class="btn btn-lg btn-block" href="/checkout">Passer la commande</a>
        <p class="muted small">Paiement à la livraison. Aucune donnée bancaire demandée.</p>
    </aside>
</div>
<?php endif; ?>
