<div style="<?= $style ?? '' ?>padding-bottom: 10px;" class="form__invalid-block">
    <b class="form__invalid-slogan">Пожалуйста, исправьте следующие ошибки:</b>
    <ul class="form__invalid-list">

        <?php foreach ($errors as $error): ?>

            <?php if (isset($error[1], $error[0])): ?>
                <li class="form__invalid-item"><?= "{$error[1]}. {$error[0]}." ?></li>
            <?php endif; ?>

        <?php endforeach; ?>

    </ul>
</div>
