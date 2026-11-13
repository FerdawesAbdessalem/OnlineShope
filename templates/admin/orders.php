<?php /** @var array $orders @var ?string $status */ ?>
<h1>Commandes</h1>
<nav class="tabs">
    <a class="<?= !$status ? 'active' : '' ?>" href="/admin/orders">Toutes</a>
    <?php foreach (ORDER_STATUSES as $key => $label): ?>
        <a class="<?= $status === $key ? 'active' : '' ?>" href="/admin/orders?status=<?= $key ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</nav>
<?php if (!$orders): ?>
    <div class="empty"><h3>Aucune commande.</h3></div>
<?php else: ?>
<div class="card table-wrap">
    <table>
        <thead><tr><th>N°</th><th>Date</th><th>Client</th><th>Ville</th><th>Total</th><th>Statut</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><a href="/admin/orders/<?= (int) $o['id'] ?>">#<?= (int) $o['id'] ?></a></td>
                <td><?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></td>
                <td><?= e($o['customer_name']) ?></td>
                <td><?= e($o['shipping_city']) ?></td>
                <td><?= money((int) $o['total_cents']) ?></td>
                <td><span class="status status-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
