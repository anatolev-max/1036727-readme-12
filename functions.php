<?php

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}

function get_mysqli_result(mysqli $link, string $sql, string $result_type = 'all') {

    if (!$result = mysqli_query($link, $sql)) {
        update_errors_log($link, $sql, 'mysql_errors.txt');

        http_response_code(500);
        exit;

    } elseif ($result_type === 'all') {
        $result = mysqli_fetch_all($result, MYSQLI_ASSOC);

    } elseif ($result_type === 'assoc') {
        $result = mysqli_fetch_assoc($result);
    }

    return $result;
}

function update_errors_log(mysqli $link, string $sql, string $filename) : void {
    $date = date('d-m-Y H:i:s');
    $error = mysqli_error($link);
    $data = "$date - $error\n$sql\n\n";

    file_put_contents($filename, $data, FILE_APPEND | LOCK_EX);
}

function crop_text_content(string $text, int $post_id, string $style = '', int $num_letters = 300) : string {
    $style = $style ? " style=\"{$style}\"" : '';
    $text_length = mb_strlen($text);

    if ($text_length > $num_letters) {
        $words = explode(' ', $text);
        $result_words_length = 0;
        $result_words = [];

        foreach ($words as $word) {
            $result_words_length += mb_strlen($word);

            if ($result_words_length > $num_letters) {
                break;
            }

            $result_words_length += 1;
            $result_words[] = $word;
        }

        $result = implode(' ', $result_words);

        $result .= '...';
        $result = "<p{$style}>" . $result . '</p>';
        $result .= '<a class="post-text__more-link" href="post.php?id=' . $post_id . '">Читать далее</a>';
    } else {
        $result = "<p{$style}>" . $text . '</p>';
    }

    return $result;
}

function esc(string $str) : string {
    $text = htmlspecialchars($str);

    return $text;
}

function get_relative_time(string $date) : string {

    if (!strtotime($date)) {
        return '';
    }

    $ts_diff = time() - strtotime($date);

    if ($ts_diff < 60) {
        $relative_time = "$ts_diff " . get_noun_plural_form($ts_diff, 'секунда', 'секунды', 'секунд');

    } elseif ($ts_diff < 3600) {
        $minutes = floor($ts_diff / 60);
        $relative_time = "$minutes " . get_noun_plural_form($minutes, 'минута', 'минуты', 'минут');

    } elseif ($ts_diff < 86400) {
        $hours = floor($ts_diff / 3600);
        $relative_time = "$hours " . get_noun_plural_form($hours, 'час', 'часа', 'часов');

    } elseif ($ts_diff < 604800) {
        $days = floor($ts_diff / 86400);
        $relative_time = "$days " . get_noun_plural_form($days, 'день', 'дня', 'дней');

    } elseif ($ts_diff < 3024000) {
        $weeks = floor($ts_diff / 604800);
        $relative_time = "$weeks " . get_noun_plural_form($weeks, 'неделя', 'недели', 'недель');

    } elseif ($ts_diff >= 3024000) {
        $dt_diff = date_diff(date_create($date), date_create('now'));
        $months = date_interval_format($dt_diff, '%m');
        $relative_time = "$months " . get_noun_plural_form($months, 'месяц', 'месяца', 'месяцев');
    }

    return $relative_time;
}

function get_time_title(string $date) : string {
    $title = '';

    if ($ts = strtotime($date)) {
        $title = date('d.m.Y H:i', $ts);
    }

    return $title;
}

function get_sorting_link_class(string $field) : string {
    $classname = '';

    if (isset($_GET['sort']) && $_GET['sort'] === $field) {
        $classname = ' sorting__link--active';

        if (isset($_GET['dir']) && $_GET['dir'] === 'asc') {
            $classname .= ' sorting__link--reverse';
        }
    }

    return $classname;
}

function get_sorting_link_url(string $field, array $types) : string {
    if ($filter = filter_input(INPUT_GET, 'filter')) {
        $parameters['filter'] = $filter;
    }

    $parameters['sort'] = $field;
    $parameters['dir'] = $types[$field];

    $scriptname = 'popular.php';
    $query = http_build_query($parameters);
    $url = '/' . $scriptname . '?' . $query;

    return $url;
}

