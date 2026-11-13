<?php /** @var array $stats @var int $customers @var array $lowStock @var array $latest @var array $daily @var array $byStatus @var int $productCount */
$max = max(1, ...array_values($daily));
$days = ['Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mer', 'Thu' => 'Jeu', 'Fri' => 'Ven', 'Sat' => 'Sam', 'Sun' => 'Dim'];
$weekTotal = array_sum($daily);
$totalOrders = max(1, array_sum($byStatus));
$statusColors = ['pending' => '#f59e0b', 'paid' => '#3b82f6', 'shipped' => '#8b5cf6', 'delivered' => '#22c55e', 'cancelled' => '#ef4444'];
$kpi = static fn (string $icon, string $label, string $value, string $tone, string $hint) =>
    "<div class=\"kpi kpi-$tone\"><span class=\"kpi-icon\"><svg class=\"i\" viewBox=\"0 0 24 24\"><use href=\"/assets/img/icons.svg#$icon\"/></svg></span>"
    . "<div><span class=\"kpi-label\">$label</span><strong>$value</strong><small>$hint</small></div></div>";
?>
<div class="page-head">
    <div><h1>Tableau de bord</h1><p class="muted">Vue d'ensemble de la boutique</p></div>
    <a class="btn" href="/admin/products/new"><svg class="i" viewBox="0 0 24 24"><use href="/assets/img/icons.svg#plus"/></svg> Nouveau produit</a>
</div>

<div class="kpis">
    <?= $kpi('trend', 'Chiffre d\'affaires', money($stats['revenue']), 'blue', money($weekTotal) . ' sur 7 jours') ?>
    <?= $kpi('receipt', 'Commandes', (string) $stats['orders'], 'violet', $stats['pending'] . ' à traiter') ?>
    <?= $kpi('users', 'Clients', (string) $customers, 'green', 'comptes clients') ?>
    <?= $kpi('alert', 'Stock faible', (string) count($lowStock), 'amber', 'sur ' . $productCount . ' produits') ?>
</div>

<div class="admin-grid">
    <section class="panel">
        <div class="panel-head"><h2>Ventes des 7 derniers jours</h2><span class="muted"><?= money($weekTotal) ?></span></div>
        <div class="chart" role="img" aria-label="Ventes par jour">
            <?php foreach ($daily as $date => $cents): $h = (int) round($cents / $max * 100); ?>
                <div class="bar-col">
                    <span class="bar-value"><?= $cents ? number_format($cents / 100, 0, ',', ' ') : '' ?></span>
                    <div class="bar" style="height: <?= max($h, 2) ?>%"></div>
                    <span class="bar-label"><?= $days[date('D', strtotime($date))] ?? '' ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Commandes par statut</h2><a href="/admin/orders">Détails →</a></div>
        <div class="status-bars">
            <?php foreach ($byStatus as $status => $count): ?>
                <div class="status-row">
                    <span><i style="background: <?= $statusColors[$status] ?>"></i><?= e(status_label($status)) ?></span>
                    <div class="track"><div style="width: <?= round($count / $totalOrders * 100) ?>%; background: <?= $statusColors[$status] ?>"></div></div>
                    <b><?= $count ?></b>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Dernières commandes</h2><a href="/admin/orders">Toutes →</a></div>
        <?php if (!$latest): ?>
            <p class="muted">Aucune commande pour le moment.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>N°</th><th>Client</th><th>Total</th><th>Statut</th></tr></thead>
                <tbody>
                <?php foreach ($latest as $o): ?>
                    <tr>
                        <td><a href="/admin/orders/<?= (int) $o['id'] ?>">#<?= (int) $o['id'] ?></a></td>
                        <td><?= e($o['customer_name']) ?></td>
                        <td><strong><?= money((int) $o['total_cents']) ?></strong></td>
                        <td><span class="status status-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Stock à surveiller</h2><a href="/admin/products">Produits →</a></div>
        <?php if (!$lowStock): ?>
            <p class="muted">Tous les produits sont bien approvisionnés.</p>
        <?php else: ?>
            <ul class="stock-list">
                <?php foreach ($lowStock as $p): ?>
                    <li>
                        <img src="<?= e(product_image($p)) ?>" alt="">
                        <a href="/admin/products/<?= (int) $p['id'] ?>/edit"><?= e($p['name']) ?></a>
                        <span class="status <?= (int) $p['stock'] === 0 ? 'status-cancelled' : 'status-pending' ?>"><?= (int) $p['stock'] ?> en stock</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
