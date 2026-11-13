<?php
/** @var App\App $app */
/** @var string $content */
$user = $app->auth->user();
$cartCount = $app->cart->count();
$isAdminPage = str_starts_with((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/admin');
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="<?= $isAdminPage ? 'is-admin' : '' ?>">
<header class="topbar">
    <div class="container topbar-inner">
        <a class="brand" href="/"><img src="/assets/img/logo.svg" alt="" width="34" height="34"> M&amp;F <span>Buy</span></a>

        <form class="search" action="/products" method="get" role="search">
            <input type="search" name="q" placeholder="Rechercher un routeur, un switch…" value="<?= e($_GET['q'] ?? '') ?>" aria-label="Rechercher">
            <button type="submit" aria-label="Lancer la recherche">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M10 2a8 8 0 0 1 6.32 12.9l5.39 5.4-1.41 1.4-5.4-5.38A8 8 0 1 1 10 2zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12z"/></svg>
            </button>
        </form>

        <nav class="nav">
            <a href="/products">Catalogue</a>
            <?php if ($user && $user['role'] === 'admin'): ?>
                <a href="/admin">Administration</a>
            <?php endif; ?>
            <?php if ($user): ?>
                <a href="/orders">Mes commandes</a>
                <form action="/logout" method="post" class="inline"><?= csrf_field() ?><button class="link">Déconnexion</button></form>
            <?php else: ?>
                <a href="/login">Connexion</a>
            <?php endif; ?>
            <a class="cart-link" href="/cart" aria-label="Panier, <?= $cartCount ?> article(s)">
                <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M7 18a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm10 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM1 2h3.27l.94 2H21a1 1 0 0 1 .96 1.27l-3 10A1 1 0 0 1 18 16H7a1 1 0 0 1-.9-.57L3 4H1V2zm5.1 4 2.53 8h8.63l2.4-8H6.1z"/></svg>
                <?php if ($cartCount > 0): ?><span class="badge"><?= $cartCount ?></span><?php endif; ?>
            </a>
        </nav>
    </div>
</header>

<?php if ($isAdminPage && $user && $user['role'] === 'admin'): ?>
<div class="admin-bar">
    <div class="container">
        <a href="/admin">Tableau de bord</a>
        <a href="/admin/products">Produits</a>
        <a href="/admin/categories">Catégories</a>
        <a href="/admin/orders">Commandes</a>
    </div>
</div>
<?php endif; ?>

<main class="container main">
    <?php foreach (flash() as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
</main>

<footer class="footer">
    <div class="container footer-inner">
        <div>
            <strong>M&amp;F Buy</strong>
            <p>Matériel réseau pour professionnels et particuliers. Livraison partout en Tunisie.</p>
        </div>
        <div class="footer-meta">
            <span>Paiement à la livraison</span>
            <span>Garantie constructeur</span>
            <span>© <?= date('Y') ?> M&amp;F Buy</span>
        </div>
    </div>
</footer>
</body>
</html>
