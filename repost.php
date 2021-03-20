<?php

require_once('vendor/autoload.php');
require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = validate_post($link, $post_id);

$sql = 'SELECT title, text_content, quote_author, image_path, video_path, link, content_type_id '
     . "FROM post WHERE id = $post_id";
$post = get_mysqli_result($link, $sql, 'assoc');

$sql = 'INSERT INTO post (title, text_content, quote_author, image_path, '
     . 'video_path, link, content_type_id, author_id, is_repost, origin_post_id) VALUES '
     . '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
$stmt_data = $post + [$user_id, 1, $post_id];
$stmt = db_get_prepare_stmt($link, $sql, $stmt_data);

if (mysqli_stmt_execute($stmt)) {
    $post['id'] = mysqli_insert_id($link);

    $sql = "SELECT hashtag_id AS id FROM post_hashtag WHERE post_id = $post_id";
    if ($hashtags = get_mysqli_result($link, $sql)) {

        foreach ($hashtags as $hashtag) {
            $sql = 'INSERT INTO post_hashtag (hashtag_id, post_id) VALUES '
                 . "({$hashtag['id']}, {$post['id']})";
            get_mysqli_result($link, $sql, false);
        }
    }

    $user_fields = 'u.id, u.dt_add, u.email, u.login, u.password, u.avatar_path';
    $sql = "SELECT $user_fields FROM user u "
         . 'INNER JOIN subscription s ON s.author_id = u.id '
         . "WHERE s.user_id = $user_id";

    if ($users = get_mysqli_result($link, $sql)) {

        $transport = new Swift_SmtpTransport('phpdemo.ru', 25);
        $transport->setUsername('keks@phpdemo.ru');
        $transport->setPassword('htmlacademy');

        $message = new Swift_Message();
        $message->setSubject("Новая публикация от пользователя {$_SESSION['user']['login']}");

        $mailer = new Swift_Mailer($transport);

        foreach ($users as $user) {
            $message->setTo([$user['email'] => $user['login']]);

            $body = "Здравствуйте, {$user['login']}. "
                  . "Пользователь {$_SESSION['user']['login']} только что опубликовал новую запись «{$post['title']}». "
                  . "Посмотрите её на странице пользователя: http://readme.net/profile.php?id={$user_id}";
            $message->setBody($body);
            $message->setFrom('keks@phpdemo.ru', 'Readme');

            $mailer->send($message);
        }
    }

    header("Location: /profile.php?id={$user_id}&tab=posts");
    exit;
}

http_response_code(500);
exit;
