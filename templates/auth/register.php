<?php /** @var array $errors */
$err = static fn (string $f): string => isset($errors[$f]) ? '<span class="error">' . e($errors[$f]) . '</span>' : '';
?>
<div class="auth">
    <form action="/register" method="post" class="card form" novalidate>
        <?= csrf_field() ?>
        <h1>Créer un compte</h1>
        <div class="field <?= isset($errors['name']) ? 'has-error' : '' ?>">
            <label for="name">Nom complet</label>
            <input id="name" name="name" value="<?= e(old('name')) ?>" autocomplete="name" required>
            <?= $err('name') ?>
        </div>
        <div class="field <?= isset($errors['email']) ? 'has-error' : '' ?>">
            <label for="email">Adresse e-mail</label>
            <input id="email" name="email" type="email" value="<?= e(old('email')) ?>" autocomplete="email" required>
            <?= $err('email') ?>
        </div>
        <div class="field <?= isset($errors['password']) ? 'has-error' : '' ?>">
            <label for="password">Mot de passe <small class="muted">(8 caractères minimum)</small></label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
            <?= $err('password') ?>
        </div>
        <div class="field">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>
        <button class="btn btn-lg btn-block">Créer mon compte</button>
        <p class="center">Déjà inscrit ? <a href="/login">Se connecter</a></p>
    </form>
</div>
