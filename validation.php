<?php

const FORM_TYPES = [
    'adding-post-photo' => [
        'heading:Заголовок' => 'required',
        'file-photo:Изображение' => 'image'
    ],
    'adding-post-video' => [
        'heading:Заголовок' => 'required',
        'video-url:Ссылка youtube' => 'youtube|url|required'
    ],
    'adding-post-text' => [
        'heading:Заголовок' => 'required',
        'post-text:Текст поста' => 'required'
    ],
    'adding-post-quote' => [
        'heading:Заголовок' => 'required',
        'cite-text:Текст цитаты' => 'required',
        'quote-author:Автор' => 'required'
    ],
    'adding-post-link' => [
        'heading:Заголовок' => 'required',
        'post-link:Ссылка' => 'url|required'
    ],
    'registration' => [
        'email:Электронная почта' => 'unique|email|required',
        'login:Логин' => 'required',
        'passwd:Пароль' => 'required',
        'passwd-repeat:Повтор пароля' => 'same:passwd|required',
        'avatar:Изображение' => 'avatar'
    ],
    'login' => [
        'email:Электронная почта' => 'email|required',
        'passwd:Пароль' => 'required'
    ],
    'comments' => [
        'comment:Ваш комментарий' => 'minLength:4|required',
        'post-id:' => 'exists:post'
    ],
    'messages' => [
        'message:Ваше сообщение' => 'required',
        'contact-id:' => 'contact|exists:user'
    ]
];

function validate_form(string $form_type, array $post_data)
{
    if (!$validation_rules_list = FORM_TYPES[$form_type] ?? []) {
        return null;
    }

    $errors = [];

    foreach ($validation_rules_list as $form_field => $validation_rules) {
        list($field_name, $field_label) = explode(':', $form_field);
        $value = $post_data[$field_name] ?? '';

        foreach (explode('|', $validation_rules) as $validation_rule) {
            $validation_rule = explode(':', $validation_rule);
            $options = [$post_data, $validation_rule[1] ?? null];

            if ($error = $validation_rule[0]($value, $options)) {
                $errors[$field_name][0] = $error;
                $errors[$field_name][1] = $field_label;
            }
        }
    }

    return $errors;
}

function required(string $value, array $options)
{
    if (mb_strlen($value) === 0) {
        $error = 'Это поле должно быть заполнено';
    }

    return $error ?? null;
}

function email(string $value, array $options)
{
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $error = 'E-mail введён некорректно';
    }

    return $error ?? null;
}

function url(string $value, array $options)
{
    if (filter_var($value, FILTER_VALIDATE_URL)) {
        $error = 'Некорректный url-адрес';
    }

    return $error ?? null;
}

function youtube(string $value, array $options)
{
    if (strpos($value, 'youtube.com/watch?v=') === false) {
        $error = 'Некорректный url-адрес';
    }

    return $error ?? null;
}

function minLength(string $value, array $options)
{
    if (isset($options[1]) && (mb_strlen($value) < intval($options[1]))) {
        $error = "Значение должно быть от {$options[1]} символов";
    }

    return $error ?? null;
}

function exists(string $value, array $options)
{
    if (isset($options[1]) && in_array($options[1], ['post', 'user'])) {
        $method = 'is' . ucfirst($options[1]) . 'Valid';
        $method_exist = method_exists('Database', $method);

        if ($method_exist && !Database::getInstance()->$method($value)) {
            $error = true;
        }
    }

    return $error ?? null;
}

function contact(string $value, array $options)
{
    if (!Database::getInstance()->isContactValid($value)) {
        $error = true;
    }

    return $error ?? null;
}

function unique(string $value, array $options)
{
    if (Database::getInstance()->isEmailExist($value)) {
        $error = 'Пользователь с этим email уже зарегистрирован';
    }

    return $error ?? null;
}

function same(string $value, array $options)
{
    if (isset($options[0], $options[1])) {
        list($post_data, $key) = $options;

        if ($value !== ($post_data[$key] ?? '')) {
            $error = 'Пароли не совпадают';
        }
    }

    return $error ?? null;
}

function avatar(string $value, array $options, int $max_size = 1)
{
    if (!empty($_FILES['avatar']['name'])) {
        $file_path = $_FILES['avatar']['tmp_name'];
        $file_size = $_FILES['avatar']['size'];
        $file_type = mime_content_type($file_path);

        if (!in_array($file_type, ACCEPT_MIME_TYPES)) {
            $error = 'Неверный MIME-тип файла';
        } elseif ($file_size > (BYTES_PER_MEGABYTE * $max_size)) {
            $error = "Максимальный размер файла: {$max_size}Мб";
        }
    }

    return $error ?? null;
}

function image(string $value, array $options, int $max_size = 1)
{
    if (!empty($_FILES['file-photo']['name'])) {
        $file_path = $_FILES['file-photo']['tmp_name'];
        $file_size = $_FILES['file-photo']['size'];
        $file_type = mime_content_type($file_path);

    } elseif (isset($post_data['image-url'])) {
        set_error_handler('exceptions_error_handler');
        try {
            $temp_file = tmpfile();
            $content = file_get_contents($post_data['image-url']);
            fwrite($temp_file, $content);
            $file_path = stream_get_meta_data($temp_file)['uri'];
            $file_size = fstat($temp_file)['size'];
            $file_type = mime_content_type($file_path);
            fclose($temp_file);

        } catch (ErrorException $ex) {
            return 'Вы не загрузили файл';
        }
        restore_error_handler();

    } else {
        return 'Вы не загрузили файл';
    }

    if (!in_array($file_type, ACCEPT_MIME_TYPES)) {
        return 'Неверный MIME-тип файла';
    } elseif ($file_size > (BYTES_PER_MEGABYTE * $max_size)) {
        return "Максимальный размер файла: {$max_size}Мб";
    }
}
