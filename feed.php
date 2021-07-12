<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

$content_type = filter_input(INPUT_GET, 'filter') ?? '';

$content_types = Database::getInstance()->getContentTypes();
$posts = Database::getInstance()->getFeedPosts($content_type);
$message_count = Database::getInstance()->getMessageCount();

$page_content = include_template('main.php', [
    'content_types' => $content_types,
    'posts' => $posts
]);

$layout_content = include_template('layouts/base.php', [
    'title' => 'readme: моя лента',
    'main_modifier' => 'feed',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
