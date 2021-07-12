<h1 class="visually-hidden">Профиль</h1>
<div class="profile profile--default">
    <div class="profile__user-wrapper">
        <div class="profile__user user container">
            <div class="profile__user-info user__info">
                <div class="profile__avatar user__avatar">

                    <?php if (!empty($user['avatar_path'])): ?>
                        <img
                            style="width: 100px; height: 100px; object-fit: cover;"
                            class="profile__picture user__picture"
                            src="uploads/<?= esc($user['avatar_path']) ?>"
                            width="100"
                            height="100"
                            alt="Аватар пользователя"
                        >
                    <?php endif; ?>

                </div>
                <div class="profile__name-wrapper user__name-wrapper">
                    <span class="profile__name user__name"><?= esc($user['login']) ?></span>
                    <time
                        class="profile__user-time user__time"
                        datetime="<?= get_datetime_value($user['dt_add']) ?>"
                    ><?= get_relative_time($user['dt_add']) ?> на сайте</time>
                </div>
            </div>
            <div class="profile__rating user__rating">
                <p class="profile__rating-item user__rating-item user__rating-item--publications">
                    <?php $publications = get_noun_plural_form($user['publication_count'], ' публикация', ' публикации', ' публикаций'); ?>
                    <span class="user__rating-amount"><?= esc($user['publication_count']) ?></span>
                    <span class="profile__rating-text user__rating-text"><?= $publications ?></span>
                </p>
                <p class="profile__rating-item user__rating-item user__rating-item--subscribers">
                    <?php $subscribers = get_noun_plural_form($user['subscriber_count'], ' подписчик', ' подписчика', ' подписчиков'); ?>
                    <span class="user__rating-amount"><?= esc($user['subscriber_count']) ?></span>
                    <span class="profile__rating-text user__rating-text"><?= $subscribers ?></span>
                </p>
            </div>
            <div class="profile__user-buttons user__buttons">

                <?php if ($user['id'] !== $_SESSION['user']['id']): ?>

                    <?php
                    $classname = $user['is_subscription'] ? 'quartz' : 'main';
                    $text_content = $user['is_subscription'] ? 'Отписаться' : 'Подписаться';
                    ?>

                    <a class="profile__user-button user__button user__button--subscription button button--<?= $classname ?>" href="/subscription.php?id=<?= esc($user['id']) ?>"><?= $text_content ?></a>

                    <?php if ($user['is_subscription']): ?>
                        <a class="profile__user-button user__button user__button--writing button button--green" href="/messages.php?contact=<?= esc($user['id']) ?>">Сообщение</a>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>
    </div>
    <div class="profile__tabs-wrapper tabs">
        <div class="container">
            <div class="profile__tabs filters">
                <b class="profile__tabs-caption filters__caption">Показать:</b>
                <ul class="profile__tabs-list filters__list tabs__list">
                    <li class="profile__tabs-item filters__item">

                        <?php
                        $user_id = esc($user['id']);
                        $classname = isset($_GET['tab']) && $_GET['tab'] === 'posts' ? ' filters__button--active tabs__item--active' : '';
                        $url = isset($_GET['tab']) && $_GET['tab'] === 'posts' ? '#' : "/profile.php?id={$user_id}&tab=posts";
                        ?>

                        <a class="profile__tabs-link filters__button tabs__item button<?= $classname ?>" href="<?= $url ?>">Посты</a>
                    </li>
                    <li class="profile__tabs-item filters__item">

                        <?php
                        $classname = isset($_GET['tab']) && $_GET['tab'] === 'likes' ? ' filters__button--active tabs__item--active' : '';
                        $url = isset($_GET['tab']) && $_GET['tab'] === 'likes' ? '#' : "/profile.php?id={$user_id}&tab=likes";
                        ?>

                        <a class="profile__tabs-link filters__button tabs__item button<?= $classname ?>" href="<?= $url ?>">Лайки</a>
                    </li>
                    <li class="profile__tabs-item filters__item">

                        <?php
                        $classname = isset($_GET['tab']) && $_GET['tab'] === 'subscriptions' ? ' filters__button--active tabs__item--active' : '';
                        $url = isset($_GET['tab']) && $_GET['tab'] === 'subscriptions' ? '#' : "/profile.php?id={$user_id}&tab=subscriptions";
                        ?>

                        <a class="profile__tabs-link filters__button tabs__item button<?= $classname ?>" href="<?= $url ?>">Подписки</a>
                    </li>
                </ul>
            </div>
            <div class="profile__tab-content">
                <?php $classname = isset($_GET['tab']) && $_GET['tab'] === 'posts' ? ' tabs__content--active' : ''; ?>
                <section class="profile__posts tabs__content<?= $classname ?>">
                    <h2 class="visually-hidden">Публикации</h2>

                    <?php foreach ($posts as $post): ?>
                        <article id="article-<?= esc($post['id']) ?>" class="profile__post post post-<?= esc($post['class_name']) ?>">
                            <header class="post__header">

                                <?php if ($post['is_repost']): ?>
                                    <div class="post__author">
                                        <a class="post__author-link" href="/profile.php?id=<?= esc($post['origin']['author_id']) ?>&tab=posts" title="Автор">
                                            <div class="post__avatar-wrapper post__avatar-wrapper--repost">

                                                <?php if (!empty($post['origin']['avatar_path'])): ?>
                                                    <img
                                                        style="width: 60px; height: 60px; object-fit: cover;"
                                                        class="post__author-avatar"
                                                        src="uploads/<?= esc($post['origin']['avatar_path']) ?>"
                                                        width="60"
                                                        height="60"
                                                        alt="Аватар пользователя"
                                                    >
                                                <?php endif; ?>

                                            </div>
                                            <div class="post__info">
                                                <b class="post__author-name">Репост: <?= esc($post['origin']['author']) ?></b>
                                                <time
                                                    class="post__time"
                                                    datetime="<?= get_datetime_value($post['origin']['dt_add']) ?>"
                                                ><?= get_relative_time($post['origin']['dt_add']) ?> назад</time>
                                            </div>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php $style = $post['class_name'] === 'text' ? 'padding: 29px 40px 26px;' : ''; ?>
                                <h2 style="<?= $style . ($post['is_repost'] ? ' padding-top: 4px;' : '') ?>"><a href="/post.php?id=<?= esc($post['id']) ?>&comments=2"><?= esc($post['title']) ?></a></h2>
                            </header>
                            <?php $style = !$post['comments'] ? ($post['hashtags'] ? 'min-height: 67px;' : 'min-height: 83px;') : ''; ?>
                            <div style="<?= $style ?>" class="post__main">
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
                                    <?php $post['style'] = 'margin-top: 0;'; ?>
                                    <?= include_template('_partials/post-text.php', ['post' => $post]) ?>
                                <?php endif; ?>
                            </div>
                            <footer class="post__footer">
                                <div class="post__indicators">
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
                                        <a class="post__indicator post__indicator--repost button" href="/repost.php?id=<?= esc($post['id']) ?>" title="Репост">
                                            <svg class="post__indicator-icon" width="19" height="17">
                                                <use xlink:href="#icon-repost"></use>
                                            </svg>
                                            <span><?= esc($post['repost_count']) ?></span>
                                            <span class="visually-hidden">количество репостов</span>
                                        </a>
                                    </div>
                                    <time
                                        class="post__time"
                                        datetime="<?= get_datetime_value($post['dt_add']) ?>"
                                    ><?= get_relative_time($post['dt_add']) ?> назад</time>
                                </div>

                                <?php if (!empty($post['hashtags'])): ?>
                                    <ul class="post__tags">

                                        <?php foreach ($post['hashtags'] as $hashtag): ?>
                                            <li><a href="/search.php?q=%23<?= esc($hashtag['name']) ?>">#<?= esc($hashtag['name']) ?></a></li>
                                        <?php endforeach; ?>

                                    </ul>
                                <?php endif; ?>

                            </footer>

                            <?php if (!empty($post['comments'])): ?>

                                <?php if (isset($_GET['article']) && $_GET['article'] == $post['id']): ?>
                                    <div class="comments">
                                        <div style="padding-bottom: 11px;" class="comments__list-wrapper">
                                            <ul class="comments__list">

                                                <?php foreach ($post['comments'] as $comment): ?>
                                                    <li class="comments__item user">
                                                        <a class="user__avatar-link" href="/profile.php?id=<?= esc($comment['author_id']) ?>&tab=posts">
                                                            <div class="comments__avatar">

                                                                <?php if (!empty($comment['avatar_path'])): ?>
                                                                    <img
                                                                        style="width: 40px; height: 40px; object-fit: cover;"
                                                                        class="comments__picture"
                                                                        src="uploads/<?= esc($comment['avatar_path']) ?>"
                                                                        width="40"
                                                                        height="40"
                                                                        alt="Аватар пользователя"
                                                                    >
                                                                <?php endif; ?>

                                                            </div>
                                                        </a>
                                                        <div class="comments__info">
                                                            <div class="comments__name-wrapper">
                                                                <a class="comments__user-name" href="/profile.php?id=<?= esc($comment['author_id']) ?>&tab=posts">
                                                                    <span><?= esc($comment['login']) ?></span>
                                                                </a>
                                                                <time
                                                                    class="comments__time"
                                                                    datetime="<?= esc($comment['dt_add']) ?>"
                                                                ><?= get_relative_time($comment['dt_add']) ?> назад</time>
                                                            </div>
                                                            <p class="comments__text"><?= nl2br(esc($comment['content']), false) ?></p>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>

                                            </ul>

                                            <?php if (isset($_GET['comments']) && intval($post['comment_count']) > 2): ?>
                                                <?php $url = "/profile.php?id={$user['id']}&tab=posts&article={$post['id']}"; ?>
                                                <a style="display: inline-block; margin-bottom: 32px;" class="comments__more-link" href="<?= esc($url) ?>">
                                                    <span>Показать все комментарии</span>
                                                    <sup class="comments__amount"><?= esc($post['comment_count']) ?></sup>
                                                </a>
                                            <?php endif; ?>

                                        </div>
                                    </div>

                                    <?php if (isset($inputs['comment'])): ?>
                                        <?php $url = "/profile.php?id={$user['id']}&tab=posts&article={$post['id']}"; ?>
                                        <form class="comments__form form" action="<?= esc($url) ?>" method="post">
                                            <div class="comments__my-avatar">

                                                <?php if (!empty($_SESSION['user']['avatar_path'])): ?>
                                                    <img
                                                        style="width: 40px; height: 40px; object-fit: cover;"
                                                        class="comments__picture"
                                                        src="uploads/<?= esc($_SESSION['user']['avatar_path']) ?>"
                                                        width="40"
                                                        height="40"
                                                        alt="Аватар пользователя"
                                                    >
                                                <?php endif; ?>

                                            </div>
                                            <?php $input = $inputs['comment'] ?>
                                            <?php $classname = isset($errors[$input['name']][0]) ? ' form__input-section--error' : ''; ?>
                                            <div class="form__input-section<?= $classname ?>">
                                                <textarea
                                                    class="comments__textarea form__textarea form__input"
                                                    name="<?= esc($input['name']) ?>"
                                                    placeholder="<?= esc($input['placeholder']) ?>"
                                                ><?= esc(get_post_value($input['name'])) ?></textarea>
                                                <label class="visually-hidden"><?= esc($input['label']) ?></label>
                                                <button class="form__error-button button" type="button">!</button>
                                                <div class="form__error-text">
                                                    <h3 class="form__error-title"><?= esc($input['label']) ?></h3>
                                                    <p class="form__error-desc"><?= esc($errors[$input['name']][0] ?? '') ?></p>
                                                </div>
                                            </div>
                                            <input type="hidden" name="post-id" value="<?= esc($post['id']) ?>">
                                            <button class="comments__submit button button--green" type="submit">Отправить</button>
                                        </form>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <div class="comments">
                                        <?php $style = !$post['hashtags'] ? 'margin-top: 4px;' : ''; ?>
                                        <?php $url = "/profile.php?id={$user['id']}&tab=posts&article={$post['id']}&comments=2"; ?>
                                        <a style="<?= $style ?>" class="comments__button button" href="<?= esc($url) ?>">Показать комментарии</a>
                                    </div>
                                <?php endif; ?>

                            <?php endif; ?>

                        </article>
                    <?php endforeach; ?>

                </section>

                <?php $classname = isset($_GET['tab']) && $_GET['tab'] === 'likes' ? ' tabs__content--active' : ''; ?>
                <section class="profile__likes tabs__content<?= $classname ?>">
                    <h2 class="visually-hidden">Лайки</h2>
                    <ul class="profile__likes-list">

                        <?php foreach ($likes as $like): ?>
                            <li class="post-mini post-mini--<?= esc($like['class_name']) ?> post user">
                                <div class="post-mini__user-info user__info">
                                    <a class="user__avatar-link" href="/profile.php?id=<?= esc($like['user_id']) ?>&tab=posts">
                                        <div class="post-mini__avatar user__avatar">

                                            <?php if (!empty($like['avatar_path'])): ?>
                                                <img
                                                    style="width: 60px; height: 60px; object-fit: cover;"
                                                    class="post-mini__picture user__picture"
                                                    src="uploads/<?= esc($like['avatar_path']) ?>"
                                                    width="60"
                                                    height="60"
                                                    alt="Аватар пользователя"
                                                >
                                            <?php endif; ?>

                                        </div>
                                    </a>
                                    <div class="post-mini__name-wrapper user__name-wrapper">
                                        <a class="post-mini__name user__name" href="/profile.php?id=<?= esc($like['user_id']) ?>&tab=posts">
                                            <span><?= esc($like['author']) ?></span>
                                        </a>
                                        <div class="post-mini__action">
                                            <?php $text_content = $user['id'] === $_SESSION['user']['id'] ? 'Лайкнул вашу публикацию' : 'Поставил лайк этой публикации'; ?>
                                            <span class="post-mini__activity user__additional"><?= $text_content ?></span>
                                            <time
                                                class="post-mini__time user__additional"
                                                datetime="<?= get_datetime_value($like['dt_add']) ?>"
                                            ><?= get_relative_time($like['dt_add']) ?> назад</time>
                                        </div>
                                    </div>
                                </div>
                                <div class="post-mini__preview">
                                    <a class="post-mini__link" href="/post.php?id=<?= esc($like['id']) ?>" title="Перейти на публикацию">

                                        <?php if ($like['class_name'] === 'quote'): ?>
                                            <svg class="post-mini__preview-icon" width="21" height="20">
                                                <use xlink:href="#icon-filter-quote"></use>
                                            </svg>
                                        <?php elseif ($like['class_name'] === 'link'): ?>
                                            <svg class="post-mini__preview-icon" width="21" height="18">
                                                <use xlink:href="#icon-filter-link"></use>
                                            </svg>
                                        <?php elseif ($like['class_name'] === 'photo'): ?>
                                            <div class="post-mini__image-wrapper">
                                                <img style="width: 109px; height: 109px; object-fit: cover;"
                                                    class="post-mini__image" src="uploads/<?= $like['image_path'] ?>" width="109" height="109" alt="Превью публикации">
                                            </div>
                                        <?php elseif ($like['class_name'] === 'video'): ?>
                                            <div style="height: 109px;" class="post-mini__image-wrapper">
                                                <?= embed_youtube_cover($like['video_path'], true); ?>
                                                <span class="post-mini__play-big">
                                                    <svg class="post-mini__play-big-icon" width="12" height="13">
                                                        <use xlink:href="#icon-video-play-big"></use>
                                                    </svg>
                                                </span>
                                            </div>
                                        <?php elseif ($like['class_name'] === 'text'): ?>
                                            <svg class="post-mini__preview-icon" width="20" height="21">
                                                <use xlink:href="#icon-filter-text"></use>
                                            </svg>
                                        <?php endif; ?>

                                        <span class="visually-hidden"><?= esc($like['type_name']) ?></span>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                </section>

                <?php $classname = isset($_GET['tab']) && $_GET['tab'] === 'subscriptions' ? ' tabs__content--active' : ''; ?>
                <section class="profile__subscriptions tabs__content<?= $classname ?>">
                    <h2 class="visually-hidden">Подписки</h2>
                    <ul class="profile__subscriptions-list">

                        <?php foreach ($subscriptions as $user): ?>
                            <li class="post-mini post-mini--photo post user">
                                <div class="post-mini__user-info user__info">
                                    <a class="user__avatar-link" href="/profile.php?id=<?= esc($user['id']) ?>">
                                        <div class="post-mini__avatar user__avatar">

                                            <?php if (!empty($user['avatar_path'])): ?>
                                                <img
                                                    style="width: 60px; height: 60px; object-fit: cover;"
                                                    class="post-mini__picture user__picture"
                                                    src="uploads/<?= esc($user['avatar_path']) ?>"
                                                    width="60"
                                                    height="60"
                                                    alt="Аватар пользователя"
                                                >
                                            <?php endif; ?>

                                        </div>
                                    </a>
                                    <div class="post-mini__name-wrapper user__name-wrapper">
                                        <a class="post-mini__name user__name" href="/profile.php?id=<?= esc($user['id']) ?>">
                                            <span><?= esc($user['login']) ?></span>
                                        </a>
                                        <time
                                            class="post-mini__time user__additional"
                                            datetime="<?= get_datetime_value($user['dt_add']) ?>"
                                        ><?= get_relative_time($user['dt_add']) ?> на сайте</time>
                                    </div>
                                </div>
                                <div class="post-mini__rating user__rating">
                                    <p class="post-mini__rating-item user__rating-item user__rating-item--publications">
                                        <?php $publications = get_noun_plural_form($user['publication_count'], ' публикация', ' публикации', ' публикаций'); ?>
                                        <span class="post-mini__rating-amount user__rating-amount"><?= esc($user['publication_count']) ?></span>
                                        <span class="post-mini__rating-text user__rating-text"><?= $publications ?></span>
                                    </p>
                                    <p class="post-mini__rating-item user__rating-item user__rating-item--subscribers">
                                        <?php $subscribers = get_noun_plural_form($user['subscriber_count'], ' подписчик', ' подписчика', ' подписчиков'); ?>
                                        <span class="post-mini__rating-amount user__rating-amount"><?= esc($user['subscriber_count']) ?></span>
                                        <span class="post-mini__rating-text user__rating-text"><?= $subscribers ?></span>
                                    </p>
                                </div>
                                <?php $style = $user['id'] === $_SESSION['user']['id'] ? 'margin-left: 0;' : ''; ?>
                                <div style="<?= $style ?>" class="post-mini__user-buttons user__buttons">

                                    <?php if ($user['id'] !== $_SESSION['user']['id']): ?>

                                        <?php
                                        $text_content = $user['is_subscription'] ? 'Отписаться' : 'Подписаться';
                                        $classname = $user['is_subscription'] ? 'quartz' : 'main';
                                        ?>

                                        <a class="post-mini__user-button user__button user__button--subscription button button--<?= $classname ?>" href="/subscription.php?id=<?= esc($user['id']) ?>"><?= $text_content ?></a>
                                    <?php endif; ?>

                                </div>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                </section>
            </div>
        </div>
    </div>
</div>
