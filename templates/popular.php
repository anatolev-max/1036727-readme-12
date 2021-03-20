<div class="container">
    <h1 class="page__title page__title--popular">Популярное</h1>
</div>
<div class="popular container">
    <div class="popular__filters-wrapper">
        <div class="popular__sorting sorting">
            <b class="popular__sorting-caption sorting__caption">Сортировка:</b>
            <ul class="popular__sorting-list sorting__list">
                <li class="sorting__item sorting__item--popular">
                    <?php $classname = get_sorting_link_class($sort_fields[0]) ?>
                    <?php $url = get_sorting_link_url($sort_fields[0], $sort_types) ?>
                    <a class="sorting__link<?= $classname ?>" href="<?= $url ?>">
                        <span>Популярность</span>
                        <svg class="sorting__icon" width="10" height="12">
                            <use xlink:href="#icon-sort"></use>
                        </svg>
                    </a>
                </li>
                <li class="sorting__item">
                    <?php $classname = get_sorting_link_class($sort_fields[1]) ?>
                    <?php $url = get_sorting_link_url($sort_fields[1], $sort_types) ?>
                    <a class="sorting__link<?= $classname ?>" href="<?= $url ?>">
                        <span>Лайки</span>
                        <svg class="sorting__icon" width="10" height="12">
                            <use xlink:href="#icon-sort"></use>
                        </svg>
                    </a>
                </li>
                <li class="sorting__item">
                    <?php $classname = get_sorting_link_class($sort_fields[2]) ?>
                    <?php $url = get_sorting_link_url($sort_fields[2], $sort_types) ?>
                    <a class="sorting__link<?= $classname ?>" href="<?= $url ?>">
                        <span>Дата</span>
                        <svg class="sorting__icon" width="10" height="12">
                            <use xlink:href="#icon-sort"></use>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
        <div class="popular__filters filters">
            <b class="popular__filters-caption filters__caption">Тип контента:</b>
            <ul class="popular__filters-list filters__list">
                <li class="popular__filters-item popular__filters-item--all filters__item filters__item--all">
                    <?php $classname = !isset($_GET['filter']) ? ' filters__button--active' : ''; ?>
                    <a class="filters__button filters__button--ellipse filters__button--all<?= $classname ?>" href="/popular.php">
                        <span>Все</span>
                    </a>
                </li>
                <?php foreach ($content_types as $type): ?>
                <li class="popular__filters-item filters__item">
                    <?php $classname = isset($_GET['filter']) && $_GET['filter'] === $type['class_name'] ? ' filters__button--active' : ''; ?>
                    <a class="filters__button filters__button--<?= esc($type['class_name']) ?> button<?= $classname ?>" href="/popular.php?filter=<?= esc($type['class_name']) ?>">
                        <span class="visually-hidden"><?= esc($type['type_name']) ?></span>
                        <svg class="filters__icon" width="<?= esc($type['icon_width']) ?>" height="<?= esc($type['icon_height']) ?>">
                            <use xlink:href="#icon-filter-<?= esc($type['class_name']) ?>"></use>
                        </svg>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="popular__posts">
        <?php foreach ($posts as $post): ?>
        <article class="popular__post post post-<?= esc($post['class_name']) ?>">
            <header class="post__header">
                <h2 style="padding-bottom: 24px;">
                    <a href="/post.php?id=<?= esc($post['id']) ?>"><?= esc($post['title']) ?></a>
                </h2>
            </header>
            <div class="post__main">
                <?php if ($post['class_name'] === 'quote'): ?>
                <?= include_template('inc/post-quote.php', ['post' => $post]) ?>

                <?php elseif ($post['class_name'] === 'link'): ?>
                <?= include_template('inc/post-link.php', ['post' => $post]) ?>

                <?php elseif ($post['class_name'] === 'photo'): ?>
                <?= include_template('inc/post-photo.php', ['post' => $post]) ?>

                <?php elseif ($post['class_name'] === 'video'): ?>
                <?= include_template('inc/post-video.php', ['post' => $post]) ?>

                <?php elseif ($post['class_name'] === 'text'): ?>
                <?= include_template('inc/post-text.php', ['post' => $post]) ?>
                <?php endif; ?>
            </div>
            <footer class="post__footer">
                <div class="post__author">
                    <a class="post__author-link" href="/profile.php?id=<?= $post['author_id'] ?>&tab=posts" title="Автор">
                        <div class="post__avatar-wrapper">
                            <?php if (!empty($post['avatar_path'])): ?>
                            <?php $style = 'width: 40px; height: 40px; object-fit: cover;'; ?>
                            <img style="<?= $style ?>" class="post__author-avatar" src="uploads/<?= esc($post['avatar_path']) ?>" width="40" height="40" alt="Аватар пользователя">
                            <?php endif; ?>
                        </div>
                        <div class="post__info">
                            <b class="post__author-name"><?= esc($post['author']) ?></b>
                            <time class="post__time" datetime="<?= get_datetime_value($post['dt_add']) ?>" title="<?= get_time_title($post['dt_add']) ?>"><?= get_relative_time($post['dt_add']) ?> назад</time>
                        </div>
                    </a>
                </div>
                <div class="post__indicators">
                    <div class="post__buttons">
                        <a class="post__indicator post__indicator--likes<?= get_likes_indicator_class($link, $post['id']) ?> button" href="/like.php?id=<?= esc($post['id']) ?>" title="Лайк">
                            <svg class="post__indicator-icon" width="20" height="17">
                                <use xlink:href="#icon-heart"></use>
                            </svg>
                            <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                                <use xlink:href="#icon-heart-active"></use>
                            </svg>
                            <span><?= get_likes_count($link, $post['id']) ?></span>
                            <span class="visually-hidden">количество лайков</span>
                        </a>
                        <a class="post__indicator post__indicator--comments button" href="/post.php?id=<?= esc($post['id']) ?>#form" title="Комментарии">
                            <svg class="post__indicator-icon" width="19" height="17">
                                <use xlink:href="#icon-comment"></use>
                            </svg>
                            <span><?= get_comment_count($link, $post['id']) ?></span>
                            <span class="visually-hidden">количество комментариев</span>
                        </a>
                    </div>
                </div>
            </footer>
        </article>
        <?php endforeach; ?>
    </div>
    <?php if ($pages_count > 1): ?>
    <div class="popular__page-links">
        <?php $href = $current_page > 1 ? ' href="' . get_page_link_url($current_page, false) . '"' : ''; ?>
        <a class="popular__page-link popular__page-link--prev button button--gray"<?= $href ?>>Предыдущая страница</a>
        <?php $href = $current_page < $pages_count ? ' href="' . get_page_link_url($current_page, true) . '"' : ''; ?>
        <a class="popular__page-link popular__page-link--next button button--gray"<?= $href ?>>Следующая страница</a>
    </div>
    <?php endif; ?>
</div>