function get_page_link_url(int $current_page, bool $next) : string {
    $parameters['filter'] = filter_input(INPUT_GET, 'filter');
    $parameters['sort'] = filter_input(INPUT_GET, 'sort');
    $parameters['dir'] = filter_input(INPUT_GET, 'dir');

    $parameters = array_filter($parameters);

    $parameters['page'] = $current_page;
    $next ? $parameters['page']++ : $parameters['page']--;

    $scriptname = 'popular.php';
    $query = http_build_query($parameters);
    $url = '/' . $scriptname . '?' . $query;

    return $url;
}

function get_adding_post_close_url() : string {
    $url = $_SERVER['HTTP_REFERER'] ?? '/feed.php';

    if (parse_url($url, PHP_URL_PATH) === '/add.php') {
        $url = $_COOKIE['add_ref'] ?? $url;
    }

    return $url;
}

function is_content_type_valid(mysqli $link, string $type) : bool {
    $sql = 'SELECT class_name FROM content_type';
    $content_types = get_mysqli_result($link, $sql);
    $class_names = array_column($content_types, 'class_name');

    return in_array($type, $class_names);
}

function validate_content_type(mysqli $link, string $type) : void {
    $sql = 'SELECT class_name FROM content_type';
    $content_types = get_mysqli_result($link, $sql);
    $class_names = array_column($content_types, 'class_name');

    if (!in_array($type, $class_names)) {
        http_response_code(500);
        exit;
    }
}

function get_post_input(mysqli $link, string $form) : array {
    $input_names = [
        'adding-post' => [
            'heading',
            'image-url',
            'video-url',
            'post-text',
            'cite-text',
            'quote-author',
            'post-link',
            'tags',
            'file-photo',
            'content-type'
        ],
        'registration' => [
            'email',
            'login',
            'password',
            'password-repeat',
            'avatar'
        ],
        'login' => ['email', 'password'],
        'comments' => ['comment', 'post-id'],
        'messages' => ['message', 'contact-id']
    ];

    if (!isset($input_names[$form])) {
        http_response_code(500);
        exit;
    }

    foreach ($input_names[$form] as $name) {
        $input[$name] = filter_input(INPUT_POST, $name);
        $input[$name] = is_null($input[$name]) ? null : trim($input[$name]);
    }

    if ($form === 'adding-post') {
        validate_content_type($link, $input['content-type']);
        list($input['text-content'], $input['image-path']) = [null, null];
    }

    return $input;
}

function get_content_type_id(mysqli $link, string $content_type) : string {
    validate_content_type($link, $content_type);

    $sql = "SELECT id FROM content_type WHERE class_name = '$content_type'";
    $content_type_id = get_mysqli_result($link, $sql, 'assoc')['id'];

    return $content_type_id;
}

function get_required_fields(mysqli $link, string $form, string $tab = '') : array {
    $input_fields = 'i.id, i.label, i.type, i.name, i.placeholder, i.required';
    $sql = "SELECT $input_fields FROM input i "
         . 'INNER JOIN form_input fi ON fi.input_id = i.id '
         . 'INNER JOIN form f ON f.id = fi.form_id '
         . "WHERE f.name = '$form' AND i.required = 1";
    $sql .= $form === 'adding-post' ? " AND f.modifier = '$tab'" : '';
    $required_fields = get_mysqli_result($link, $sql);

    return array_column($required_fields, 'name');
}

function get_stmt_data(array $input, int $content_type_id) : array {
    $keys = ['heading', 'text-content', 'quote-author', 'image-path', 'video-url', 'post-link'];
    foreach ($keys as $key) {
        if (!array_key_exists($key, $input)) {
            http_response_code(500);
            exit;
        }

        $stmt_data[] = $input[$key];
    }

    $stmt_data[] = $_SESSION['user']['id'];
    $stmt_data[] = $content_type_id;

    return $stmt_data;
}

function get_post_value(string $name) : string {
    $value = filter_input(INPUT_POST, $name) ?? '';

    return $value;
}

