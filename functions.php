<?php

function exceptions_error_handler($severity, $message, $filename, $lineno)
{
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}

function crop_text_content(string $text, int $post_id, string $style = '', int $num_letters = 300): string
{
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

function get_post_input(string $form): array
{
    $input_names = [
        'adding-post' => [
            'heading',
            'image-url',
            'video-url',
            'post-text',
            'cite-text',
            'text-content',
            'quote-author',
            'post-link',
            'tags',
            'file-photo',
            'image-path',
            'content-type'
        ],
        'registration' => [
            'email',
            'login',
            'passwd',
            'passwd-repeat',
            'avatar-path'
        ],
        'login' => ['email', 'passwd'],
        'comments' => ['comment', 'post-id'],
        'messages' => ['message', 'contact-id']
    ];

    if (!isset($input_names[$form])) {
        return [];
    }

    foreach ($input_names[$form] as $name) {
        $input[$name] = filter_input(INPUT_POST, $name);
        $input[$name] = is_null($input[$name]) ? null : trim($input[$name]);
    }

    return $input;
}

function get_post_fields(string $mode = 'select'): array
{
    $post_fields = [
        'select' => [
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
        ],
        'insert' => [
            'title',
            'text_content',
            'quote_author',
            'image_path',
            'video_path',
            'link',
            'author_id',
            'is_repost',
            'origin_post_id',
            'content_type_id'
        ]
    ];

    return $post_fields[$mode] ?? [];
}

function get_stmt_data(array $input, string $form): array
{
    $input_keys = [
        'adding-post' => [
            'heading',
            'text-content',
            'quote-author',
            'image-path',
            'video-url',
            'post-link'
        ],
        'registration' => [
            'email',
            'login',
            'passwd',
            'avatar-path'
        ]
    ];

    if (!isset($input_keys[$form])) {
        return [];
    }

    foreach ($input_keys[$form] as $key) {
        if (!array_key_exists($key, $input)) {
            return [];
        }
        $stmt_data[$key] = $input[$key];
    }

    return $stmt_data;
}

function get_relative_time(string $date): string
{

    if (!strtotime($date)) {
        return '';
    }

    $array = [
        [SECONDS_PER_MINUTE, 1, 'секунда', 'секунды', 'секунд'],
        [SECONDS_PER_HOUR, SECONDS_PER_MINUTE, 'минута', 'минуты', 'минут'],
        [SECONDS_PER_DAY, SECONDS_PER_HOUR, 'час', 'часа', 'часов'],
        [SECONDS_PER_WEEK, SECONDS_PER_DAY, 'день', 'дня', 'дней'],
        [SECONDS_PER_MONTH, SECONDS_PER_WEEK, 'неделя', 'недели', 'недель'],
        [SECONDS_PER_YEAR, SECONDS_PER_DAY * 30, 'месяц', 'месяца', 'месяцев'],
        [PHP_INT_MAX, SECONDS_PER_YEAR, 'год', 'года', 'лет']
    ];

    $ts_diff = time() - strtotime($date);

    $i = 0;
    do {
        $time = floor($ts_diff / $array[$i][1]);
        $relative_time = "$time " . get_noun_plural_form($time, $array[$i][2], $array[$i][3], $array[$i][4]);
        $i++;

        if ($ts_diff < $array[$i - 1][0]) {
            break;
        }

    } while ($i < count($array));

    return $relative_time;
}

function get_datetime_value(string $date): string
{
    if ($ts = strtotime($date)) {
        $datetime = date('Y-m-d H:i', $ts);
        $datetime = str_replace(' ', 'T', $datetime);
    }

    return $datetime ?? '';
}

function get_time_title(string $date): string
{
    if ($ts = strtotime($date)) {
        $title = date('d.m.Y H:i', $ts);
    }

    return $title ?? '';
}

function get_sorting_link_class(string $field): string
{
    if (isset($_GET['sort']) && $_GET['sort'] === $field) {
        $classname = ' sorting__link--active';
        if (isset($_GET['dir']) && $_GET['dir'] === 'asc') {
            $classname .= ' sorting__link--reverse';
        }
    }

    return $classname ?? '';
}

function get_sorting_link_url(string $field, array $types): string
{
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

function get_page_link_url(int $current_page, bool $next): string
{
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

function get_adding_post_close_url(): string
{
    $url = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
    if (parse_url($url, PHP_URL_PATH) === '/add.php') {
        $url = $_COOKIE['add_ref'] ?? $url;
    }

    return $url;
}

function validate_link_info(array &$input)
{
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

function cmp(array $a, array $b): int
{
    $a = isset($a['sizes']) ? explode('x', $a['sizes'])[0] : '0';
    $b = isset($b['sizes']) ? explode('x', $b['sizes'])[0] : '0';

    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}

function get_icon_url(string $url): string
{
    $url = parse_url($url, PHP_URL_HOST);
    $url = "https://favicongrabber.com/api/grab/{$url}?pretty=true";

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Application');

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

function validate_icon_file(array &$input)
{
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

function validate_input_post_link(array &$input)
{
    validate_link_info($input);
    validate_icon_file($input);
}

function upload_avatar_file()
{
    if (!empty($_FILES['avatar']['name'])) {
        $file_path = $_FILES['avatar']['tmp_name'];
        $file_type = mime_content_type($file_path);
        $file_extension = explode('/', $file_type);
        $file_name = uniqid() . ".{$file_extension[1]}";
        move_uploaded_file($file_path, "uploads/$file_name");
    }

    return $file_name ?? null;
}

function upload_image_file(array $input, array &$errors)
{
    if (!empty($_FILES['file-photo']['name'])) {
        $file_path = $_FILES['file-photo']['tmp_name'];
        $file_type = mime_content_type($file_path);
        $file_extension = explode('/', $file_type);
        $file_name = uniqid() . ".{$file_extension[1]}";
        move_uploaded_file($file_path, "uploads/$file_name");

    } elseif (isset($input['image-url'])) {
        set_error_handler('exceptions_error_handler');
        try {
            $temp_file = tmpfile();
            $content = file_get_contents($input['image-url']);
            fwrite($temp_file, $content);
            $file_path = stream_get_meta_data($temp_file)['uri'];
            $file_type = mime_content_type($file_path);
            fclose($temp_file);

            $file_extension = explode('/', $file_type);
            $file_name = uniqid() . ".{$file_extension[1]}";
            file_put_contents('uploads/' . $file_name, $content);

        } catch (ErrorException $ex) {
            $errors['file-photo'][0] = 'Вы не загрузили файл';
            $errors['file-photo'][1] = 'Изображение';
        }
        restore_error_handler();
    }

    return $file_name ?? null;
}

function process_post_hashtags(array $hashtags, int $post_id)
{
    array_walk($hashtags, function (&$val, $key) {
        $val = ltrim($val, '#');
    });

    if ($hashtags = array_filter($hashtags)) {
        $exist_hashtags = Database::getInstance()->getExistHashtags($hashtags);
        $exist_hashtag_ids = array_column($exist_hashtags, 'id');
        $exist_hashtag_names = array_column($exist_hashtags, 'name');
        $not_exist_hashtags = array_diff($hashtags, $exist_hashtag_names);

        foreach ($not_exist_hashtags as $hashtag) {
            $hashtag_ids[] = Database::getInstance()->insertHashtag($hashtag);
        }

        $hashtag_ids = array_merge($exist_hashtag_ids, $hashtag_ids);

        foreach ($hashtag_ids as $hashtag_id) {
            Database::getInstance()->insertPostHashtag([$hashtag_id, $post_id]);
        }
    }
}

function send_post_notifications(array $recipients, string $post_title)
{
    try {
        $smtp_config = require('config/smtp.php');
        $transport = new Swift_SmtpTransport($smtp_config['host'], $smtp_config['port']);
        $transport->setUsername($smtp_config['username']);
        $transport->setPassword($smtp_config['password']);

        $message = new Swift_Message();
        $message->setSubject("Новая публикация от пользователя {$_SESSION['user']['login']}");

        $mailer = new Swift_Mailer($transport);

        foreach ($recipients as $recipient) {
            $message->setTo([$recipient['email'] => $recipient['login']]);
            $body = include_template('notifications/post.php', [
                'recipient' => $recipient,
                'post_title' => $post_title
            ]);
            $message->setBody($body);
            $message->setFrom('keks@phpdemo.ru', 'Readme');

            $mailer->send($message);
        }

    } catch (Swift_TransportException $ex) {}
}

function get_post_value(string $name): string
{
    return filter_input(INPUT_POST, $name) ?? '';
}

function esc(string $str): string
{
    return htmlspecialchars($str);
}

function add_prefix(&$item, $key, $prefix)
{
    $item = $prefix . $item;
}

function cut_out_extra_spaces(string $text): string
{
    $text = preg_replace('/(\r\n){3,}|(\n){3,}/', "\n\n", $text);
    return preg_replace('/\040\040+/', ' ', $text);
}

function get_password_hash(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}
