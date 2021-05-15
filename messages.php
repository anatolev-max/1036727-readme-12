<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/messages.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$form_inputs = get_form_inputs($con, 'messages');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input('messages');

    if (mb_strlen($input['message']) === 0) {
        $errors['message'][0] = 'Это поле должно быть заполнено';
        $errors['message'][1] = $form_inputs['message']['label'];
    }

    if (empty($errors)) {
        $contact_id = validate_user($con, intval($input['contact-id']));

        if (!is_contact_valid($con, $contact_id)) {
            http_response_code(500);
            exit;
        }

        $message = preg_replace('/(\r\n){3,}|(\n){3,}/', "\n\n", $input['message']);
        $message = preg_replace('/\040\040+/', ' ', $message);
        $stmt_data = [$message, $_SESSION['user']['id'], $contact_id];
        insert_message($con, $stmt_data);

        if (($_COOKIE['new_contact'] ?? null) == $contact_id) {
            setcookie('new_contact', '', time() - 3600);
        }

        header("Location: /messages.php?contact={$contact_id}");
        exit;
    }
}

if (isset($_GET['contact'])) {
    $contact_id = intval(filter_input(INPUT_GET, 'contact'));
    update_messages_status($con, $contact_id);
}

$contacts = get_contacts($con);

if (isset($_GET['contact'])) {

    if (!in_array($contact_id, array_column($contacts, 'id'))) {
        if (!add_new_contact($con, $contacts, $contact_id)
            && $contact_id = $_COOKIE['new_contact'] ?? null) {
            add_new_contact($con, $contacts, $contact_id);
        }

    } elseif ($contact_id = $_COOKIE['new_contact'] ?? null) {
        add_new_contact($con, $contacts, $contact_id);
    }

} elseif ($contact_id = $_COOKIE['new_contact'] ?? null) {
    add_new_contact($con, $contacts, $contact_id);
}

$page_content = include_template('messages.php', [
    'contacts' => $contacts,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$messages_count = get_message_count($con);
$layout_content = include_template('layout.php', [
    'title' => 'readme: личные сообщения',
    'main_modifier' => 'messages',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
