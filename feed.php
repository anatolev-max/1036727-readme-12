<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$sql = 'SELECT id, type_name, class_name, icon_width, icon_height FROM content_type';
$content_types = get_mysqli_result($link, $sql);

$content_type_filter = '';
if ($content_type = filter_input(INPUT_GET, 'filter')) {
    $content_type = mysqli_real_escape_string($link, $content_type);

    if (is_content_type_valid($link, $content_type)) {
        $content_type_filter = " AND ct.class_name = '$content_type' ";
    }
}

$post_fields = get_post_fields('p.');
$user_fields = 'u.login AS author, u.avatar_path';
$sql = "SELECT {$post_fields}, {$user_fields}, ct.class_name FROM post p "
     . 'INNER JOIN subscription s ON s.user_id = p.author_id '
     . 'INNER JOIN user u ON u.id = p.author_id '
     . 'INNER JOIN content_type ct ON ct.id = p.content_type_id '
     . "WHERE s.author_id = {$user_id}{$content_type_filter} "
     . 'ORDER BY p.dt_add ASC';
$posts = get_mysqli_result($link, $sql);

$page_content = include_template('main.php', [
    'content_types' => $content_types,
    'posts' => $posts,
    'link' => $link
]);

$layout_content = include_template('layout.php', [
    'link' => $link,
    'title' => 'readme: моя лента',
    'page_main_class' => 'feed',
    'page_content' => $page_content
]);

print($layout_content);
