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
     . "WHERE f.name = 'registration'";

$form_inputs = get_mysqli_result($link, $sql);
$input_names = array_column($form_inputs, 'name');
$form_inputs = array_combine($input_names, $form_inputs);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input($link, 'registration');
    $mime_types = ['image/jpeg', 'image/png', 'image/gif'];

    $required_fields = get_required_fields($link, 'registration');
    foreach ($required_fields as $field) {
        if (mb_strlen($input[$field]) === 0) {
            $errors[$field][0] = 'Это поле должно быть заполнено';
            $errors[$field][1] = $form_inputs[$field]['label'];
        }
    }

    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'][0] = 'E-mail введён некорректно';
        $errors['email'][1] = 'Электронная почта';
    } else {
        $email = mysqli_real_escape_string($link, $input['email']);
        $sql = "SELECT id FROM user WHERE email = '$email';";
        $result = mysqli_query($link, $sql);
        if (mysqli_num_rows($result) > 0) {
            $errors['email'][0] = 'Пользователь с этим email уже зарегистрирован';
            $errors['email'][1] = 'Электронная почта';
        }
    }

    if (mb_strlen($input['password']) > 0 && mb_strlen($input['password-repeat']) > 0) {
        if ($input['password'] !== $input['password-repeat']) {
            $errors['password-repeat'][0] = 'Пароли не совпадают';
            $errors['password-repeat'][1] = 'Повтор пароля';
        } else {
            $password = password_hash($input['password'], PASSWORD_DEFAULT);
        }
    }

    if (!empty($_FILES['avatar']['name'])) {
        $file_path = $_FILES['avatar']['tmp_name'];
        $file_size = $_FILES['avatar']['size'];
        $file_type = mime_content_type($file_path);

        if (!in_array($file_type, $mime_types)) {
            $errors['avatar'][0] = 'Неверный MIME-тип файла';
            $errors['avatar'][1] = 'Изображение';
        } elseif ($file_size > 1000000) {
            $errors['avatar'][0] = 'Максимальный размер файла: 1Мб';
            $errors['avatar'][1] = 'Изображение';
        } else {
            $file_name = uniqid();
            $file_extension = explode('/', $file_type);
            $file_name .= ".{$file_extension[1]}";
            $input['avatar'] = $file_name;
        }
    }

    if (empty($errors)) {
        $login = mysqli_real_escape_string($link, $input['login']);
        $avatar = $input['avatar'] ? "'{$input['avatar']}'" : 'null';

        $sql = 'INSERT INTO user (email, login, password, avatar_path) VALUES '
             . "('$email', '$login', '$password', $avatar)";
        get_mysqli_result($link, $sql, false);

        if ($input['avatar']) {
            move_uploaded_file($file_path, 'uploads/' . $file_name);
        }

        header('Location: /index.php');
        exit;
    }
}

$page_content = include_template('register.php', [
    'inputs' => $form_inputs,
    'errors' => $errors
]);

$layout_content = include_template('layout.php', [
    'link' => $link,
    'title' => 'readme: регистрация',
    'page_main_class' => 'registration',
    'page_content' => $page_content
]);

print($layout_content);
