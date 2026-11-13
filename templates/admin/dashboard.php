<?php /** @var array $stats @var int $customers @var array $lowStock @var array $latest */ ?>
<h1>Tableau de bord</h1>
<div class="stats">
    <div class="stat"><span>Chiffre d'affaires</span><strong><?= money($stats['revenue']) ?></strong></div>
    <div class="stat"><span>Commandes</span><strong><?= $stats['orders'] ?></strong></div>
    <div class="stat stat-warn"><span>À traiter</span><strong><?= $stats['pending'] ?></strong></div>
    <div class="stat"><span>Clients</span><strong><?= $customers ?></strong></div>
</div>

<div class="admin-grid">
    <section class="card">
        <div class="section-head"><h2>Dernières commandes</h2><a href="/admin/orders">Toutes →</a></div>
        <?php if (!$latest): ?>
            <p class="muted">Aucune commande pour le moment.</p>
        <?php else: ?>
            <table>
                <tbody>
                <?php foreach ($latest as $o): ?>
                    <tr>
                        <td><a href="/admin/orders/<?= (int) $o['id'] ?>">#<?= (int) $o['id'] ?></a></td>
                        <td><?= e($o['customer_name']) ?></td>
                        <td><?= money((int) $o['total_cents']) ?></td>
                        <td><span class="status status-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="card">
        <div class="section-head"><h2>Stock faible</h2><a href="/admin/products">Produits →</a></div>
        <?php if (!$lowStock): ?>
            <p class="muted">Tous les produits sont bien approvisionnés.</p>
        <?php else: ?>
            <table>
                <tbody>
                <?php foreach ($lowStock as $p): ?>
                    <tr>
                        <td><a href="/admin/products/<?= (int) $p['id'] ?>/edit"><?= e($p['name']) ?></a></td>
                        <td><span class="status <?= (int) $p['stock'] === 0 ? 'status-cancelled' : 'status-pending' ?>"><?= (int) $p['stock'] ?> en stock</span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
