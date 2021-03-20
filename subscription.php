<?php

require_once('vendor/autoload.php');
require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$profile_id = intval(filter_input(INPUT_GET, 'id'));
$profile_id = validate_user($link, $profile_id);

if ($profile_id === $user_id) {
    http_response_code(500);
    exit;
}

$sql = "SELECT id FROM subscription WHERE author_id = $user_id AND user_id = $profile_id";
$result = get_mysqli_result($link, $sql, false);

if (!mysqli_num_rows($result)) {
    $sql = "INSERT INTO subscription (author_id, user_id) VALUES ($user_id, $profile_id)";

    if (get_mysqli_result($link, $sql, false)) {
        $user_fields = 'id, dt_add, email, login, password, avatar_path';
        $sql = "SELECT $user_fields FROM user WHERE id = $profile_id";
        $profile = get_mysqli_result($link, $sql, 'assoc');

        $transport = new Swift_SmtpTransport('phpdemo.ru', 25);
        $transport->setUsername('keks@phpdemo.ru');
        $transport->setPassword('htmlacademy');

        $message = new Swift_Message('У вас новый подписчик');
        $message->setTo([$profile['email'] => $profile['login']]);

        $body = "Здравствуйте, {$profile['login']}. "
              . "На вас подписался новый пользователь {$_SESSION['user']['login']}. "
              . "Вот ссылка на его профиль: http://readme.net/profile.php?id={$profile_id}";
        $message->setBody($body);
        $message->setFrom('keks@phpdemo.ru', 'Readme');

        $mailer = new Swift_Mailer($transport);
        $mailer->send($message);
    }

} else {
    $sql = "DELETE FROM subscription WHERE author_id = $user_id AND user_id = $profile_id";
    get_mysqli_result($link, $sql, false);
}

$ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';

header("Location: $ref");
exit;
