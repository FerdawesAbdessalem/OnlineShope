<?php
/** @var App\App $app */
/** @var string $content */
$user = $app->auth->user();
$cartCount = $app->cart->count();
$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$isAdmin = str_starts_with($path, '/admin') && $user && $user['role'] === 'admin';
$navCategories = $app->categories->all();
$icon = static fn (string $name): string => '<svg class="i" viewBox="0 0 24 24" aria-hidden="true"><use href="/assets/img/icons.svg#' . $name . '"/></svg>';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'M&F Buy') ?> · M&amp;F Buy</title>
    <meta name="description" content="M&F Buy — boutique en ligne de matériel réseau : routeurs, switches, Wi-Fi, câblage et sécurité.">
    <link rel="icon" href="/assets/img/logo.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="<?= $isAdmin ? 'admin' : 'shop' ?>">

<?php if ($isAdmin): ?>
<div class="admin-shell">
    <aside class="sidebar">
        <a class="brand brand-light" href="/"><img src="/assets/img/logo.svg" alt="" width="34" height="34"> M&amp;F <span>Buy</span></a>
        <p class="sidebar-label">Back-office</p>
        <?php foreach ([
            ['/admin', 'grid', 'Tableau de bord'],
            ['/admin/products', 'box', 'Produits'],
            ['/admin/categories', 'tag', 'Catégories'],
            ['/admin/orders', 'receipt', 'Commandes'],
        ] as [$href, $ic, $label]):
            $active = $href === '/admin' ? $path === '/admin' : str_starts_with($path, $href); ?>
            <a class="side-link <?= $active ? 'active' : '' ?>" href="<?= $href ?>"><?= $icon($ic) ?><?= $label ?></a>
        <?php endforeach; ?>
        <div class="sidebar-foot">
            <a class="side-link" href="/"><?= $icon('store') ?>Voir la boutique</a>
            <form action="/logout" method="post"><?= csrf_field() ?><button class="side-link"><?= $icon('logout') ?>Déconnexion</button></form>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-top">
            <span class="muted"><?= e($title ?? '') ?></span>
            <span class="admin-user"><span class="avatar"><?= e(mb_substr($user['name'], 0, 1)) ?></span><?= e($user['name']) ?></span>
        </header>
        <main class="admin-content">
            <?php foreach (flash() as $f): ?>
                <div class="alert alert-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
            <?php endforeach; ?>
            <?= $content ?>
        </main>
    </div>
</div>
<?php else: ?>

<div class="announce">
    <div class="container announce-inner">
        <span><?= $icon('truck') ?>Livraison offerte partout en Tunisie</span>
        <span class="hide-sm"><?= $icon('shield') ?>Garantie constructeur 2 ans</span>
        <span class="hide-sm"><?= $icon('cash') ?>Paiement à la livraison</span>
    </div>
</div>

<header class="topbar">
    <div class="container topbar-inner">
        <a class="brand" href="/"><img src="/assets/img/logo.svg" alt="" width="38" height="38"> M&amp;F <span>Buy</span></a>

        <form class="search" action="/products" method="get" role="search">
            <?= $icon('search') ?>
            <input type="search" name="q" placeholder="Rechercher un routeur, un switch, une borne Wi-Fi…" value="<?= e($_GET['q'] ?? '') ?>" aria-label="Rechercher">
            <button type="submit" class="btn btn-sm">Rechercher</button>
        </form>

        <nav class="nav">
            <?php if ($user): ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a class="nav-item" href="/admin"><?= $icon('grid') ?><span>Admin</span></a>
                <?php endif; ?>
                <a class="nav-item" href="/orders"><?= $icon('receipt') ?><span>Commandes</span></a>
                <form action="/logout" method="post" class="inline"><?= csrf_field() ?><button class="nav-item"><?= $icon('logout') ?><span>Sortir</span></button></form>
            <?php else: ?>
                <a class="nav-item" href="/login"><?= $icon('user') ?><span>Connexion</span></a>
            <?php endif; ?>
            <a class="nav-item cart-link" href="/cart" aria-label="Panier, <?= $cartCount ?> article(s)">
                <?= $icon('cart') ?><span>Panier</span>
                <?php if ($cartCount > 0): ?><b class="badge"><?= $cartCount ?></b><?php endif; ?>
            </a>
        </nav>
    </div>
    <nav class="catbar" aria-label="Catégories">
        <div class="container catbar-inner">
            <a href="/products" class="<?= $path === '/products' && empty($_GET['category']) ? 'active' : '' ?>">Tous les produits</a>
            <?php foreach ($navCategories as $c): ?>
                <a href="/products?category=<?= e($c['slug']) ?>" class="<?= ($_GET['category'] ?? '') === $c['slug'] ? 'active' : '' ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
            <a href="/products?sort=price_asc" class="deal">Petits prix</a>
        </div>
    </nav>
</header>

<main class="container main">
    <?php foreach (flash() as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
</main>

<footer class="footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="brand brand-light" href="/"><img src="/assets/img/logo.svg" alt="" width="34" height="34"> M&amp;F <span>Buy</span></a>
            <p>Le spécialiste du matériel réseau pour les entreprises et les particuliers : routeurs, switches, Wi-Fi, câblage et sécurité.</p>
        </div>
        <div>
            <h4>Boutique</h4>
            <?php foreach (array_slice($navCategories, 0, 5) as $c): ?>
                <a href="/products?category=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <div>
            <h4>Mon compte</h4>
            <a href="/login">Connexion</a>
            <a href="/register">Créer un compte</a>
            <a href="/orders">Suivre mes commandes</a>
            <a href="/cart">Mon panier</a>
        </div>
        <div>
            <h4>Nos engagements</h4>
            <span><?= $icon('truck') ?>Livraison en 48 h</span>
            <span><?= $icon('cash') ?>Paiement à la livraison</span>
            <span><?= $icon('shield') ?>Garantie constructeur</span>
            <span><?= $icon('refresh') ?>Retour sous 14 jours</span>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>© <?= date('Y') ?> M&amp;F Buy — Tous droits réservés.</span>
        <span>Projet de démonstration · PHP 8 · MySQL</span>
    </div>
</footer>
<?php endif; ?>
</body>
</html>
