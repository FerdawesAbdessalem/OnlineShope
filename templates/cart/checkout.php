<?php /** @var array $summary @var array $user @var array $errors */
$field = static function (string $name, string $label, string $type = 'text', string $default = '') use ($errors): string {
    $error = $errors[$name] ?? null;
    return '<div class="field' . ($error ? ' has-error' : '') . '">'
        . '<label for="' . $name . '">' . e($label) . '</label>'
        . '<input id="' . $name . '" name="' . $name . '" type="' . $type . '" value="' . e(old($name, $default)) . '" required>'
        . ($error ? '<span class="error">' . e($error) . '</span>' : '')
        . '</div>';
};
?>
<?= $view->partial('partials/steps', ['current' => 2]) ?>
<h1>Finaliser la commande</h1>
<div class="cart-layout">
    <form action="/checkout" method="post" class="card form" novalidate>
        <?= csrf_field() ?>
        <h2>Adresse de livraison</h2>
        <?= $field('name', 'Nom complet', 'text', $user['name']) ?>
        <?= $field('address', 'Adresse') ?>
        <div class="field-row">
            <?= $field('city', 'Ville') ?>
            <?= $field('phone', 'Téléphone', 'tel') ?>
        </div>
        <h2>Paiement</h2>
        <label class="radio-card"><input type="radio" checked disabled> Paiement à la livraison (espèces ou carte)</label>
        <button class="btn btn-lg btn-block">Confirmer la commande · <?= money($summary['total']) ?></button>
    </form>

    <aside class="card summary">
        <h2>Votre commande</h2>
        <?php foreach ($summary['lines'] as $line): ?>
            <div class="summary-row"><span><?= (int) $line['quantity'] ?> × <?= e($line['product']['name']) ?></span><span><?= money($line['subtotal']) ?></span></div>
        <?php endforeach; ?>
        <div class="summary-row total"><span>Total</span><span><?= money($summary['total']) ?></span></div>
    </aside>
</div>