function validate_post(mysqli $link, int $post_id) : int {
    $sql = "SELECT id FROM post WHERE id = $post_id";
    $result = get_mysqli_result($link, $sql, false);

    if (!mysqli_num_rows($result)) {
        http_response_code(404);
        exit;
    }

    return $post_id;
}

function validate_user(mysqli $link, int $user_id) : int {
    $sql = "SELECT id FROM user WHERE id = $user_id";
    $result = get_mysqli_result($link, $sql, false);

    if (!mysqli_num_rows($result)) {
        http_response_code(404);
        exit;
    }

    return $user_id;
}

function is_user_valid(mysqli $link, int $user_id) : bool {
    $sql = "SELECT id FROM user WHERE id = $user_id";
    $result = get_mysqli_result($link, $sql, false);

    return boolval(mysqli_num_rows($result));
}

function get_likes_indicator_class(mysqli $link, int $post_id) : string {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT id FROM post_like WHERE post_id = $post_id AND author_id = $user_id";
    $result = get_mysqli_result($link, $sql, false);

    return mysqli_num_rows($result) ? ' post__indicator--likes-active' : '';
}

function get_subscription_status(mysqli $link, int $profile_id) : bool {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT id FROM subscription WHERE author_id = $user_id AND user_id = $profile_id";
    $result = get_mysqli_result($link, $sql, false);

    return boolval(mysqli_num_rows($result));
}

function get_likes_count(mysqli $link, int $post_id) : int {
    $sql = "SELECT COUNT(id) FROM post_like WHERE post_id = $post_id";
    $likes_count = get_mysqli_result($link, $sql, 'assoc')['COUNT(id)'];

    return $likes_count;
}

function get_comment_count(mysqli $link, int $post_id) : int {
    $sql = "SELECT COUNT(id) FROM comment WHERE post_id = $post_id";
    $comment_count = get_mysqli_result($link, $sql, 'assoc')['COUNT(id)'];

    return $comment_count;

}

function get_repost_count(mysqli $link, int $post_id) : int {
    $sql = "SELECT COUNT(id) FROM post WHERE origin_post_id = $post_id";
    $repost_count = get_mysqli_result($link, $sql, 'assoc')['COUNT(id)'];

    return $repost_count;
}

function get_show_count(int $show_count) : string {
    $result = "$show_count " . get_noun_plural_form($show_count, 'просмотр', 'просмотра', 'просмотров');

    return $result;
}

function get_subscriber_count(mysqli $link, int $user_id, bool $numeric_value = false) : string {
    $sql = "SELECT COUNT(id) FROM subscription WHERE user_id = $user_id";
    $subscriber_count = get_mysqli_result($link, $sql, 'assoc')['COUNT(id)'];

    if ($numeric_value) {
        return $subscriber_count;
    }

    return get_noun_plural_form($subscriber_count, ' подписчик', ' подписчика', ' подписчиков');
}

function get_publication_count(mysqli $link, int $user_id, bool $numeric_value = false) : string {
    $sql = "SELECT COUNT(id) FROM post WHERE author_id = $user_id";
    $publication_count = get_mysqli_result($link, $sql, 'assoc')['COUNT(id)'];

    if ($numeric_value) {
        return $publication_count;
    }

    return get_noun_plural_form($publication_count, ' публикация', ' публикации', ' публикаций');
}

function get_search_sql(string $value, bool $hashtag_mode = false) : string {
    $post_fields = get_post_fields('p.');
    $user_fields = 'u.login AS author, u.avatar_path';

    if ($hashtag_mode === true) {
        $sql = "SELECT {$post_fields}, {$user_fields}, ct.class_name FROM post p "
             . 'INNER JOIN user u ON u.id = p.author_id '
             . 'INNER JOIN content_type ct ON ct.id = p.content_type_id '
             . 'INNER JOIN post_hashtag ph ON ph.post_id = p.id '
             . 'INNER JOIN hashtag h ON h.id = ph.hashtag_id '
             . "WHERE h.name = '$value' "
             . 'ORDER BY p.dt_add DESC';
    } else {
        $sql = "SELECT {$post_fields}, {$user_fields}, ct.class_name, "
             . "MATCH (p.title, p.text_content) AGAINST ('$value') AS score FROM post p "
             . 'INNER JOIN user u ON u.id = p.author_id '
             . 'INNER JOIN content_type ct ON ct.id = p.content_type_id '
             . "WHERE MATCH (p.title, p.text_content) AGAINST ('$value' IN BOOLEAN MODE) "
             . 'ORDER BY score DESC';
    }

    return $sql;
}

