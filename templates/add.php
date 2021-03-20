<div class="page__main-section">
    <div class="container">
        <h1 class="page__title page__title--adding-post">Добавить публикацию</h1>
    </div>
    <div class="adding-post container">
        <div class="adding-post__tabs-wrapper tabs">
            <div class="adding-post__tabs filters">
                <ul class="adding-post__tabs-list filters__list tabs__list">
                    <?php foreach ($content_types as $type): ?>
                    <li class="adding-post__tabs-item filters__item">
                        <?php $classname = isset($_GET['tab']) && $_GET['tab'] === $type['class_name'] ? 'filters__button--active tabs__item--active' : ''; ?>
                        <?php $url = '/add.php?tab=' . esc($type['class_name']); ?>
                        <a class="adding-post__tabs-link filters__button filters__button--<?= esc($type['class_name']) ?> tabs__item button <?= $classname ?>" href="<?= $url ?>">
                            <svg class="filters__icon" width="<?= esc($type['icon_width']) ?>" height="<?= esc($type['icon_height']) ?>">
                                <use xlink:href="#icon-filter-<?= esc($type['class_name']) ?>"></use>
                            </svg>
                            <span><?= esc($type['type_name']) ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="adding-post__tab-content">
                <section class="adding-post__photo tabs__content<?php if (isset($_GET['tab']) && $_GET['tab'] === 'photo'): ?> tabs__content--active<?php endif; ?>">
                    <h2 class="visually-hidden">Форма добавления фото</h2>
                    <?php $keys = ['heading', 'image-url', 'tags', 'file-photo']; ?>
                    <?php if (!array_diff_key(array_flip($keys), $inputs)): ?>
                    <form class="adding-post__form form" action="/add.php?tab=photo" method="post" enctype="multipart/form-data">
                        <div class="form__text-inputs-wrapper">
                            <div class="form__text-inputs">
                                <?php $data = ['errors' => $errors, 'input' => $inputs['heading']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>

                                <?php $data = ['errors' => $errors, 'input' => $inputs['image-url']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>

                                <?php $data = ['errors' => $errors, 'input' => $inputs['tags']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>
                                <input type="hidden" name="content-type" value="photo">
                            </div>
                            <?php if (!empty($errors)): ?>
                            <?php $data = ['errors' => $errors]; ?>
                            <?= include_template('inc/invalid-block.php', $data) ?>
                            <?php endif; ?>
                        </div>
                        <div class="adding-post__input-file-container form__input-container form__input-container--file">
                            <?= include_template('inc/input-file.php', ['input' => $inputs['file-photo']]) ?>
                            <div class="adding-post__file adding-post__file--photo form__file dropzone-previews"></div>
                        </div>
                        <div class="adding-post__buttons">
                            <button class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                            <?php $url = get_adding_post_close_url(); ?>
                            <a class="adding-post__close" href="<?= $url ?>">Закрыть</a>
                        </div>
                    </form>
                    <?php endif; ?>
                </section>

                <section class="adding-post__video tabs__content<?php if (isset($_GET['tab']) && $_GET['tab'] === 'video'): ?> tabs__content--active<?php endif; ?>">
                    <h2 class="visually-hidden">Форма добавления видео</h2>
                    <?php $keys = ['heading', 'video-url', 'tags']; ?>
                    <?php if (!array_diff_key(array_flip($keys), $inputs)): ?>
                    <form class="adding-post__form form" action="/add.php?tab=video" method="post" enctype="multipart/form-data">
                        <div class="form__text-inputs-wrapper">
                            <div class="form__text-inputs">
                                <?php $data = ['errors' => $errors, 'input' => $inputs['heading']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>

                                <?php $data = ['errors' => $errors, 'input' => $inputs['video-url']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>

                                <?php $data = ['errors' => $errors, 'input' => $inputs['tags']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>
                                <input type="hidden" name="content-type" value="video">
                            </div>
                            <?php if (!empty($errors)): ?>
                            <?php $data = ['errors' => $errors]; ?>
                            <?= include_template('inc/invalid-block.php', $data) ?>
                            <?php endif; ?>
                        </div>
                        <div class="adding-post__buttons">
                            <button style="margin-top: 18px;" class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                            <a class="adding-post__close" href="<?= $url ?>">Закрыть</a>
                        </div>
                    </form>
                    <?php endif; ?>
                </section>

                <section class="adding-post__text tabs__content<?php if (isset($_GET['tab']) && $_GET['tab'] === 'text'): ?> tabs__content--active<?php endif; ?>">
                    <h2 class="visually-hidden">Форма добавления текста</h2>
                    <?php $keys = ['heading', 'post-text', 'tags']; ?>
                    <?php if (!array_diff_key(array_flip($keys), $inputs)): ?>
                    <form class="adding-post__form form" action="/add.php?tab=text" method="post">
                        <div class="form__text-inputs-wrapper">
                            <div class="form__text-inputs">
                                <?php $data = ['errors' => $errors, 'input' => $inputs['heading']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>

                                <?php $data = ['errors' => $errors, 'input' => $inputs['post-text']]; ?>
                                <?= include_template('inc/textarea.php', $data) ?>

                                <?php $data = ['errors' => $errors, 'input' => $inputs['tags']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>
                                <input type="hidden" name="content-type" value="text">
                            </div>
                            <?php if (!empty($errors)): ?>
                            <?php $data = ['errors' => $errors]; ?>
                            <?= include_template('inc/invalid-block.php', $data) ?>
                            <?php endif; ?>
                        </div>
                        <div class="adding-post__buttons">
                            <button style="margin-top: 18px;" class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                            <a class="adding-post__close" href="<?= $url ?>">Закрыть</a>
                        </div>
                    </form>
                    <?php endif; ?>
                </section>

                <section class="adding-post__quote tabs__content<?php if (isset($_GET['tab']) && $_GET['tab'] === 'quote'): ?> tabs__content--active<?php endif; ?>">
                    <h2 class="visually-hidden">Форма добавления цитаты</h2>
                    <?php $keys = ['heading', 'cite-text', 'quote-author', 'tags']; ?>
                    <?php if (!array_diff_key(array_flip($keys), $inputs)): ?>
                    <form class="adding-post__form form" action="/add.php?tab=quote" method="post">
                        <div class="form__text-inputs-wrapper">
                            <div class="form__text-inputs">
                                <?php $data = ['errors' => $errors, 'input' => $inputs['heading']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>

                                <?php $data = ['errors' => $errors, 'input' => $inputs['cite-text']]; ?>
                                <?= include_template('inc/textarea.php', $data) ?>

                                <?php $data = ['errors' => $errors, 'input' => $inputs['quote-author']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>

                                <?php $data = ['errors' => $errors, 'input' => $inputs['tags']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>
                                <input type="hidden" name="content-type" value="quote">
                            </div>
                            <?php if (!empty($errors)): ?>
                            <?php $data = ['errors' => $errors]; ?>
                            <?= include_template('inc/invalid-block.php', $data) ?>
                            <?php endif; ?>
                        </div>
                        <div class="adding-post__buttons">
                            <button style="margin-top: 18px;" class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                            <a class="adding-post__close" href="<?= $url ?>">Закрыть</a>
                        </div>
                    </form>
                    <?php endif; ?>
                </section>

                <section class="adding-post__link tabs__content<?php if (isset($_GET['tab']) && $_GET['tab'] === 'link'): ?> tabs__content--active<?php endif; ?>">
                    <h2 class="visually-hidden">Форма добавления ссылки</h2>
                    <?php $keys = ['heading', 'post-link', 'tags']; ?>
                    <?php if (!array_diff_key(array_flip($keys), $inputs)): ?>
                    <form class="adding-post__form form" action="/add.php?tab=link" method="post">
                        <div class="form__text-inputs-wrapper">
                            <div class="form__text-inputs">
                                <?php $data = ['errors' => $errors, 'input' => $inputs['heading']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>

                                <?php $data = ['errors' => $errors, 'input' => $inputs['post-link']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>

                                <?php $data = ['errors' => $errors, 'input' => $inputs['tags']]; ?>
                                <?= include_template('inc/input-text.php', $data) ?>
                                <input type="hidden" name="content-type" value="link">
                            </div>
                            <?php if (!empty($errors)): ?>
                            <?php $data = ['errors' => $errors]; ?>
                            <?= include_template('inc/invalid-block.php', $data) ?>
                            <?php endif; ?>
                        </div>
                        <div class="adding-post__buttons">
                            <button style="margin-top: 18px;" class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                            <a class="adding-post__close" href="<?= $url ?>">Закрыть</a>
                        </div>
                    </form>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</div>
