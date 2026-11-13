<?php /** @var array $order */ ?>
<a href="/admin/orders" class="back">← Commandes</a>
<div class="page-head">
    <h1>Commande n° <?= (int) $order['id'] ?></h1>
    <span class="status status-<?= e($order['status']) ?>"><?= e(status_label($order['status'])) ?></span>
</div>

<div class="cart-layout">
    <div class="card table-wrap">
        <table>
            <thead><tr><th>Produit</th><th>Prix unitaire</th><th>Qté</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td><?= e($item['product_name']) ?></td>
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
        <h2>Client</h2>
        <p><strong><?= e($order['customer_name']) ?></strong><br><?= e($order['customer_email']) ?></p>
        <h2>Livraison</h2>
        <p><?= e($order['shipping_name']) ?><br><?= e($order['shipping_address']) ?><br><?= e($order['shipping_city']) ?><br><?= e($order['phone']) ?></p>
        <form action="/admin/orders/<?= (int) $order['id'] ?>/status" method="post" class="form">
            <?= csrf_field() ?>
            <div class="field">
                <label for="status">Changer le statut</label>
                <select id="status" name="status">
                    <?php foreach (ORDER_STATUSES as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-block">Mettre à jour</button>
        </form>
    </aside>
</div>
