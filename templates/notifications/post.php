<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

    <h1>Уведомление о публикации нового поста</h1>

    <?php $link = "http://readme.net/profile.php?id={$_SESSION['user']['id']}"; ?>
    <p>
        Здравствуйте, <?= $recipient['login'] ?>.
        Пользователь <?= $_SESSION['user']['login'] ?> только что опубликовал новую запись «<?= $post_title ?>».
        Посмотрите её на странице пользователя: <a href="<?= $link ?>"><?= $link ?></a>
    </p>

</body>
</html>
