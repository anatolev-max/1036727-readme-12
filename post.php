<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/post.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = validate_post($link, $post_id);

$input_fields = 'i.id, i.label, i.type, i.name, i.placeholder, i.required';
$sql = "SELECT $input_fields FROM input i "
     . 'INNER JOIN form_input fi ON fi.input_id = i.id '
     . 'INNER JOIN form f ON f.id = fi.form_id '
     . "WHERE f.name = 'comments'";

$form_inputs = get_mysqli_result($link, $sql);
$input_names = array_column($form_inputs, 'name');
$form_inputs = array_combine($input_names, $form_inputs);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input($link, 'comments');

    if (mb_strlen($input['comment']) === 0) {
        $errors['comment'][0] = 'Это поле должно быть заполнено';
        $errors['comment'][1] = $form_inputs['comment']['label'];
    } elseif (mb_strlen($input['comment']) < 4) {
        $errors['comment'][0] = 'Длина комментария не должна быть меньше четырёх символов';
        $errors['comment'][1] = $form_inputs['comment']['label'];
    }

    if (empty($errors)) {
        $post_id = validate_post($link, intval($input['post-id']));
        $comment = mysqli_real_escape_string($link, $input['comment']);
        $sql = 'INSERT INTO comment (content, author_id, post_id) VALUES '
             . "('$comment', $user_id, $post_id)";
        get_mysqli_result($link, $sql, false);

        $sql = "SELECT author_id FROM post WHERE id = $post_id";
        $author_id = get_mysqli_result($link, $sql, 'assoc')['author_id'];

        header("Location: /profile.php?id={$author_id}&tab=posts");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_COOKIE['like'])) {
    $sql = "UPDATE post SET show_count = show_count + 1 WHERE id = $post_id";
    get_mysqli_result($link, $sql, false);
} elseif (isset($_COOKIE['like'])) {
    setcookie('like', '', time() - 3600);
}

$post_fields = get_post_fields('p.');
$user_fields = 'u.dt_add AS dt_reg, u.login AS author, u.avatar_path';
$sql = "SELECT {$post_fields}, {$user_fields}, ct.class_name FROM post p "
     . 'INNER JOIN user u ON u.id = p.author_id '
     . 'INNER JOIN content_type ct ON ct.id = p.content_type_id '
     . "WHERE p.id = $post_id";
$post = get_mysqli_result($link, $sql, 'assoc');
$post['display_mode'] = 'details';

$sql = 'SELECT h.id, h.name FROM hashtag h '
     . 'INNER JOIN post_hashtag ph ON ph.hashtag_id = h.id '
     . 'INNER JOIN post p ON p.id = ph.post_id '
     . "WHERE p.id = $post_id";
$hashtags = get_mysqli_result($link, $sql);

$comments = filter_input(INPUT_GET, 'comments');
$limit = !$comments || $comments !== 'all' ? ' LIMIT 2' : '';

$comment_fields = 'c.id, c.dt_add, c.content, c.author_id, c.post_id';
$sql = "SELECT {$comment_fields}, u.login, u.avatar_path FROM comment c "
     . 'INNER JOIN user u ON u.id = c.author_id '
     . "WHERE post_id = $post_id "
     . "ORDER BY c.dt_add DESC{$limit}";
$comments = get_mysqli_result($link, $sql);

$page_content = include_template('post.php', [
    'inputs' => $form_inputs,
    'errors' => $errors,
    'link' => $link,
    'post' => $post,
    'hashtags' => $hashtags,
    'comments' => $comments
]);

$layout_content = include_template('layout.php', [
    'link' => $link,
    'title' => 'readme: публикация',
    'page_main_class' => 'publication',
    'page_content' => $page_content
]);

print($layout_content);
