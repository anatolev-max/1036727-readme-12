<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/popular.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$sql = 'SELECT id, type_name, class_name, icon_width, icon_height FROM content_type';
$content_types = get_mysqli_result($link, $sql);

$sort_fields = ['popular', 'likes', 'date'];
$sort_types = array_fill_keys($sort_fields, 'desc');

$expires = strtotime('+30 days');
$sort = isset($_GET['sort'], $_COOKIE['sort']) ? $_COOKIE['sort'] : 'popular';
$value = isset($_GET['dir'], $_COOKIE['dir']) ? $_COOKIE['dir'] : 'desc';

setcookie('sort', $sort, $expires);
setcookie('dir', $value, $expires);
$request_uri = preg_replace('%&page=[0-9]+%', '', $_SERVER['REQUEST_URI']);
setcookie('request_uri', $request_uri, $expires);

if (isset($_COOKIE['sort']) && isset($_COOKIE['dir']) && $_COOKIE['request_uri'] !== $request_uri) {

    if (isset($_GET['sort']) && in_array($_GET['sort'], $sort_fields) && $_COOKIE['sort'] === $_GET['sort']) {
        $sort = $_GET['sort'];

        $value = $_COOKIE['dir'] === 'desc' ? 'asc' : 'desc';
        $sort_types[$sort] = $value;
        setcookie('dir', $value, $expires);

    } elseif (isset($_GET['sort']) && in_array($_GET['sort'], $sort_fields)) {
        list($sort, $value) = [$_GET['sort'], 'asc'];

        setcookie('sort', $sort, $expires);
        setcookie('dir', 'asc', $expires);
    }
}

$sort_types[$sort] = $value;

$content_type_filter = '';
if ($content_type = filter_input(INPUT_GET, 'filter')) {
    $content_type = mysqli_real_escape_string($link, $content_type);

    if (is_content_type_valid($link, $content_type)) {
        $content_type_filter = "WHERE ct.class_name = '$content_type' ";
    }
}

$sort_filter = 'p.show_count DESC';
if (isset($_GET['sort']) && isset($_GET['dir'])) {

    switch ($_GET['sort']) {
        case 'popular':
            $sort_filter = 'p.show_count ';
            break;
        case 'likes':
            $sort_filter = 'COUNT(pl.id) ';
            break;
        case 'date':
            $sort_filter = 'p.dt_add ';
            break;
        default:
            $sort_filter = 'p.show_count ';
            break;
    }

    $sort_filter .= $_GET['dir'] === 'asc' ? 'ASC' : 'DESC';
}

$current_page = intval(filter_input(INPUT_GET, 'page') ?? 1);
$page_items = 6;

$sql = 'SELECT COUNT(p.id) FROM post p '
     . 'LEFT JOIN content_type ct ON ct.id = p.content_type_id '

     . $content_type_filter;
$items_count = get_mysqli_result($link, $sql, 'assoc')['COUNT(p.id)'];
$pages_count = ceil($items_count / $page_items) ?: 1;

if ($current_page <= 0) {
    $current_page = 1;
} elseif ($current_page > $pages_count) {
    $current_page = $pages_count;
}

$offset = ($current_page - 1) * $page_items;

$post_fields = get_post_fields('p.');
$user_fields = 'u.login AS author, u.avatar_path';
$sql = "SELECT COUNT(pl.id), {$post_fields}, {$user_fields}, ct.class_name FROM post p "
     . 'LEFT JOIN user u ON u.id = p.author_id '
     . 'LEFT JOIN content_type ct ON ct.id = p.content_type_id '
     . 'LEFT JOIN post_like pl ON pl.post_id = p.id '

     . $content_type_filter

     . 'GROUP BY p.id '
     . "ORDER BY $sort_filter LIMIT $page_items OFFSET $offset";
$posts = get_mysqli_result($link, $sql);

$page_content = include_template('popular.php', [
    'sort_fields' => $sort_fields,
    'sort_types' => $sort_types,
    'content_types' => $content_types,
    'posts' => $posts,
    'link' => $link,
    'current_page' => $current_page,
    'pages_count' => $pages_count
]);

$layout_content = include_template('layout.php', [
    'link' => $link,
    'title' => 'readme: популярное',
    'page_main_class' => 'popular',
    'page_content' => $page_content
]);

print($layout_content);
