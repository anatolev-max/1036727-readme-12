<?php

require_once('vendor/autoload.php');
require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/add.php?tab=text';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$sql = 'SELECT id, type_name, class_name, icon_width, icon_height FROM content_type';
$content_types = get_mysqli_result($link, $sql);
$class_names = array_column($content_types, 'class_name');

$tab = filter_input(INPUT_GET, 'tab') ?? 'photo';
$tab = in_array($tab, $class_names) ? $tab : 'photo';

$input_fields = 'i.id, i.label, i.type, i.name, i.placeholder, i.required';
$sql = "SELECT {$input_fields}, f.name AS form FROM input i "
     . 'INNER JOIN form_input fi ON fi.input_id = i.id '
     . 'INNER JOIN form f ON f.id = fi.form_id '
     . "WHERE f.name = 'adding-post'";

$form_inputs = get_mysqli_result($link, $sql);
$input_names = array_column($form_inputs, 'name');
$form_inputs = array_combine($input_names, $form_inputs);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input($link, 'adding-post');

    $required_fields = get_required_fields($link, 'adding-post', $tab);
    foreach ($required_fields as $field) {
        if (mb_strlen($input[$field]) === 0) {
            $errors[$field][0] = 'Это поле должно быть заполнено';
            $errors[$field][1] = $form_inputs[$field]['label'];
        }
    }

    if ($input['content-type'] === 'photo') {
        if (!empty($_FILES['file-photo']['name'])) {
            validate_input_file_photo($errors, $input);
        } elseif (filter_var($input['image-url'], FILTER_VALIDATE_URL)) {
            validate_input_image_url($errors, $input);
        } else {
            $errors['file-photo'][0] = 'Вы не загрузили файл';
            $errors['file-photo'][1] = 'Изображение';
        }

    } elseif ($input['content-type'] === 'video') {
        if (filter_var($input['video-url'], FILTER_VALIDATE_URL)) {
            validate_input_video_url($errors, $input);
        } else {
            $errors['video-url'][0] = 'Некорректный url-адрес';
            $errors['video-url'][1] = 'Ссылка youtube';
        }

    } elseif ($input['content-type'] === 'text') {
        $input['text-content'] = $input['post-text'];

    } elseif ($input['content-type'] === 'quote') {
        $input['text-content'] = $input['cite-text'];

    } elseif ($input['content-type'] === 'link') {
        if (filter_var($input['post-link'], FILTER_VALIDATE_URL)) {
            validate_input_post_link($input);
        } else {
            $errors['post-link'][0] = 'Некорректный url-адрес';
            $errors['post-link'][1] = 'Ссылка';
        }
    }

    if (empty($errors)) {
        $content_type_id = get_content_type_id($link, $input['content-type']);
        $sql = 'INSERT INTO post (title, text_content, quote_author, image_path, '
             . 'video_path, link, author_id, content_type_id) VALUES '
             . '(?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt_data = get_stmt_data($input, $content_type_id);
        $stmt = db_get_prepare_stmt($link, $sql, $stmt_data);

        if (mysqli_stmt_execute($stmt)) {
            $post_id = mysqli_insert_id($link);

            if ($tags = array_filter(explode(' ', $input['tags']))) {
                foreach ($tags as $tag_name) {
                    validate_hashtag($link, $tag_name, $post_id);
                }
            }

            $user_fields = 'u.id, u.dt_add, u.email, u.login, u.password, u.avatar_path';
            $sql = "SELECT $user_fields FROM user u "
                 . 'INNER JOIN subscription s ON s.author_id = u.id '
                 . "WHERE s.user_id = {$_SESSION['user']['id']}";

            if ($users = get_mysqli_result($link, $sql)) {

                $transport = new Swift_SmtpTransport('phpdemo.ru', 25);
                $transport->setUsername('keks@phpdemo.ru');
                $transport->setPassword('htmlacademy');

                $message = new Swift_Message();
                $message->setSubject("Новая публикация от пользователя {$_SESSION['user']['login']}");

                $mailer = new Swift_Mailer($transport);

                foreach ($users as $user) {
                    $message->setTo([$user['email'] => $user['login']]);

                    $body = "Здравствуйте, {$user['login']}. "
                          . "Пользователь {$_SESSION['user']['login']} только что опубликовал новую запись «{$input['heading']}». "
                          . "Посмотрите её на странице пользователя: http://readme.net/profile.php?id={$_SESSION['user']['id']}";
                    $message->setBody($body);
                    $message->setFrom('keks@phpdemo.ru', 'Readme');

                    $mailer->send($message);
                }
            }

            header("Location: /post.php?id={$post_id}");
            exit;
        }

        http_response_code(500);
        exit;

    } elseif (isset($input['image-path'])) {
        delete_file($input['image-path']);
    }
}

$url = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
if (parse_url($url, PHP_URL_PATH) !== '/add.php') {
    setcookie('add_ref', $url, strtotime('+30 days'));
}

$page_content = include_template('add.php', [
    'content_types' => $content_types,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = include_template('layout.php', [
    'link' => $link,
    'title' => 'readme: добавление публикации',
    'page_main_class' => 'adding-post',
    'page_content' => $page_content
]);

print($layout_content);
