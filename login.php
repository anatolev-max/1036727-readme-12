<?php

require_once('init.php');

if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

$input_fields = 'i.id, i.label, i.type, i.name, i.placeholder, i.required';
$sql = "SELECT {$input_fields}, f.name AS form FROM input i "
     . 'INNER JOIN form_input fi ON fi.input_id = i.id '
     . 'INNER JOIN form f ON f.id = fi.form_id '
     . "WHERE f.name = 'login'";

$form_inputs = get_mysqli_result($link, $sql);
$input_names = array_column($form_inputs, 'name');
$form_inputs = array_combine($input_names, $form_inputs);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input($link, 'login');

    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'][0] = 'E-mail введён некорректно';
        $errors['email'][1] = 'Электронная почта';
    }

    $required_fields = get_required_fields($link, 'login');
    foreach ($required_fields as $field) {
        if (mb_strlen($input[$field]) === 0) {
            $errors[$field][0] = 'Это поле должно быть заполнено';
            $errors[$field][1] = $form_inputs[$field]['label'];
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
            $errors['email'] = ['Вы ввели неверный email/пароль', 'Электронная почта'];
            $errors['password'] = ['Вы ввели неверный email/пароль', 'Пароль'];
        }
    }
}

$page_content = include_template('login.php', [
    'inputs' => $form_inputs,
    'errors' => $errors
]);

$layout_content = include_template('layout.php', [
    'title' => 'readme: авторизация',
    'page_main_class' => 'login',
    'page_content' => $page_content
]);

print($layout_content);
