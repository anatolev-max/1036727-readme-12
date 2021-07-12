<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/search.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

if (!$search = trim(filter_input(INPUT_GET, 'q'))) {
    $ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';

    header("Location: $ref");
    exit;
}

$posts = [];

if (substr($search, 0, 1) === '#') {
    if ($hashtag = substr($search, 1)) {
        $posts = Database::getInstance()->getPostsByHashtag($hashtag);
    }

} else {
    $search_words = [];

    foreach (explode(' ', $search) as $search_word) {
        $search_word = ltrim($search_word, '+-<>');
        $search_word = rtrim($search_word, '+-<>*');
        if (mb_strlen($search_word) >= 3) {
            $search_words[] = "{$search_word}*";
        }
    }

    if ($query = implode(' ', $search_words)) {
        $posts = Database::getInstance()->getPostsByQueryString($query);
    }
}

$search_ref = $_COOKIE['search_ref'] ?? null;
$ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';

if (empty($posts) && is_null($search_ref)) {
    setcookie('search_ref', $ref, strtotime('+30 days'));
} elseif (!empty($posts) && !is_null($search_ref)) {
    setcookie('search_ref', '', time() - 3600);
}

$message_count = Database::getInstance()->getMessageCount();

$page_content = include_template('search.php', [
    'posts' => $posts
]);

$layout_content = include_template('layouts/base.php', [
    'title' => 'readme: страница результатов поиска',
    'main_modifier' => 'search-results',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
