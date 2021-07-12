<header class="header">
    <div class="header__wrapper container">
        <div class="header__logo-wrapper">
            <a class="header__logo-link" href="/index.php">
                <img class="header__logo" src="img/logo.svg" alt="Логотип readme" width="128" height="24">
            </a>
            <p class="header__topic">micro blogging</p>
        </div>
        <?php if (isset($_SESSION['user'])): ?>
            <form class="header__search-form form" action="/search.php" method="get">
                <div class="header__search">
                    <label class="visually-hidden">Поиск</label>
                    <input class="header__search-input form__input" type="search" name="q" value="<?= esc($_GET['q'] ?? '') ?>">
                    <button class="header__search-button button" type="submit">
                        <svg class="header__search-icon" width="18" height="18">
                            <use xlink:href="#icon-search"></use>
                        </svg>
                        <span class="visually-hidden">Начать поиск</span>
                    </button>
                </div>
            </form>
        <?php endif; ?>
        <div class="header__nav-wrapper">
            <nav class="header__nav">
                <?php if (isset($_SESSION['user'])): ?>
                    <ul class="header__my-nav">
                        <li class="header__my-page header__my-page--popular">
                            <?php $classname = $_SERVER['PHP_SELF'] === '/popular.php' ? ' header__page-link--active' : ''; ?>
                            <a class="header__page-link<?= $classname ?>" href="/popular.php" title="Популярный контент">
                                <span class="visually-hidden">Популярный контент</span>
                            </a>
                        </li>
                        <li class="header__my-page header__my-page--feed">
                            <?php $classname = $_SERVER['PHP_SELF'] === '/feed.php' ? ' header__page-link--active' : ''; ?>
                            <a class="header__page-link<?= $classname ?>" href="/feed.php" title="Моя лента">
                                <span class="visually-hidden">Моя лента</span>
                            </a>
                        </li>
                        <li class="header__my-page header__my-page--messages">
                            <?php $classname = $_SERVER['PHP_SELF'] === '/messages.php' ? ' header__page-link--active' : ''; ?>
                            <a class="header__page-link<?= $classname ?>" href="/messages.php" title="Личные сообщения">
                                <span class="visually-hidden">Личные сообщения</span>
                            </a>
                        </li>
                    </ul>
                    <ul class="header__user-nav">
                        <li class="header__profile">
                            <?php $profile_url = "/profile.php?id={$_SESSION['user']['id']}&tab=posts"; ?>
                            <a class="header__profile-link" href="<?= esc($profile_url) ?>">
                                <div class="header__avatar-wrapper">
                                    <?php if (!empty($_SESSION['user']['avatar_path'])): ?>
                                        <img style="width: 40px; height: 40px; object-fit: cover;"
                                            class="header__profile-avatar" src="uploads/<?= esc($_SESSION['user']['avatar_path']) ?>" alt="Аватар профиля">
                                    <?php endif; ?>
                                </div>
                                <div class="header__profile-name">
                                    <span><?= esc($_SESSION['user']['login']) ?></span>
                                    <svg class="header__link-arrow" width="10" height="6">
                                        <use xlink:href="#icon-arrow-right-ad"></use>
                                    </svg>
                                </div>
                            </a>
                            <div class="header__tooltip-wrapper">
                                <div class="header__profile-tooltip">
                                    <ul class="header__profile-nav">
                                        <li class="header__profile-nav-item">
                                            <a class="header__profile-nav-link" href="<?= esc($profile_url) ?>">
                                                <span class="header__profile-nav-text">Мой профиль</span>
                                            </a>
                                        </li>
                                        <li class="header__profile-nav-item">
                                            <a class="header__profile-nav-link" href="/messages.php">
                                                <span class="header__profile-nav-text">Сообщения
                                                    <?php if ($message_count): ?>
                                                        <i class="header__profile-indicator"><?= $message_count ?></i>
                                                    <?php endif; ?>
                                                </span>
                                            </a>
                                        </li>
                                        <li class="header__profile-nav-item">
                                            <a class="header__profile-nav-link" href="/logout.php">
                                                <span class="header__profile-nav-text">Выход</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a class="header__post-button button button--transparent" href="/add.php?tab=text">Пост</a>
                        </li>
                    </ul>
                <?php else: ?>
                    <ul class="header__user-nav" style="margin-left: auto;">
                        <li class="header__authorization">
                            <?php $classname = $_SERVER['PHP_SELF'] === '/login.php' ? ' header__user-button--active' : ''; ?>
                            <?php $url = $_SERVER['PHP_SELF'] !== '/login.php' ? '/login.php' : '#'; ?>
                            <a class="header__user-button<?= $classname ?> header__authorization-button button" href="<?= $url ?>">Вход</a>
                        </li>
                        <li>
                            <?php $classname = $_SERVER['PHP_SELF'] === '/register.php' ? ' header__user-button--active' : ''; ?>
                            <?php $url = $_SERVER['PHP_SELF'] !== '/register.php' ? '/register.php' : '#'; ?>
                            <a class="header__user-button<?= $classname ?> header__register-button button" href="<?= $url ?>">Регистрация</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>
