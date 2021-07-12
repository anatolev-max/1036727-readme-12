<?php

require_once('vendor/autoload.php');
require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$user_id = intval($_SESSION['user']['id']);
$profile_id = intval(filter_input(INPUT_GET, 'id'));
$profile_id = Database::getInstance()->validateUser($profile_id);

if ($profile_id === $user_id) {
    http_response_code(500);
    exit;
}

if (!Database::getInstance()->isSubscription([$user_id, $profile_id])) {
    Database::getInstance()->insertSubscription([$user_id, $profile_id]);
    $subscriber = Database::getInstance()->getSubscription($profile_id);

    try {
        $smtp_config = require_once('config/smtp.php');
        $transport = new Swift_SmtpTransport($smtp_config['host'], $smtp_config['port']);
        $transport->setUsername($smtp_config['username']);
        $transport->setPassword($smtp_config['password']);

        $message = new Swift_Message('У вас новый подписчик');
        $message->setTo([$subscriber['email'] => $subscriber['login']]);
        $body = include_template('notifications/subscriber.php', [
            'recipient' => $subscriber
        ]);
        $message->setBody($body);
        $message->setFrom('keks@phpdemo.ru', 'Readme');

        $mailer = new Swift_Mailer($transport);
        $mailer->send($message);

    } catch (Swift_TransportException $ex) {}

} else {
    Database::getInstance()->deleteSubscription([$user_id, $profile_id]);
}

$ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
if (parse_url($ref, PHP_URL_PATH) === '/post.php') {
    setcookie('action', 1, strtotime('+30 days'));
}

header("Location: $ref");
exit;
