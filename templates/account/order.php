<?php /** @var array $order */
$steps = ['pending', 'paid', 'shipped', 'delivered'];
$current = array_search($order['status'], $steps, true);
?>
<a href="/orders" class="back">← Mes commandes</a>
<h1>Commande n° <?= (int) $order['id'] ?></h1>
<p class="muted">Passée le <?= e(date('d/m/Y à H:i', strtotime($order['created_at']))) ?></p>

<?php if ($order['status'] === 'cancelled'): ?>
    <div class="alert alert-error">Cette commande a été annulée.</div>
<?php else: ?>
    <ol class="tracker">
        <?php foreach ($steps as $i => $step): ?>
            <li class="<?= $current !== false && $i <= $current ? 'done' : '' ?>"><?= e(status_label($step)) ?></li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>

<div class="cart-layout">
    <div class="card table-wrap">
        <table>
            <thead><tr><th>Produit</th><th>Prix unitaire</th><th>Qté</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td><a href="/products/<?= (int) $item['product_id'] ?>"><?= e($item['product_name']) ?></a></td>
                    <td><?= money((int) $item['unit_price_cents']) ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td><?= money((int) $item['unit_price_cents'] * (int) $item['quantity']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot><tr><td colspan="3">Total</td><td><strong><?= money((int) $order['total_cents']) ?></strong></td></tr></tfoot>
        </table>
    </div>
    <aside class="card summary">
        <h2>Livraison</h2>
        <p><?= e($order['shipping_name']) ?><br><?= e($order['shipping_address']) ?><br><?= e($order['shipping_city']) ?><br><?= e($order['phone']) ?></p>
        <p class="muted small">Paiement à la livraison.</p>
    </aside>
</div>
