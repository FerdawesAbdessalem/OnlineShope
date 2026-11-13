<?php /** @var array $categories */ ?>
<h1>Catégories</h1>
<div class="admin-grid">
    <div class="card table-wrap">
        <table>
            <thead><tr><th></th><th>Nom</th><th>Produits</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><img class="thumb" src="/assets/img/<?= e($c['icon']) ?>.svg" alt=""></td>
                    <td><?= e($c['name']) ?></td>
                    <td><?= (int) $c['product_count'] ?></td>
                    <td class="actions">
                        <form action="/admin/categories/<?= (int) $c['id'] ?>/delete" method="post" onsubmit="return confirm('Supprimer cette catégorie ?')">
                            <?= csrf_field() ?><button class="link danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <form action="/admin/categories" method="post" class="card form">
        <?= csrf_field() ?>
        <h2>Nouvelle catégorie</h2>
        <div class="field">
            <label for="cat-name">Nom</label>
            <input id="cat-name" name="name" required>
        </div>
        <div class="field">
            <label for="cat-icon">Icône</label>
            <select id="cat-icon" name="icon">
                <?php foreach (['router' => 'Routeur', 'switch' => 'Switch', 'wifi' => 'Wi-Fi', 'cable' => 'Câble', 'shield' => 'Sécurité', 'box' => 'Autre'] as $icon => $label): ?>
                    <option value="<?= $icon ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-block">Ajouter</button>
    </form>
</div>
