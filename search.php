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
        $hashtag = mysqli_real_escape_string($link, $hashtag);
        $search_sql = get_search_sql($hashtag, true);

        $posts = get_mysqli_result($link, $search_sql);
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

    if ($request = implode(' ', $search_words)) {
        $request = mysqli_real_escape_string($link, $request);
        $search_sql = get_search_sql($request, false);

        $posts = get_mysqli_result($link, $search_sql);
    }
}

$search_ref = $_COOKIE['search_ref'] ?? null;
$ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';

if (empty($posts) && is_null($search_ref)) {
    setcookie('search_ref', $ref, strtotime('+30 days'));
} elseif (!empty($posts) && !is_null($search_ref)) {
    setcookie('search_ref', $search_ref, time() - 3600);
}

$page_content = include_template('search.php', [
    'posts' => $posts,
    'link' => $link
]);

$layout_content = include_template('layout.php', [
    'link' => $link,
    'title' => 'readme: страница результатов поиска',
    'page_main_class' => 'search-results',
    'page_content' => $page_content
]);

print($layout_content);
