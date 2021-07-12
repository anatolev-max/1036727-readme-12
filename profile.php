<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/profile.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$user_id = $_SESSION['user']['id'];
$profile_id = intval(filter_input(INPUT_GET, 'id'));
$profile_id = Database::getInstance()->validateUser($profile_id);

$form_inputs = Database::getInstance()->getFormInputs('comments');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input('comments');
    $errors = validate_form('comments', $input);

    if (!is_null($errors) && empty($errors)) {
        $comment = cut_out_extra_spaces($input['comment']);
        $stmt_data = [$comment, $user_id, $input['post-id']];
        Database::getInstance()->insertComment($stmt_data);
        $ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';

        header("Location: $ref");
        exit;
    }
}

$limit = intval(filter_input(INPUT_GET, 'comments'));

$user = Database::getInstance()->getUserProfile($profile_id);
$posts = Database::getInstance()->getProfilePosts($profile_id, $limit);
$likes = Database::getInstance()->getProfileLikes($profile_id);
$subscriptions = Database::getInstance()->getProfileSubscriptions($profile_id);

$message_count = Database::getInstance()->getMessageCount();

$page_content = include_template('profile.php', [
    'user' => $user,
    'posts' => $posts,
    'likes' => $likes,
    'subscriptions' => $subscriptions,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = include_template('layouts/base.php', [
    'title' => 'readme: профиль',
    'main_modifier' => 'profile',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
