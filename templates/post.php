<div class="container">
    <h1 class="page__title page__title--publication"><?= esc($post['title']) ?></h1>
    <section class="post-details">
        <h2 class="visually-hidden">Публикация</h2>
        <div class="post-details__wrapper post-<?= esc($post['class_name']) ?>">
            <?php $style = in_array($post['class_name'], ['quote', 'photo', 'video']) ? ' border-top: none;' : ''; ?>
            <div style="border-top-right-radius: 0;<?= $style ?>" class="post-details__main-block post post--details">
                <?php $style = $post['class_name'] === 'text' ? 'border-bottom: 1px solid #dee5fc;' : ''; ?>
                <div style="<?= $style ?>" class="post__main">
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
                        <a class="post__indicator post__indicator--comments button" href="#form" title="Комментарии">
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
                    <span class="post__view"><?= $post['show_count'] . get_noun_plural_form($post['show_count'], ' просмотр', ' просмотра', ' просмотров') ?></span>
                </div>

                <?php if (!empty($post['hashtags'])): ?>
                    <ul class="post__tags">

                        <?php foreach ($post['hashtags'] as $hashtag): ?>
                            <li><a href="/search.php?q=%23<?= esc($hashtag['name']) ?>">#<?= esc($hashtag['name']) ?></a></li>
                        <?php endforeach; ?>

                    </ul>
                <?php endif; ?>

                <div class="comments">

                    <?php if (isset($inputs['comment'])): ?>
                        <?php $style = empty($post['comments']) ? 'border-bottom-left-radius: 30px; border-bottom-right-radius: 30px;' : ''; ?>
                        <form style="<?= $style ?>" class="comments__form form" id="form" action="/post.php?id=<?= esc($post['id']) ?>" method="post">
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

                    <?php if (!empty($post['comments'])): ?>
                        <div class="comments__list-wrapper">
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
                                                    datetime="<?= get_datetime_value($comment['dt_add']) ?>"
                                                ><?= get_relative_time($comment['dt_add']) ?> назад</time>
                                            </div>
                                            <p class="comments__text"><?= nl2br(esc($comment['content']), false) ?></p>
                                        </div>
                                    </li>
                                <?php endforeach; ?>

                            </ul>

                            <?php if ((isset($_GET['comments'])) && intval($post['comment_count']) > 2): ?>
                                <a class="comments__more-link" href="/post.php?id=<?= esc($post['id']) ?>">
                                    <span>Показать все комментарии</span>
                                    <sup class="comments__amount"><?= esc($post['comment_count']) ?></sup>
                                </a>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>

                </div>
            </div>
            <div class="post-details__user user">
                <div class="post-details__user-info user__info">
                    <a class="post-details__avatar-link user__avatar-link" href="/profile.php?id=<?= esc($post['author_id']) ?>&tab=posts">
                        <div class="post-details__avatar user__avatar">

                            <?php if (!empty($post['author']['avatar_path'])): ?>
                                <img
                                    style="width: 60px; height: 60px; object-fit: cover;"
                                    class="post-details__picture user__picture"
                                    src="uploads/<?= esc($post['author']['avatar_path']) ?>"
                                    width="60"
                                    height="60"
                                    alt="Аватар пользователя"
                                >
                            <?php endif; ?>

                        </div>
                    </a>
                    <div class="post-details__name-wrapper user__name-wrapper">
                        <a class="post-details__name user__name" href="/profile.php?id=<?= esc($post['author_id']) ?>&tab=posts">
                            <span><?= esc($post['author']['login']) ?></span>
                        </a>
                        <time
                            class="post-details__time user__time"
                            datetime="<?= get_datetime_value($post['author']['dt_add']) ?>"
                        ><?= get_relative_time($post['author']['dt_add']) ?> на сайте</time>
                    </div>
                </div>
                <?php $style = $post['author_id'] === $_SESSION['user']['id'] ? 'margin-bottom: 0;' : ''; ?>
                <div style="<?= $style ?>" class="post-details__rating user__rating">
                    <p class="post-details__rating-item user__rating-item user__rating-item--subscribers">
                        <?php $subscribers = get_noun_plural_form($post['author']['subscriber_count'], ' подписчик', ' подписчика', ' подписчиков'); ?>
                        <span class="post-details__rating-amount user__rating-amount"><?= esc($post['author']['subscriber_count']) ?></span>
                        <span class="post-details__rating-text user__rating-text"><?= $subscribers ?></span>
                    </p>
                    <p class="post-details__rating-item user__rating-item user__rating-item--publications">
                        <?php $publications = get_noun_plural_form($post['author']['publication_count'], ' публикация', ' публикации', ' публикаций'); ?>
                        <span class="post-details__rating-amount user__rating-amount"><?= esc($post['author']['publication_count']) ?></span>
                        <span class="post-details__rating-text user__rating-text"><?= $publications ?></span>
                    </p>
                </div>

                <?php if ($post['author_id'] !== $_SESSION['user']['id']): ?>
                    <div class="post-details__user-buttons user__buttons">
                        <?php $text_content = $post['author']['is_subscription'] ? 'Отписаться' : 'Подписаться'; ?>
                        <a class="user__button user__button--subscription button button--main" href="/subscription.php?id=<?= esc($post['author_id']) ?>"><?= $text_content ?></a>

                        <?php if ($post['author']['is_subscription']): ?>
                            <a class="user__button user__button--writing button button--green" href="/messages.php?contact=<?= esc($post['author_id']) ?>">Сообщение</a>
                        <?php endif; ?>

                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
</div>
