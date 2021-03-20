<h1 class="visually-hidden">Личные сообщения</h1>
<section style="justify-content: flex-start;" class="messages tabs">
    <h2 class="visually-hidden">Сообщения</h2>
    <div class="messages__contacts">
        <ul class="messages__contacts-list tabs__list">
            <?php foreach ($contacts as $contact): ?>
            <li class="messages__contacts-item">
                <?php $classname = isset($_GET['contact']) && $_GET['contact'] === $contact['id'] ? ' messages__contacts-tab--active tabs__item--active' : ''; ?>
                <a class="messages__contacts-tab tabs__item<?= $classname ?>" href="/messages.php?contact=<?= esc($contact['id']) ?>">
                    <div class="messages__avatar-wrapper">
                        <?php if (!empty($contact['avatar_path'])): ?>
                        <?php $style = 'width: 60px; height: 60px; object-fit: cover;'; ?>
                        <img style="<?= $style ?>" class="messages__avatar" src="uploads/<?= esc($contact['avatar_path']) ?>" width="60" height="60" alt="Аватар пользователя">
                        <?php endif; ?>
                        <?php if ($messages_count = get_messages_count($link, $contact['id'])): ?>
                        <i class="messages__indicator"><?= $messages_count ?></i>
                        <?php endif; ?>
                    </div>
                    <div class="messages__info">
                        <span class="messages__contact-name"><?= esc($contact['login']) ?></span>
                        <div class="messages__preview">
                            <?php if (!is_new_contact($link, $contact['id'])): ?>
                            <p class="messages__preview-text"><?= get_message_preview($link, $contact['id']) ?></p>
                            <time class="messages__preview-time" datetime="<?= get_datetime_value($contact['MAX(m.dt_add)']) ?>"><?= date('M j', strtotime($contact['MAX(m.dt_add)'])) ?></time>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php $style = get_messages_chat_style($contacts); ?>
    <div style="<?= $style ?>" class="messages__chat">
        <?php if (isset($_GET['contact']) && in_array($_GET['contact'], array_column($contacts, 'id'))): ?>
        <div class="messages__chat-wrapper">
            <?php foreach ($contacts as $contact): ?>
            <?php $messages = get_contact_messages($link, $contact['id']); ?>
            <?php $classname = $_GET['contact'] === $contact['id'] ? ' tabs__content--active' : ''; ?>
            <ul class="messages__list tabs__content<?= $classname ?>">
                <?php foreach ($messages as $message): ?>
                <?php $classname = $message['sender_id'] === $_SESSION['user']['id'] ? ' messages__item--my' : ''; ?>
                <li class="messages__item<?= $classname ?>">
                    <div class="messages__info-wrapper">
                        <a class="messages__author-link" href="/profile.php?id=<?= esc($message['sender_id']) ?>&tab=posts">
                            <div class="messages__item-avatar">
                                <?php if (!empty($message['avatar_path'])): ?>
                                <?php $style = 'width: 40px; height: 40px; object-fit: cover;'; ?>
                                <img style="<?= $style ?>" class="messages__avatar" src="uploads/<?= esc($message['avatar_path']) ?>" width="40" height="40" alt="Аватар пользователя">
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="messages__item-info">
                            <a class="messages__author" href="/profile.php?id=<?= esc($message['sender_id']) ?>&tab=posts"><?= esc($message['author']) ?></a>
                            <time class="messages__time" datetime="<?= get_datetime_value($message['dt_add']) ?>"><?= get_relative_time($message['dt_add']) ?> назад</time>
                        </div>
                    </div>
                    <p class="messages__text"><?= esc($message['content']) ?></p>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endforeach; ?>
        </div>
        <?php if (is_contact_valid($link)): ?>
        <div style="margin-top: auto;" class="comments">
            <?php if (isset($inputs['message'])): ?>
            <form class="comments__form form" action="/messages.php?contact=<?= esc($_GET['contact']) ?>" method="post">
                <div class="comments__my-avatar">
                    <?php if (!empty($_SESSION['user']['avatar_path'])): ?>
                    <?php $style = 'width: 40px; height: 40px; object-fit: cover;'; ?>
                    <img style="<?= $style ?>" class="comments__picture" src="uploads/<?= esc($_SESSION['user']['avatar_path']) ?>" width="40" height="40" alt="Аватар пользователя">
                    <?php endif; ?>
                </div>
                <?php $input = $inputs['message'] ?>
                <?php $classname = isset($errors[$input['name']][0]) ? ' form__input-section--error' : ''; ?>
                <div class="form__input-section<?= $classname ?>">
                    <textarea class="comments__textarea form__textarea form__input" name="<?= esc($input['name']) ?>" placeholder="<?= esc($input['placeholder']) ?>"><?= esc(get_post_value($input['name'])) ?></textarea>
                    <label class="visually-hidden"><?= esc($input['label']) ?></label>
                    <button class="form__error-button button" type="button">!</button>
                    <div class="form__error-text">
                        <h3 class="form__error-title"><?= esc($input['label']) ?></h3>
                        <p class="form__error-desc"><?= esc($errors[$input['name']][0] ?? '') ?></p>
                    </div>
                </div>
                <input type="hidden" name="contact-id" value="<?= esc($_GET['contact'] ?? '') ?>">
                <button class="comments__submit button button--green" type="submit">Отправить</button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
