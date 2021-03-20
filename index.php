<?php

require_once('init.php');

if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input($link, 'login');

    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'E-mail введён некорректно';
    }

    $required_fields = get_required_fields($link, 'login');
    foreach ($required_fields as $field) {
        if (mb_strlen($input[$field]) === 0) {
            $errors[$field] = 'Это поле должно быть заполнено';
        }
    }

    if (empty($errors)) {
        $email = mysqli_real_escape_string($link, $input['email']);

        $user_fields = 'id, dt_add, email, login, password, avatar_path';
        $sql = "SELECT $user_fields FROM user WHERE email = '$email';";
        $user = get_mysqli_result($link, $sql, 'assoc');

        if ($user && password_verify($input['password'], $user['password'])) {
            $_SESSION['user'] = $user;
            $url = $_COOKIE['login_ref'] ?? '/feed.php';
            setcookie('login_ref', '', time() - 3600);

            header("Location: $url");
            exit;

        } else {
            $errors['email'] = 'Вы ввели неверный email/пароль';
            $errors['password'] = 'Вы ввели неверный email/пароль';
        }
    }
}

$layout_content = include_template('anonym.php', [
    'title' => 'readme: блог, каким он должен быть',
    'errors' => $errors
]);

print($layout_content);
