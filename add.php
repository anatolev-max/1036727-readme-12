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

$user_id = $_SESSION['user']['id'];

$ctypes = Database::getInstance()->getContentTypes();
$class_names = array_column($ctypes, 'class_name');
$ctypes = array_combine($class_names, $ctypes);

$form_inputs = Database::getInstance()->getFormInputs('adding-post');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input('adding-post');

    if (Database::getInstance()->isContentTypeValid($input['content-type'] ?? '')) {
        $form_name = "adding-post-{$input['content-type']}";
        $errors = validate_form($form_name, $input);

        if (!is_null($errors) && empty($errors)) {
            if ($input['content-type'] === 'text') {
                $input['text-content'] = cut_out_extra_spaces($input['post-text']);
            } elseif ($input['content-type'] === 'quote') {
                $input['text-content'] = cut_out_extra_spaces($input['cite-text']);
            } elseif ($input['content-type'] === 'link') {
                validate_input_post_link($input);
            }

            $input['image-path'] = upload_image_file($input, $errors);
            $ctype_id = $ctypes[$input['content-type']]['id'];
            $stmt_data = get_stmt_data($input, 'adding-post');
            $stmt_data += [$user_id, 0, null, $ctype_id];
            $post_id = Database::getInstance()->insertPost($stmt_data);

            if ($hashtags = explode(' ', $input['tags'])) {
                process_post_hashtags($hashtags, $post_id);
            }

            if ($subscribers = Database::getInstance()->getSubscribers()) {
                send_post_notifications($subscribers, $input['heading']);
            }

            header("Location: /post.php?id={$post_id}");
            exit;
        }
    }
}

$url = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
if (parse_url($url, PHP_URL_PATH) !== '/add.php') {
    setcookie('add_ref', $url, strtotime('+30 days'));
}

$message_count = Database::getInstance()->getMessageCount();

$page_content = include_template('add.php', [
    'content_types' => $ctypes,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = include_template('layouts/base.php', [
    'title' => 'readme: добавление публикации',
    'main_modifier' => 'adding-post',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
