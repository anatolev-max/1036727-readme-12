<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/messages.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$user_id = $_SESSION['user']['id'];

$form_inputs = Database::getInstance()->getFormInputs('messages');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input('messages');
    $errors = validate_form('messages', $input);

    if (!is_null($errors) && empty($errors)) {
        $contact_id = $input['contact-id'];
        $message = cut_out_extra_spaces($input['message']);
        $stmt_data = [$message, $user_id, $contact_id];
        Database::getInstance()->insertMessage($stmt_data);

        if (($_COOKIE['new_contact'] ?? null) == $contact_id) {
            setcookie('new_contact', '', time() - 3600);
        }

        header("Location: /messages.php?contact={$contact_id}");
        exit;
    }
}

if (isset($_GET['contact'])) {
    $contact_id = intval(filter_input(INPUT_GET, 'contact'));
    Database::getInstance()->updateMessagesStatus($contact_id);
}

$contacts = Database::getInstance()->getContacts();

if (isset($_GET['contact'])) {

    if (!in_array($contact_id, array_column($contacts, 'id'))) {
        if (!Database::getInstance()->addNewContact($contacts, $contact_id)
            && $contact_id = $_COOKIE['new_contact'] ?? null) {
            Database::getInstance()->addNewContact($contacts, $contact_id);
        }

    } elseif ($contact_id = $_COOKIE['new_contact'] ?? null) {
        Database::getInstance()->addNewContact($contacts, $contact_id);
    }

} elseif ($contact_id = $_COOKIE['new_contact'] ?? null) {
    Database::getInstance()->addNewContact($contacts, $contact_id);
}

$message_count = Database::getInstance()->getMessageCount();

$page_content = include_template('messages.php', [
    'contacts' => $contacts,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = include_template('layouts/base.php', [
    'title' => 'readme: личные сообщения',
    'main_modifier' => 'messages',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
