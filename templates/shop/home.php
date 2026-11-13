<?php /** @var App\View $view @var array $featured @var array $categories */
$hero = $featured[0] ?? null;
$promo = null;
foreach ($featured as $f) {
    if (($f['category_icon'] ?? '') === 'shield') { $promo = $f; }
}
$promo ??= $featured[1] ?? $hero;
?>
<section class="hero">
    <div class="hero-text">
        <span class="pill"><span class="dot"></span>Nouveautés Wi-Fi 6 & PoE+ en stock</span>
        <h1>Un réseau rapide et sécurisé, <span class="grad">sans compromis.</span></h1>
        <p>Routeurs, switches, bornes Wi-Fi et équipements de sécurité des grandes marques — sélectionnés pour les PME, les écoles et la maison.</p>
        <div class="hero-actions">
            <a class="btn btn-lg" href="/products">Explorer le catalogue <svg class="i" viewBox="0 0 24 24"><use href="/assets/img/icons.svg#arrow"/></svg></a>
            <a class="btn btn-lg btn-glass" href="/products?category=securite">Solutions sécurité</a>
        </div>
        <dl class="hero-stats">
            <div><dt>14+</dt><dd>références pro</dd></div>
            <div><dt>48 h</dt><dd>livraison</dd></div>
            <div><dt>2 ans</dt><dd>garantie</dd></div>
        </dl>
    </div>
    <?php if ($hero): ?>
    <a class="hero-visual" href="/products/<?= (int) $hero['id'] ?>">
        <img src="<?= e(product_image($hero)) ?>" alt="<?= e($hero['name']) ?>">
        <div class="hero-card">
            <span class="muted">Best-seller</span>
            <strong><?= e($hero['name']) ?></strong>
            <span class="price"><?= money((int) $hero['price_cents']) ?></span>
        </div>
        <div class="hero-chip"><svg class="i" viewBox="0 0 24 24"><use href="/assets/img/icons.svg#check"/></svg> En stock</div>
    </a>
    <?php endif; ?>
</section>

<section class="brands" aria-label="Marques distribuées">
    <span>Marques distribuées</span>
    <div><b>CISCO</b><b>UBIQUITI</b><b>TP-LINK</b><b>FORTINET</b><b>NETGEAR</b><b>HUAWEI</b><b>D-LINK</b></div>
</section>

<section>
    <div class="section-head"><div><span class="kicker">Catégories</span><h2>Tout pour votre infrastructure</h2></div><a href="/products" class="link-arrow">Tout le catalogue →</a></div>
    <div class="categories">
        <?php foreach ($categories as $c): ?>
            <a class="category cat-<?= e($c['icon']) ?>" href="/products?category=<?= e($c['slug']) ?>">
                <span class="category-icon"><img src="/assets/img/<?= e($c['icon']) ?>.svg" alt=""></span>
                <span class="category-name"><?= e($c['name']) ?></span>
                <small><?= (int) $c['product_count'] ?> produits</small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <div class="section-head"><div><span class="kicker">Sélection</span><h2>Les plus demandés</h2></div><a href="/products" class="link-arrow">Voir tout →</a></div>
    <div class="grid">
        <?php foreach ($featured as $product): ?>
            <?= $view->partial('partials/product_card', ['product' => $product]) ?>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($promo): ?>
<section class="promo">
    <div>
        <span class="kicker kicker-light">Sécurité réseau</span>
        <h2>Protégez votre entreprise avec un pare-feu nouvelle génération.</h2>
        <p>Filtrage applicatif, VPN et inspection du trafic : installez-le en une après-midi, nos conseillers vous accompagnent.</p>
        <a class="btn btn-lg btn-white" href="/products/<?= (int) $promo['id'] ?>">Découvrir — <?= money((int) $promo['price_cents']) ?></a>
    </div>
    <img src="<?= e(product_image($promo)) ?>" alt="<?= e($promo['name']) ?>">
</section>
<?php endif; ?>

<section class="perks">
    <?php foreach ([
        ['truck', 'Livraison offerte', 'Partout en Tunisie, sous 48 h.'],
        ['cash', 'Paiement à la livraison', 'Vous payez à la réception.'],
        ['shield', 'Garantie constructeur', 'Jusqu\'à 2 ans sur le matériel.'],
        ['headset', 'Conseil d\'experts', 'Aide au choix et à l\'installation.'],
    ] as [$ic, $t, $d]): ?>
        <div class="perk"><span class="perk-icon"><svg class="i" viewBox="0 0 24 24"><use href="/assets/img/icons.svg#<?= $ic ?>"/></svg></span><div><strong><?= $t ?></strong><span><?= $d ?></span></div></div>
    <?php endforeach; ?>
</section>
