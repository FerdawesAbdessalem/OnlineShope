<?php /** @var App\View $view @var array $featured @var array $categories */ ?>
<section class="hero">
    <div class="hero-text">
        <span class="eyebrow">Routeurs · Switches · Wi-Fi · Sécurité</span>
        <h1>Le matériel réseau fiable, livré chez vous.</h1>
        <p>Équipez votre maison ou votre entreprise avec des marques reconnues — conseils, stock en temps réel et paiement à la livraison.</p>
        <div class="hero-actions">
            <a class="btn btn-lg" href="/products">Voir le catalogue</a>
            <a class="btn btn-lg btn-ghost" href="/products?category=securite">Sécurité réseau</a>
        </div>
    </div>
    <div class="hero-visual" aria-hidden="true">
        <img src="/assets/img/router.svg" alt="">
    </div>
</section>

<section>
    <div class="section-head"><h2>Catégories</h2></div>
    <div class="categories">
        <?php foreach ($categories as $c): ?>
            <a class="category" href="/products?category=<?= e($c['slug']) ?>">
                <img src="/assets/img/<?= e($c['icon']) ?>.svg" alt="">
                <span><?= e($c['name']) ?></span>
                <small><?= (int) $c['product_count'] ?> produits</small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <div class="section-head"><h2>Sélection du moment</h2><a href="/products">Tout voir →</a></div>
    <div class="grid">
        <?php foreach ($featured as $product): ?>
            <?= $view->partial('partials/product_card', ['product' => $product]) ?>
        <?php endforeach; ?>
    </div>
</section>

<section class="perks">
    <div><strong>Stock en temps réel</strong><span>Les quantités sont vérifiées à chaque commande.</span></div>
    <div><strong>Paiement à la livraison</strong><span>Vous payez à la réception du colis.</span></div>
    <div><strong>Suivi de commande</strong><span>Statut visible depuis votre espace client.</span></div>
</section>
