<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/profile.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$profile_id = intval(filter_input(INPUT_GET, 'id'));
$profile_id = validate_user($link, $profile_id);

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
             . "('$comment', {$_SESSION['user']['id']}, $post_id)";
        get_mysqli_result($link, $sql, false);
        $ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
        $ref = preg_replace('%&show=all%', '', $ref);

        header("Location: $ref");
        exit;
    }
}

$user_fields = 'id, dt_add, email, login, password, avatar_path';
$sql = "SELECT $user_fields FROM user WHERE id = $profile_id";
$user = get_mysqli_result($link, $sql, 'assoc');

$post_fields = get_post_fields('p.');
$sql = "SELECT {$post_fields}, COUNT(c.id), ct.class_name FROM post p "
     . 'LEFT JOIN content_type ct ON ct.id = p.content_type_id '
     . 'LEFT JOIN comment c ON c.post_id = p.id '
     . "WHERE p.author_id = $profile_id "
     . 'GROUP BY p.id '
     . 'ORDER BY p.dt_add ASC';
$posts = get_mysqli_result($link, $sql);

$user_fields2 = 'u.id AS user_id, u.login AS author, u.avatar_path';
$sql = "SELECT {$post_fields}, {$user_fields2}, "
     . 'ct.type_name, ct.class_name, pl.dt_add FROM post p '
     . 'LEFT JOIN content_type ct ON ct.id = p.content_type_id '
     . 'LEFT JOIN post_like pl ON pl.post_id = p.id '
     . 'LEFT JOIN user u ON u.id = pl.author_id '
     . "WHERE p.author_id = $profile_id "
     . 'GROUP BY p.id, pl.id, u.id '
     . 'HAVING COUNT(pl.id) > 0 '
     . 'ORDER BY pl.dt_add DESC';
$likes = get_mysqli_result($link, $sql);

$user_fields = explode(', ', $user_fields);
array_walk($user_fields, 'add_prefix', 'u.');
$user_fields = implode(', ', $user_fields);

$sql = "SELECT {$user_fields} FROM subscription s "
     . 'INNER JOIN user u ON u.id = s.user_id '
     . "WHERE s.author_id = $profile_id";
$subscriptions = get_mysqli_result($link, $sql);

$page_content = include_template('profile.php', [
    'subscriptions' => $subscriptions,
    'posts' => $posts,
    'likes' => $likes,
    'link' => $link,
    'user' => $user,
    'inputs' => $form_inputs,
    'errors' => $errors
]);

$layout_content = include_template('layout.php', [
    'link' => $link,
    'title' => 'readme: профиль',
    'page_main_class' => 'profile',
    'page_content' => $page_content
]);

print($layout_content);