function get_post_hashtags(mysqli $link, int $post_id) : array {
    $sql = 'SELECT h.id, h.name FROM hashtag h '
         . 'INNER JOIN post_hashtag ph ON ph.hashtag_id = h.id '
         . 'INNER JOIN post p ON p.id = ph.post_id '
         . "WHERE p.id = $post_id";
    $hashtags = get_mysqli_result($link, $sql);

    return $hashtags;
}

function get_post_comments(mysqli $link, int $post_id) : array {
    $comments = filter_input(INPUT_GET, 'show');
    $limit = !$comments || $comments !== 'all' ? ' LIMIT 2' : '';

    $comment_fields = 'c.id, c.dt_add, c.content, c.author_id, c.post_id';
    $sql = "SELECT {$comment_fields}, u.login, u.avatar_path FROM comment c "
     . 'INNER JOIN user u ON u.id = c.author_id '
     . "WHERE post_id = $post_id "
     . "ORDER BY c.dt_add DESC{$limit}";
    $comments = get_mysqli_result($link, $sql);

    return $comments;
}

function get_contact_messages(mysqli $link, int $contact_id) : array {
    $user_id = intval($_SESSION['user']['id']);
    $message_fields = 'm.id, m.dt_add, m.content, m.status, m.sender_id, m.recipient_id';
    $sql = "SELECT {$message_fields}, u.login AS author, u.avatar_path FROM message m "
     . "INNER JOIN user u ON u.id = m.sender_id "
     . "WHERE (m.recipient_id = $user_id AND m.sender_id = $contact_id) "
     . "OR (m.recipient_id = $contact_id AND m.sender_id = $user_id) "
     . "ORDER BY m.dt_add";
    $messages = get_mysqli_result($link, $sql);

    return $messages;
}

function get_message_preview(mysqli $link, int $contact_id) : string {
    $user_id = intval($_SESSION['user']['id']);
    $sql = 'SELECT content, sender_id FROM message '
         . "WHERE (recipient_id = $user_id AND sender_id = $contact_id) "
         . "OR (recipient_id = $contact_id AND sender_id = $user_id) "
         . 'ORDER BY dt_add DESC LIMIT 1';
    $message = get_mysqli_result($link, $sql, 'assoc');
    $preview = mb_substr($message['content'], 0, 30);

    return $message['sender_id'] == $user_id ? "Вы: $preview" : $preview;
}

function get_messages_count(mysqli $link, int $contact_id = null, bool $unread = true) : string {
    $user_id = intval($_SESSION['user']['id']);
    $read_filter = $unread ? ' AND status = 0' : '';
    $contact_filter = isset($contact_id) ? " AND sender_id = $contact_id" : '';

    $sql = 'SELECT COUNT(id) FROM message '
         . "WHERE recipient_id = {$user_id}{$contact_filter}{$read_filter}";
    $messages_count = get_mysqli_result($link, $sql, 'assoc')['COUNT(id)'];

    return $messages_count;
}

function add_new_contact(mysqli $link, array &$contacts, int $contact_id) : bool {
    $user_id = intval($_SESSION['user']['id']);

    if (is_user_valid($link, $contact_id) && $contact_id !== $user_id
        && get_subscription_status($link, $contact_id)) {
        $sql = "SELECT id, login, avatar_path FROM user WHERE id = $contact_id";
        $contact = get_mysqli_result($link, $sql, 'assoc');
        array_unshift($contacts, $contact);

        return setcookie('new_contact', $contact_id);
    }

    return false;
}

