<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/messages.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$input_fields = 'i.id, i.label, i.type, i.name, i.placeholder, i.required';
$sql = "SELECT $input_fields FROM input i "
     . 'INNER JOIN form_input fi ON fi.input_id = i.id '
     . 'INNER JOIN form f ON f.id = fi.form_id '
     . "WHERE f.name = 'messages'";

$form_inputs = get_mysqli_result($link, $sql);
$input_names = array_column($form_inputs, 'name');
$form_inputs = array_combine($input_names, $form_inputs);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input($link, 'messages');

    if (mb_strlen($input['message']) === 0) {
        $errors['message'][0] = 'Это поле должно быть заполнено';
        $errors['message'][1] = $form_inputs['message']['label'];
    }

    if (empty($errors)) {
        $contact_id = validate_user($link, intval($input['contact-id']));

        if ($contact_id === $user_id
            || !is_contact_valid($link, $contact_id)) {
            http_response_code(500);
            exit;
        }

        $message = mysqli_real_escape_string($link, $input['message']);
        $sql = 'INSERT INTO message (content, sender_id, recipient_id) VALUES '
             . "('$message', $user_id, $contact_id)";
        get_mysqli_result($link, $sql, false);
        setcookie('new_contact', '', time() - 3600);

        header("Location: /messages.php?contact={$contact_id}");
        exit;
    }
}

$sql = 'SELECT u.id, u.login, u.avatar_path, MAX(m.dt_add) FROM message m '
     . 'INNER JOIN user u ON u.id = m.sender_id OR u.id = m.recipient_id '
     . "WHERE (m.sender_id = $user_id OR m.recipient_id = $user_id) AND u.id != $user_id "
     . 'GROUP BY u.id '
     . 'ORDER BY MAX(m.dt_add) DESC';
$contacts = get_mysqli_result($link, $sql);

if (isset($_GET['contact'])) {
    $contact_id = intval(filter_input(INPUT_GET, 'contact'));
    update_messages_status($link, $contact_id);

    if (!in_array($contact_id, array_column($contacts, 'id'))) {

        if (!add_new_contact($link, $contacts, $contact_id)
            && $contact_id = $_COOKIE['new_contact'] ?? null) {
            add_new_contact($link, $contacts, $contact_id);
        }

    } elseif ($contact_id = $_COOKIE['new_contact'] ?? null) {
        add_new_contact($link, $contacts, $contact_id);
    }

} elseif ($contact_id = $_COOKIE['new_contact'] ?? null) {
    add_new_contact($link, $contacts, $contact_id);
}

$page_content = include_template('messages.php', [
    'inputs' => $form_inputs,
    'errors' => $errors,
    'link' => $link,
    'contacts' => $contacts
]);

$layout_content = include_template('layout.php', [
    'link' => $link,
    'title' => 'readme: личные сообщения',
    'page_main_class' => 'messages',
    'page_content' => $page_content
]);

print($layout_content);
