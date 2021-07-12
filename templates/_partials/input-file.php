<?php
$classname = $input['form'] === 'adding-post' ? ' adding-post__file-zone--photo' : '';
$script = "document.querySelector('.adding-post__input-file-button span').textContent = this.files[0].name";
?>

<div class="<?= esc($input['form']) ?>__input-file-wrapper form__input-file-wrapper">
    <div class="<?= esc($input['form']) ?>__file-zone<?= $classname ?> form__file-zone dropzone">

        <input
            class="<?= esc($input['form']) ?>__input-file form__input-file"
            id="<?= esc($input['name']) ?>"
            type="file"
            name="<?= esc($input['name']) ?>"
            title=""
        >

        <div class="form__file-zone-text">
            <span>Перетащите фото сюда</span>
        </div>

    </div>
    <button class="<?= esc($input['form']) ?>__input-file-button form__input-file-button form__input-file-button--photo button" type="button">
        <span>Выбрать фото</span>
        <svg class="<?= esc($input['form']) ?>__attach-icon form__attach-icon" width="10" height="20">
            <use xlink:href="#icon-attach"></use>
        </svg>
    </button>
</div>
