<?php
$classname = isset($errors[$input['name']][0]) ? ' form__input-section--error' : '';
$required = boolval($input['required']) === true ? ' <span class="form__input-required">*</span>' : '';
$input_id = $input['form'] === 'adding-post' ? ($_GET['tab'] ?? '') . "-{$input['name']}" : "{$input['form']}-{$input['name']}";
?>

<div class="<?= esc($input['form']) ?>__input-wrapper form__input-wrapper<?= $classname ?>">

    <label class="<?= esc($input['form']) ?>__label form__label" for="<?= esc($input_id) ?>">
        <?= esc($input['label']) ?>
        <?= $required ?>
    </label>

    <div class="form__input-section">
        <input
            class="<?= esc($input['form']) ?>__input form__input"
            id="<?= esc($input_id) ?>"
            type="<?= esc($input['type']) ?>"
            name="<?= esc($input['name']) ?>"
            value="<?= esc(get_post_value($input['name'])) ?>"
            placeholder="<?= esc($input['placeholder']) ?>"
        >

        <button class="form__error-button button" type="button">
            !<span class="visually-hidden">Информация об ошибке</span>
        </button>

        <div class="form__error-text">
            <h3 class="form__error-title"><?= esc($input['label']) ?></h3>
            <p class="form__error-desc"><?= esc($errors[$input['name']][0] ?? '') ?></p>
        </div>
    </div>
</div>