function is_new_contact(mysqli $link, int $contact_id) : bool {
    $user_id = intval($_SESSION['user']['id']);
    $sql = 'SELECT id FROM message '
         . "WHERE (recipient_id = $user_id AND sender_id = $contact_id) "
         . "OR (recipient_id = $contact_id AND sender_id = $user_id)";
    $result = get_mysqli_result($link, $sql, false);

    return !boolval(mysqli_num_rows($result));
}

function update_messages_status(mysqli $link, int $contact_id) : void {
    $user_id = intval($_SESSION['user']['id']);
    $sql = 'UPDATE message SET status = 1 '
         . "WHERE sender_id = $contact_id AND recipient_id = $user_id";
    get_mysqli_result($link, $sql, false);
}

function get_messages_chat_style(array $contacts) : string {
    $style = 'padding-top: 0; border: none;';

    if (!empty($contacts)) {
        $style = 'display: flex; flex-direction: column; align-self: stretch; ';
        $style .= 'min-height: 343px; margin-bottom: -30px;';
    }

    return $style;
}

function get_origin_post(mysqli $link, int $post_id) : array {
    $sql = 'SELECT p.dt_add, u.id AS author_id, u.login AS author, '
         . 'u.avatar_path FROM post p '
         . 'INNER JOIN user u ON u.id = p.author_id '
         . "WHERE p.id = $post_id";
    $post = get_mysqli_result($link, $sql, 'assoc');

    return $post;
}

function get_post_header_h2_style(array $post) : string {
    $style = '';

    if ($post['class_name'] === 'text' && $post['is_repost']) {
        $style = 'padding: 29px 40px 26px; padding-top: 4px;';
    } elseif ($post['class_name'] === 'text') {
        $style = 'padding: 29px 40px 26px;';
    } elseif ($post['is_repost']) {
        $style = 'padding-top: 4px;';
    }

    return $style;
}

function get_post_main_style(mysqli $link, array $post) : string {
    $cond1 = !$post['is_repost'] && empty($post['COUNT(c.id)']);
    $sql = "SELECT COUNT(id) FROM post_hashtag WHERE post_id = {$post['id']}";
    $cond2 = get_mysqli_result($link, $sql, 'assoc')['COUNT(id)'];
    $style = '';

    if ($cond1 && $cond2) {
        $style = 'min-height: 67px;';
    } elseif ($cond1) {
        $style = 'min-height: 110px;';
    }

    return $style;
}

function get_datetime_value(string $date) : string {
    $datetime = '';

    if ($ts = strtotime($date)) {
        $datetime = date('Y-m-d H:i', $ts);
    }

    return str_replace(' ', 'T', $datetime);
}

function is_contact_valid(mysqli $link, int $contact_id = null) : bool {
    $contact_id = $contact_id ?? $_GET['contact'] ?? null;

    if (!isset($contact_id)) {
        return false;
    }

    $is_subscription = get_subscription_status($link, $contact_id);
    $messages_count = get_messages_count($link, $contact_id, false);

    return $is_subscription || $messages_count;
}

function add_prefix(&$item, $key, $prefix) {
    $item = $prefix . $item;
}

function get_post_fields(string $prefix) : string {
    $post_fields = [
        'id',
        'dt_add',
        'title',
        'text_content',
        'quote_author',
        'image_path',
        'video_path',
        'link',
        'show_count',
        'author_id',
        'is_repost',
        'origin_post_id',
        'content_type_id'
    ];
    array_walk($post_fields, 'add_prefix', $prefix);

    return implode(', ', $post_fields);
}

function validate_input_file_photo(array &$errors, array &$input) : void {
    $mime_types = ['image/jpeg', 'image/png', 'image/gif'];

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
}

