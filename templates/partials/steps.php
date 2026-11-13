<?php /** @var int $current 1 = cart, 2 = delivery, 3 = confirmation */ ?>
<ol class="steps">
    <?php foreach (['Panier', 'Livraison', 'Confirmation'] as $i => $label): $n = $i + 1; ?>
        <li class="<?= $n < $current ? 'done' : ($n === $current ? 'current' : '') ?>"><span><?= $n < $current ? '✓' : $n ?></span><?= $label ?></li>
    <?php endforeach; ?>
</ol>
