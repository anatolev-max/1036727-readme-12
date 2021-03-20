<div class="container">
    <h1 class="page__title page__title--registration">Регистрация</h1>
</div>
<section class="registration container">
    <h2 class="visually-hidden">Форма регистрации</h2>
    <?php $keys = ['email', 'login', 'password', 'password-repeat', 'avatar']; ?>
    <?php if (!array_diff_key(array_flip($keys), $inputs)): ?>
    <form class="registration__form form" action="/register.php" method="post" enctype="multipart/form-data">
        <div class="form__text-inputs-wrapper">
            <div class="form__text-inputs">
                <?php $data = ['errors' => $errors, 'input' => $inputs['email']]; ?>
                <?= include_template('inc/input-text.php', $data) ?>

                <?php $data = ['errors' => $errors, 'input' => $inputs['login']]; ?>
                <?= include_template('inc/input-text.php', $data) ?>

                <?php $data = ['errors' => $errors, 'input' => $inputs['password']]; ?>
                <?= include_template('inc/input-text.php', $data) ?>

                <?php $data = ['errors' => $errors, 'input' => $inputs['password-repeat']]; ?>
                <?= include_template('inc/input-text.php', $data) ?>
            </div>
            <?php if (!empty($errors)): ?>
            <?php $data = ['errors' => $errors]; ?>
            <?= include_template('inc/invalid-block.php', $data) ?>
            <?php endif; ?>
        </div>
        <div class="registration__input-file-container form__input-container form__input-container--file">
            <?= include_template('inc/input-file.php', ['input' => $inputs['avatar']]) ?>
            <div class="registration__file form__file dropzone-previews"></div>
        </div>
        <button class="registration__submit button button--main" type="submit">Отправить</button>
    </form>
    <?php endif; ?>
</section>
