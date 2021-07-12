<?php
$classname = isset($errors[$input['name']][0]) ? ' form__input-section--error' : '';
$required = boolval($input['required']) === true ? ' <span class="form__input-required">*</span>' : '';
$textarea_id = isset($_GET['tab']) && $_GET['tab'] !== 'text' ? "{$_GET['tab']}-{$input['name']}" : $input['name'];
?>

<div class="<?= esc($input['form']) ?>__textarea-wrapper form__textarea-wrapper<?= $classname ?>">

    <label class="<?= esc($input['form']) ?>__label form__label" for="<?= esc($textarea_id) ?>">
        <?= esc($input['label']) ?>
        <?= $required ?>
    </label>

    <div class="form__input-section">
        <textarea
            class="<?= esc($input['form']) ?>__textarea form__textarea form__input"
            id="<?= esc($textarea_id) ?>"
            name="<?= esc($input['name']) ?>"
            placeholder="<?= esc($input['placeholder']) ?>"
        ><?= esc(get_post_value($input['name'])) ?></textarea>

        <button class="form__error-button button" type="button">
            !<span class="visually-hidden">Информация об ошибке</span>
        </button>

        <div class="form__error-text">
            <h3 class="form__error-title"><?= esc($input['label']) ?></h3>
            <p class="form__error-desc"><?= esc($errors[$input['name']][0] ?? '') ?></p>
        </div>
    </div>
</div>
