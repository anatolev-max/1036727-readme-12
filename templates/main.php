<div class="container">
    <h1 class="page__title page__title--feed">Моя лента</h1>
</div>
<div class="page__main-wrapper container">
    <section class="feed">
        <h2 class="visually-hidden">Лента</h2>
        <?php $style = !empty($posts) ? 'background-image: none;' : ''; ?>
        <div style="<?= $style ?>" class="feed__main-wrapper">
            <div class="feed__wrapper">

                <?php foreach ($posts as $post): ?>
                    <article class="feed__post post post-<?= esc($post['class_name']) ?>">
                        <header class="post__header post__author">
                            <a class="post__author-link" href="/profile.php?id=<?= $post['author_id'] ?>&tab=posts" title="Автор">
                                <div class="post__avatar-wrapper">

                                    <?php if (!empty($post['avatar_path'])): ?>
                                        <img
                                            style="width: 60px; height: 60px; object-fit: cover;"
                                            class="post__author-avatar"
                                            src="uploads/<?= esc($post['avatar_path']) ?>"
                                            width="60"
                                            height="60"
                                            alt="Аватар пользователя"
                                        >
                                    <?php endif; ?>

                                </div>
                                <div class="post__info">
                                    <b class="post__author-name"><?= esc($post['author']) ?></b>
                                    <span class="post__time"><?= get_relative_time($post['dt_add']) ?> назад</span>
                                </div>
                            </a>
                        </header>
                        <div style="min-height: 141px;" class="post__main">
                            <?php if (in_array($post['class_name'], ['photo', 'text'])): ?>
                                <h2><a href="/post.php?id=<?= esc($post['id']) ?>&comments=2"><?= esc($post['title']) ?></a></h2>
                            <?php endif; ?>

                            <?php $post['display_mode'] = 'feed'; ?>
                            <?php if ($post['class_name'] === 'quote'): ?>
                                <?= include_template('_partials/post-quote.php', ['post' => $post]) ?>

                            <?php elseif ($post['class_name'] === 'link'): ?>
                                <?= include_template('_partials/post-link.php', ['post' => $post]) ?>

                            <?php elseif ($post['class_name'] === 'photo'): ?>
                                <?= include_template('_partials/post-photo.php', ['post' => $post]) ?>

                            <?php elseif ($post['class_name'] === 'video'): ?>
                                <?= include_template('_partials/post-video.php', ['post' => $post]) ?>

                            <?php elseif ($post['class_name'] === 'text'): ?>
                                <?= include_template('_partials/post-text.php', ['post' => $post]) ?>
                            <?php endif; ?>
                        </div>
                        <footer style="flex-direction: column;" class="post__footer post__indicators">
                            <div class="post__buttons">
                                <?php $classname = $post['is_like'] ? ' post__indicator--likes-active' : ''; ?>
                                <a class="post__indicator post__indicator--likes<?= $classname ?> button" href="/like.php?id=<?= esc($post['id']) ?>" title="Лайк">
                                    <svg class="post__indicator-icon" width="20" height="17">
                                        <use xlink:href="#icon-heart"></use>
                                    </svg>
                                    <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                                        <use xlink:href="#icon-heart-active"></use>
                                    </svg>
                                    <span><?= esc($post['like_count']) ?></span>
                                    <span class="visually-hidden">количество лайков</span>
                                </a>
                                <a class="post__indicator post__indicator--comments button" href="/post.php?id=<?= esc($post['id']) ?>#form" title="Комментарии">
                                    <svg class="post__indicator-icon" width="19" height="17">
                                        <use xlink:href="#icon-comment"></use>
                                    </svg>
                                    <span><?= esc($post['comment_count']) ?></span>
                                    <span class="visually-hidden">количество комментариев</span>
                                </a>
                                <a class="post__indicator post__indicator--repost button" href="/repost.php?id=<?= esc($post['id']) ?>" title="Репост">
                                    <svg class="post__indicator-icon" width="19" height="17">
                                        <use xlink:href="#icon-repost"></use>
                                    </svg>
                                    <span><?= esc($post['repost_count']) ?></span>
                                    <span class="visually-hidden">количество репостов</span>
                                </a>
                            </div>

                            <?php if (!empty($post['hashtags'])): ?>
                                <ul style="margin: 0 0 0 -3px; padding: 23px 0 0 0;" class="post__tags">

                                    <?php foreach ($post['hashtags'] as $hashtag): ?>
                                        <li><a href="/search.php?q=%23<?= esc($hashtag['name']) ?>">#<?= esc($hashtag['name']) ?></a></li>
                                    <?php endforeach; ?>

                                </ul>
                            <?php endif; ?>

                        </footer>
                    </article>
                <?php endforeach; ?>

            </div>
        </div>
        <ul class="feed__filters filters">
            <li class="feed__filters-item filters__item">
                <?php $classname = !isset($_GET['filter']) ? ' filters__button--active' : ''; ?>
                <a class="filters__button<?= $classname ?>" href="/feed.php">
                    <span>Все</span>
                </a>
            </li>

            <?php foreach ($content_types as $type): ?>
                <li class="feed__filters-item filters__item">
                    <?php $classname = isset($_GET['filter']) && $_GET['filter'] === $type['class_name'] ? ' filters__button--active' : ''; ?>
                    <a class="filters__button filters__button--<?= esc($type['class_name']) ?> button<?= $classname ?>" href="/feed.php?filter=<?= esc($type['class_name']) ?>">
                        <span class="visually-hidden"><?= esc($type['type_name']) ?></span>
                        <svg class="filters__icon" width="<?= esc($type['icon_width']) ?>" height="<?= esc($type['icon_height']) ?>">
                            <use xlink:href="#icon-filter-<?= esc($type['class_name']) ?>"></use>
                        </svg>
                    </a>
                </li>
            <?php endforeach; ?>

        </ul>
    </section>
    <aside class="promo">
        <article class="promo__block promo__block--barbershop">
            <h2 class="visually-hidden">Рекламный блок</h2>
            <p class="promo__text">Все еще сидишь на окладе в офисе? Открой свой барбершоп по нашей франшизе!</p>
            <a class="promo__link" href="#">Подробнее</a>
        </article>
        <article class="promo__block promo__block--technomart">
            <h2 class="visually-hidden">Рекламный блок</h2>
            <p class="promo__text">Товары будущего уже сегодня в онлайн-сторе Техномарт!</p>
            <a class="promo__link" href="#">Перейти в магазин</a>
        </article>
        <article class="promo__block">
            <h2 class="visually-hidden">Рекламный блок</h2>
            <p class="promo__text">Здесь<br>могла быть<br>ваша реклама</p>
            <a class="promo__link" href="#">Разместить</a>
        </article>
    </aside>
</div>
