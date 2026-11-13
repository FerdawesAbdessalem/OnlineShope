<?php /** @var array $orders */ ?>
<h1>Mes commandes</h1>
<?php if (!$orders): ?>
    <div class="empty"><h3>Vous n'avez pas encore passé de commande.</h3><a class="btn" href="/products">Découvrir le catalogue</a></div>
<?php else: ?>
    <div class="card table-wrap">
        <table>
            <thead><tr><th>N°</th><th>Date</th><th>Articles</th><th>Total</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= (int) $o['id'] ?></td>
                    <td><?= e(date('d/m/Y', strtotime($o['created_at']))) ?></td>
                    <td><?= (int) $o['item_count'] ?></td>
                    <td><?= money((int) $o['total_cents']) ?></td>
                    <td><span class="status status-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td>
                    <td><a href="/orders/<?= (int) $o['id'] ?>">Détails</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
