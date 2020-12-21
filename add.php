<?php

require_once('init.php');
set_error_handler('exceptions_error_handler');

$sql = 'SELECT * FROM content_type';
$content_types = get_mysqli_result($link, $sql);
$class_names = array_column($content_types, 'class_name');

$tab = filter_input(INPUT_GET, 'tab') ?? 'photo';
$tab = in_array($tab, $class_names) ? $tab : 'photo';

$sql = 'SELECT i.* FROM input i '
     . 'INNER JOIN form_input fi ON fi.input_id = i.id '
     . 'INNER JOIN form f ON f.id = fi.form_id '
     . "WHERE f.name LIKE '%adding-post__%'";

$form_inputs = get_mysqli_result($link, $sql);
$input_names = array_column($form_inputs, 'name');
$form_inputs = array_combine($input_names, $form_inputs);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = get_post_input($link, 'adding-post');
    $content_type = get_content_type($link);
    $content_type_id = get_content_type_id($link, $content_type);

    switch ($content_type) {
        case 'photo':
            $mime_types = ['image/jpeg', 'image/png', 'image/gif'];

            if (!empty($_FILES['file-photo']['name'])) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $file_path = $_FILES['file-photo']['tmp_name'];
                $file_size = $_FILES['file-photo']['size'];
                $file_type = finfo_file($finfo, $file_path);

                if (!in_array($file_type, $mime_types)) {
                    $errors['file-photo'][0] = 'Неверный MIME-тип файла';
                    $errors['file-photo'][1] = 'Изображение';
                } elseif ($file_size > 1000000) {
                    $errors['file-photo'][0] = 'Максимальный размер файла: 1Мб';
                    $errors['file-photo'][1] = 'Изображение';
                } else {
                    $file_name = uniqid();
                    $file_extension = explode('/', $file_type);
                    $file_name .= ".{$file_extension[1]}";
                    move_uploaded_file($file_path, 'uploads/' . $file_name);
                    $input['image-path'] = $file_name;
                }

            } elseif (!empty($input['image-url']) && filter_var($input['image-url'], FILTER_VALIDATE_URL)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $file_name = uniqid();
                $file_path = "uploads/{$file_name}.jpeg";

                try {
                    $content = file_get_contents($input['image-url']);
                    file_put_contents($file_path, $content);
                    $file_type = finfo_file($finfo, $file_path);
                } catch (ErrorException $ex) {
                    $errors['file-photo'][0] = 'Вы не загрузили файл';
                    $errors['file-photo'][1] = 'Изображение';
                    break;
                }

                if (!in_array($file_type, $mime_types)) {
                    unlink($file_path);
                    $errors['file-photo'][0] = 'Неверный MIME-тип файла';
                    $errors['file-photo'][1] = 'Изображение';
                } elseif (filesize($file_path) > 1000000) {
                    unlink($file_path);
                    $errors['file-photo'][0] = 'Максимальный размер файла: 1Мб';
                    $errors['file-photo'][1] = 'Изображение';
                } else {
                    $file_extension = explode('/', $file_type);
                    $file_name .= ".{$file_extension[1]}";
                    rename($file_path, 'uploads/' . $file_name);
                    $input['image-path'] = $file_name;
                }

            } else {
                $errors['file-photo'][0] = 'Вы не загрузили файл';
                $errors['file-photo'][1] = 'Изображение';
            }

            break;

        case 'video':
            if (!empty($input['video-url']) && filter_var($input['video-url'], FILTER_VALIDATE_URL)) {

                if (strpos($input['video-url'], 'youtube.com/watch?v=') === false) {
                    $errors['video-url'][0] = 'Некорректный url-адрес';
                    $errors['video-url'][1] = 'Ссылка youtube';
                }

                // функция ckeck_youtube_url работает некорректно

                // } elseif (is_string(check_youtube_url($input['video-url']))) {
                //     $errors['video-url'][0] = 'Видео по такой ссылке не найдено';
                //     $errors['video-url'][1] = 'Ссылка youtube';
                // }

            } else {
                $errors['video-url'][0] = 'Некорректный url-адрес';
                $errors['video-url'][1] = 'Ссылка youtube';
            }

            break;

        case 'link':
            if (!empty($input['post-link']) && !filter_var($input['post-link'], FILTER_VALIDATE_URL)) {
                $errors['post-link'][0] = 'Некорректный url-адрес';
                $errors['post-link'][1] = 'Ссылка';
            }

            break;
    }

    if ($content_type == 'text') {
        $input['text-content'] = $input['post-text'];
    } elseif ($content_type == 'quote') {
        $input['text-content'] = $input['cite-text'];
    }

    $required_fields = get_required_fields($link, $content_type);
    foreach ($required_fields as $field) {
        if (strlen($input[$field]) == 0) {
            $errors[$field][0] = 'Заполните это поле';
            $errors[$field][1] = $form_inputs[$field]['label'];
        }
    }

    if (empty($errors)) {
        $sql = 'INSERT INTO post (title, text_content, quote_author, image_path, video_path, link, author_id, content_type_id) VALUES '
             . '(?, ?, ?, ?, ?, ?, 1, ?)';
        $stmt_data = get_stmt_data($input, $content_type_id);
        $stmt = db_get_prepare_stmt($link, $sql, $stmt_data);

        if (mysqli_stmt_execute($stmt)) {
            $post_id = mysqli_insert_id($link);

            if ($tags = array_filter(explode(' ', $input['tags']))) {

                foreach ($tags as $tag_name) {
                    $tag_name = mysqli_real_escape_string($link, $tag_name);
                    $sql = "SELECT COUNT(*), id FROM hashtag WHERE name = '$tag_name'";
                    $hashtag = get_mysqli_result($link, $sql, FETCH_ASSOC);

                    if ($hashtag['COUNT(*)'] == 0) {
                        $sql = "INSERT INTO hashtag SET name = '$tag_name'";
                        get_mysqli_result($link, $sql, MODIFICATION_QUERY);
                        $hashtag_id = mysqli_insert_id($link);
                    } else {
                        $hashtag_id = $hashtag['id'];
                    }

                    $sql = "INSERT INTO post_hashtag (hashtag_id, post_id) VALUES ($hashtag_id, $post_id)";
                    get_mysqli_result($link, $sql, MODIFICATION_QUERY);
                }
            }

            header("Location: /post.php?id=$post_id");
            exit;
        }

        http_response_code(500);
        exit;

    } elseif (isset($input['image-path']) && file_exists($input['image-path'])) {
        unlink($input['image-path']);
    }
}

$is_auth = rand(0, 1);
$user_name = 'Максим'; // укажите здесь ваше имя

$page_content = include_template('adding-post.php', [
    'content_types' => $content_types,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = include_template('layout.php', [
    'page_main_class' => 'adding-post',
    'title' => 'readme: добавление публикации',
    'page_content' => $page_content,
    'is_auth' => $is_auth,
    'username' => $user_name
]);

print($layout_content);
