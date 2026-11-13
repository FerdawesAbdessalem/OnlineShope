<div class="auth">
    <form action="/login" method="post" class="card form">
        <?= csrf_field() ?>
        <h1>Connexion</h1>
        <p class="muted">Accédez à vos commandes et finalisez vos achats.</p>
        <div class="field">
            <label for="email">Adresse e-mail</label>
            <input id="email" name="email" type="email" value="<?= e(old('email')) ?>" autocomplete="email" required autofocus>
        </div>
        <div class="field">
            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="btn btn-lg btn-block">Se connecter</button>
        <p class="center">Pas encore de compte ? <a href="/register">Créer un compte</a></p>
        <p class="demo-hint">Démo — client : <code>client@mfbuy.test</code> / <code>Client@2025</code> · admin : <code>admin@mfbuy.test</code> / <code>Admin@2025</code></p>
    </form>
</div>
