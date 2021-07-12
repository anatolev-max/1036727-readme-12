<?php

require_once('init.php');

if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input('login');
    $errors = validate_form('login', $input);

    if (!is_null($errors) && empty($errors)) {
        $user = Database::getInstance()->getUserByEmail($input['email']);

        if ($user && password_verify($input['passwd'], $user['password'])) {
            $_SESSION['user'] = $user;
            $url = $_COOKIE['login_ref'] ?? '/feed.php';
            setcookie('login_ref', '', time() - 3600);

            header("Location: $url");
            exit;

        } else {
            $errors['email'][0] = 'Вы ввели неверный email/пароль';
            $errors['passwd'][0] = 'Вы ввели неверный email/пароль';
        }
    }
}

$layout_content = include_template('layouts/anonym.php', [
    'title' => 'readme: блог, каким он должен быть',
    'errors' => $errors
]);

print($layout_content);
