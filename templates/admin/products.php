<?php /** @var array $products */ ?>
<div class="page-head">
    <h1>Produits</h1>
    <a class="btn" href="/admin/products/new">+ Nouveau produit</a>
</div>
<div class="card table-wrap">
    <table>
        <thead><tr><th></th><th>Produit</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr class="<?= $p['active'] ? '' : 'is-inactive' ?>">
                <td><img class="thumb" src="<?= e(product_image($p)) ?>" alt=""></td>
                <td><strong><?= e($p['name']) ?></strong><br><small class="muted"><?= e($p['brand']) ?></small></td>
                <td><?= e($p['category_name']) ?></td>
                <td><?= money((int) $p['price_cents']) ?></td>
                <td><?= (int) $p['stock'] ?></td>
                <td>
                    <?php if (!$p['active']): ?><span class="status status-cancelled">Masqué</span>
                    <?php elseif ($p['featured']): ?><span class="status status-paid">En vedette</span>
                    <?php else: ?><span class="status status-delivered">En ligne</span><?php endif; ?>
                </td>
                <td class="actions">
                    <a href="/admin/products/<?= (int) $p['id'] ?>/edit">Modifier</a>
                    <form action="/admin/products/<?= (int) $p['id'] ?>/delete" method="post" onsubmit="return confirm('Supprimer ce produit ?')">
                        <?= csrf_field() ?><button class="link danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
