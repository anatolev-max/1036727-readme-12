<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/post.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$user_id = $_SESSION['user']['id'];
$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = Database::getInstance()->validatePost($post_id);

$form_inputs = Database::getInstance()->getFormInputs('comments');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input('comments');
    $errors = validate_form('comments', $input);

    if (!is_null($errors) && empty($errors)) {
        $comment = cut_out_extra_spaces($input['comment']);
        $stmt_data = [$comment, $user_id, $input['post-id']];
        Database::getInstance()->insertComment($stmt_data);
        $author_id = Database::getInstance()->getPostAuthorId($input['post-id']);

        header("Location: /profile.php?id={$author_id}&tab=posts");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_COOKIE['action'])) {
    Database::getInstance()->updatePostShowCount($post_id);
} elseif (isset($_COOKIE['action'])) {
    setcookie('action', '', time() - 3600);
}

$limit = intval(filter_input(INPUT_GET, 'comments'));

$post = Database::getInstance()->getPostDetails($post_id);
$post['author'] = Database::getInstance()->getPostAuthor($post_id);
$post['hashtags'] = Database::getInstance()->getPostHashtags($post_id);
$post['comments'] = Database::getInstance()->getPostComments($post_id, $limit);

$message_count = Database::getInstance()->getMessageCount();

$page_content = include_template('post.php', [
    'post' => $post,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = include_template('layouts/base.php', [
    'title' => 'readme: публикация',
    'main_modifier' => 'publication',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