function validate_input_image_url(array &$errors, array &$input) : void {
    $mime_types = ['image/jpeg', 'image/png', 'image/gif'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_name = uniqid();
    $file_path = "uploads/{$file_name}.jpeg";

    set_error_handler('exceptions_error_handler');
    try {
        $content = file_get_contents($input['image-url']);
        file_put_contents($file_path, $content);
        $file_type = finfo_file($finfo, $file_path);

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

    } catch (ErrorException $ex) {
        $errors['file-photo'][0] = 'Вы не загрузили файл';
        $errors['file-photo'][1] = 'Изображение';
    }
    restore_error_handler();
}

function validate_input_video_url(array &$errors, array &$input) : void {
    if (strpos($input['video-url'], 'youtube.com/watch?v=') === false) {
        $errors['video-url'][0] = 'Некорректный url-адрес';
        $errors['video-url'][1] = 'Ссылка youtube';
    }
}

function validate_link_info(array &$input) : void {
    set_error_handler('exceptions_error_handler');

    try {
        $html = file_get_contents($input['post-link']);
    } catch (ErrorException $ex) {
        return;
    }

    restore_error_handler();
    $doc = new DOMDocument();

    libxml_use_internal_errors(true);
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'utf-8');
    $doc->loadHTML($html);
    libxml_clear_errors();

    $h1_elements = $doc->getElementsByTagName('h1');

    if ($h1_elements->count() === 1) {
        $h1_childs = $h1_elements->item(0)->childNodes;

        if ($h1_childs->count() === 1
            && $h1_childs->item(0)->nodeName === '#text') {
            $h1 = trim($h1_elements->item(0)->nodeValue);
        }
    }

    $title = $doc->getElementsByTagName('title')->item(0);
    $title = !is_null($title) ? trim($title->nodeValue) : '';

    $input['text-content'] = $h1 ?? $title;
}

function cmp($a, $b) {
    $a = isset($a['sizes']) ? explode('x', $a['sizes'])[0] : '0';
    $b = isset($b['sizes']) ? explode('x', $b['sizes'])[0] : '0';

    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}

function get_icon_url(string $url) : string {
    $url = parse_url($url, PHP_URL_HOST);
    $url = "https://favicongrabber.com/api/grab/{$url}?pretty=true";

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');

    do {
        $response = curl_exec($curl_handle);
        $http_code = curl_getinfo($curl_handle)['http_code'];
        $http_error = $http_code >= 400 && $http_code !== 502;

        if (!$response || $http_error) {
            return '';
        }

    } while ($http_code === 502);

    curl_close($curl_handle);
    $response = json_decode($response, true)['icons'];
    usort($response, 'cmp');

    return $response[0]['src'] ?? '';
}

function validate_icon_file(array &$input) : void {
    $icon_url = get_icon_url($input['post-link']);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_name = uniqid();
    $file_path = "uploads/{$file_name}.jpeg";

    set_error_handler('exceptions_error_handler');
    try {
        $content = file_get_contents($icon_url);
        file_put_contents($file_path, $content);
        $file_type = finfo_file($finfo, $file_path);

        $file_extension = explode('/', $file_type);
        $file_name .= ".{$file_extension[1]}";
        rename($file_path, 'uploads/' . $file_name);
        $input['image-path'] = $file_name;

    } catch (ErrorException $ex) {}
    restore_error_handler();
}

function validate_input_post_link(array &$input) : void {
    validate_link_info($input);
    validate_icon_file($input);
}

function delete_file(string $filename) : void {
    if (file_exists($filename)) {
        unlink($filename);
    }
}

function validate_hashtag(mysqli $link, string $hashtag, int $post_id) : void {
    if (!$tag_name = ltrim($hashtag, '#')) {
        return;
    }

    $tag_name = mysqli_real_escape_string($link, $tag_name);
    $sql = "SELECT COUNT(*), id FROM hashtag WHERE name = '$tag_name'";
    $hashtag = get_mysqli_result($link, $sql, 'assoc');
    mysqli_query($link, 'START TRANSACTION');

    if ($hashtag['COUNT(*)'] === '0') {
        $sql = "INSERT INTO hashtag SET name = '$tag_name'";
        $result1 = get_mysqli_result($link, $sql, false);
        $hashtag_id = mysqli_insert_id($link);
    } else {
        $hashtag_id = $hashtag['id'];
    }

    $sql = "INSERT INTO post_hashtag (hashtag_id, post_id) VALUES ($hashtag_id, $post_id)";
    $result2 = get_mysqli_result($link, $sql, false);

    if (($result1 ?? true) && $result2) {
        mysqli_query($link, 'COMMIT');
    } else {
        mysqli_query($link, 'ROLLBACK');
    }
}
