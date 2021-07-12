<?php

class Database {
    private $mysqli;
    private static $db;

    public static function getInstance(): Database
    {
        if (self::$db === null) {
            self::$db = new Database();
        }

        return self::$db;
    }

    private function __construct()
    {
        $db_config = require_once('config/db.php');
        $db_config = array_values($db_config);
        $this->mysqli = new mysqli(...$db_config);

        if (!$this->mysqli) {
            http_response_code(500);
            exit;
        }

        $this->mysqli->set_charset('utf8');
    }

    private function __clone() {}
    private function __wakeup() {}

    public function select(string $sql, array $stmt_data = []): array
    {
        $stmt = $this->executeQuery($sql, $stmt_data);
        if (!$result = $stmt->get_result()) {
            http_response_code(500);
            exit;
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function selectOne(string $sql, array $stmt_data = [])
    {
        $stmt = $this->executeQuery($sql, $stmt_data);
        if (!$result = $stmt->get_result()) {
            http_response_code(500);
            exit;
        }

        return $result->fetch_assoc();
    }

    public function getNumRows(string $sql, array $stmt_data = []): int
    {
        $stmt = $this->executeQuery($sql, $stmt_data);
        if (!$result = $stmt->get_result()) {
            http_response_code(500);
            exit;
        }

        return $result->num_rows;
    }

    public function getLastId(string $sql, array $stmt_data = []): int
    {
        $stmt = $this->getPrepareStmt($sql, $stmt_data);
        if (!$stmt->execute()) {
            http_response_code(500);
            exit;
        }

        return $stmt->insert_id;
    }

    public function executeQuery(string $sql, array $stmt_data = []): mysqli_stmt
    {
        $stmt = $this->getPrepareStmt($sql, $stmt_data);
        if (!$stmt->execute()) {
            http_response_code(500);
            exit;
        }

        return $stmt;
    }

    private function getPrepareStmt(string $sql, array $data): mysqli_stmt
    {
        if (!$stmt = $this->mysqli->prepare($sql)) {
            http_response_code(500);
            exit;
        }

        if ($data) {
            $types = '';
            $stmt_data = [];

            foreach ($data as $value) {
                $type = 's';

                if (is_int($value)) {
                    $type = 'i';
                } elseif (is_double($value)) {
                    $type = 'd';
                } elseif (is_string($value)) {
                    $type = 's';
                }

                if ($type) {
                    $types .= $type;
                    $stmt_data[] = $value;
                }
            }

            $values = array_merge([$types], $stmt_data);

            if (!$stmt->bind_param(...$values)) {
                http_response_code(500);
                exit;
            }
        }

        return $stmt;
    }

    public function getContentTypes(): array
    {
        $query = (new QueryBuilder())
            ->select(['id', 'type_name', 'class_name', 'icon_width', 'icon_height'])
            ->from('content_type');

        return $query->all();
    }

    public function getFormInputs(string $form): array
    {
        $query = (new QueryBuilder())
            ->select(['id', 'label', 'name', 'placeholder', 'required'], 'i.')
            ->addSelect(['it.name AS type', 'f.name AS form'])
            ->from('input i')
            ->join('LEFT', 'input_type it', 'it.id = i.type_id')
            ->join('LEFT', 'form_input fi', 'fi.input_id = i.id')
            ->join('LEFT', 'form f', 'f.id = fi.form_id')
            ->where('=', 'f.name', '?');
        $form_inputs = $query->all([$form]);
        $input_names = array_column($form_inputs, 'name');

        return array_combine($input_names, $form_inputs);
    }

    public function isContentTypeValid(string $content_type): bool
    {
        $query = (new QueryBuilder())
            ->select(['class_name'])
            ->from('content_type')
            ->where('=', 'class_name', '?');

        return $query->exists([$content_type]);
    }

    public function getRequiredFields(string $form, string $tab = ''): array
    {
        $stmt_data = array_filter([$form, $tab]);
        $query = (new QueryBuilder())
            ->select(['i.name'])
            ->from('input i')
            ->join('LEFT', 'form_input fi', 'fi.input_id = i.id')
            ->join('LEFT', 'form f', 'f.id = fi.form_id')
            ->where('=', 'f.name', '?')
            ->andWhere('=', 'i.required', '1')
            ->andFilterWhere($tab, 'f.modifier = ?');

        return array_column($query->all($stmt_data), 'name');
    }

    public function insertPost(array $stmt_data): int {
        $post_fields = get_post_fields('insert');
        $query = (new QueryBuilder())
            ->insert('post', $post_fields, array_fill(0, 10, '?'));

        return $this->getLastId($query->getQuery(), $stmt_data);
    }

    public function getExistHashtags(array $hashtags): array
    {
        $placeholders = array_fill(0, count($hashtags), '?');
        $placeholders = implode(', ', $placeholders);
        $query = (new QueryBuilder())
            ->select(['id', 'name'])
            ->from('hashtag')
            ->where('IN', 'name', "({$placeholders})");

        return $query->all($hashtags);
    }

    public function insertHashtag(string $hashtag): int
    {
        $query = (new QueryBuilder())
            ->insert('hashtag', ['name'], ['?']);

        return $this->getLastId($query->getQuery(), [$hashtag]);
    }

    public function insertPostHashtag(array $stmt_data)
    {
        $query = (new QueryBuilder())
            ->insert('post_hashtag', ['hashtag_id', 'post_id'], ['?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    public function getSubscribers(): array
    {
        $query = (new QueryBuilder())
            ->select(['u.email', 'u.login'])
            ->from('user u')
            ->join('LEFT', 'subscription s', 's.author_id = u.id')
            ->where('=', 's.user_id', '?');

        return $query->all([$_SESSION['user']['id']]);
    }

    public function getMessageCount(): string
    {
        $query = (new QueryBuilder())
            ->select(['COUNT(id)'])
            ->from('message')
            ->where('=', 'recipient_id', '?')
            ->andWhere('=', 'status', '0');

        return $query->one([$_SESSION['user']['id']])['COUNT(id)'];
    }

    public function getPostHashtags(int $post_id): array
    {
        $query = (new QueryBuilder())
            ->select(['h.name'])
            ->from('hashtag h')
            ->join('LEFT', 'post_hashtag ph', 'ph.hashtag_id = h.id')
            ->join('LEFT', 'post p', 'p.id = ph.post_id')
            ->where('=', 'p.id', '?');

        return $query->all([$post_id]);
    }

    public function getFeedPosts(string $content_type): array
    {
        $post_fields = get_post_fields();
        $user_id = $_SESSION['user']['id'];
        $stmt_data = array_filter([$user_id, $user_id, $content_type]);
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT p2.id) AS repost_count',
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['u.login AS author', 'u.avatar_path', 'ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post p2', 'p2.origin_post_id = p.id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id')
            ->andWhere('=', 'pl2.author_id', '?')
            ->join('LEFT', 'subscription s', 's.user_id = p.author_id')
            ->where('=', 's.author_id', '?')
            ->andFilterWhere($content_type, 'ct.class_name = ?')
            ->groupBy('p.id')
            ->orderBy('p.dt_add ASC');
        $posts = $query->all($stmt_data);

        for ($i = 0; $i < count($posts); $i++) {
            $hashtags = $this->getPostHashtags($posts[$i]['id']);
            $posts[$i]['hashtags'] = $hashtags;
        }

        return $posts;
    }

    public function getUserByEmail(string $email)
    {
        $query = (new QueryBuilder())
            ->select(['id', 'dt_add', 'email', 'login', 'password', 'avatar_path'])
            ->from('user')
            ->where('=', 'email', '?');

        return $query->one([$email]) ?? null;
    }

    public function isPostValid(int $post_id): bool
    {
        $query = (new QueryBuilder())
            ->select(['id'])
            ->from('post')
            ->where('=', 'id', '?');

        return $query->exists([$post_id]);
    }

    public function validatePost(int $post_id): int
    {
        if (!$this->isPostValid($post_id)) {
            http_response_code(404);
            exit;
        }

        return $post_id;
    }

    public function isPostLike(array $stmt_data): bool
    {
        $query = (new QueryBuilder())
            ->select(['id'])
            ->from('post_like')
            ->where('=', 'post_id', '?')
            ->andWhere('=', 'author_id', '?');

        return $query->exists($stmt_data);
    }

    public function insertPostLike(array $stmt_data)
    {
        $query = (new QueryBuilder())
            ->insert('post_like', ['post_id', 'author_id'], ['?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    public function deletePostLike(array $stmt_data)
    {
        $query = (new QueryBuilder())
            ->delete('post_like')
            ->where('=', 'post_id', '?')
            ->andWhere('=', 'author_id', '?');

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    public function isUserValid(int $user_id): bool
    {
        $query = (new QueryBuilder())
            ->select(['id'])
            ->from('user')
            ->where('=', 'id', '?');

        return $query->exists([$user_id]);
    }

    public function validateUser(int $user_id): int
    {
        if (!$this->isUserValid($user_id)) {
            http_response_code(404);
            exit;
        }

        return $user_id;
    }

    public function isContactValid(int $contact_id): bool
    {
        $user_id = $_SESSION['user']['id'];
        $stmt_data = [$user_id, $user_id, $contact_id, $contact_id];
        $query = (new QueryBuilder())
            ->select(['u.id'])
            ->from('user u')
            ->join('LEFT', 'subscription s', 's.user_id = u.id AND s.author_id = ?')
            ->join('LEFT', 'message m', 'm.recipient_id = ? AND m.sender_id = ?')
            ->where('=', 'u.id', '?')
            ->groupBy('u.id')
            ->having('>', 'COUNT(s.id)', '0')
            ->orHaving('>', 'COUNT(m.id)', '0');

        return $query->exists($stmt_data);
    }

    public function insertMessage(array $stmt_data)
    {
        $message_fields = ['content', 'sender_id', 'recipient_id'];
        $query = (new QueryBuilder())
            ->insert('message', $message_fields, ['?', '?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    public function updateMessagesStatus(int $contact_id)
    {
        $stmt_data = [$contact_id, $_SESSION['user']['id']];
        $query = (new QueryBuilder())
            ->update('message', ['status' => '1'])
            ->where('=', 'sender_id', '?')
            ->andWhere('=', 'recipient_id', '?');

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    public function getMessagePreview(int $contact_id): array
    {
        $user_id = $_SESSION['user']['id'];
        $stmt_data = [$user_id, $contact_id, $contact_id, $user_id];
        $query = (new QueryBuilder())
            ->select(['dt_add', 'content', 'sender_id'])
            ->from('message')
            ->where('OR', '(recipient_id = ? AND sender_id = ?)',
                '(recipient_id = ? AND sender_id = ?)')
            ->orderBy('dt_add DESC')->limit('1');
        $message = $query->one($stmt_data);
        $preview = mb_substr($message['content'], 0, 30);
        $preview = $message['sender_id'] === $user_id ? "Вы: $preview" : $preview;

        return ['text' => $preview, 'time' => $message['dt_add']];
    }

    public function getContactMessages(int $contact_id): array
    {
        $user_id = $_SESSION['user']['id'];
        $stmt_data = [$user_id, $contact_id, $contact_id, $user_id];
        $query = (new QueryBuilder())
            ->select(['id', 'dt_add', 'content', 'status', 'sender_id', 'recipient_id'], 'm.')
            ->addSelect(['u.login AS author', 'u.avatar_path'])
            ->from('message m')
            ->join('LEFT', 'user u', 'u.id = m.sender_id')
            ->where('OR', '(m.recipient_id = ? AND m.sender_id = ?)',
                '(m.recipient_id = ? AND m.sender_id = ?)')
            ->orderBy('m.dt_add');

        return $query->all($stmt_data);
    }

    public function getContacts(): array
    {
        $stmt_data = array_fill(0, 4, $_SESSION['user']['id']);
        $query = (new QueryBuilder())
            ->select(['COUNT(DISTINCT m2.id) AS unread_messages_count'])
            ->addSelect(['u.id', 'u.login', 'u.avatar_path'])
            ->from('message m')
            ->join('LEFT', 'user u', 'u.id = m.sender_id OR u.id = m.recipient_id')
            ->join('LEFT', 'message m2', 'm2.recipient_id = ? AND m2.sender_id = u.id AND m2.status = 0')
            ->where('AND', '(m.sender_id = ? OR m.recipient_id = ?)', 'u.id != ?')
            ->groupBy('u.id')
            ->orderBy('MAX(m.dt_add) DESC');
        $contacts = $query->all($stmt_data);

        for ($i = 0; $i < count($contacts); $i++) {
            $contact_id = $contacts[$i]['id'];
            $contacts[$i]['preview'] = $this->getMessagePreview($contact_id);
            $contacts[$i]['messages'] = $this->getContactMessages($contact_id);
        }

        return $contacts;
    }

    public function addNewContact(array &$contacts, int $contact_id): bool
    {
        $stmt_data = [$_SESSION['user']['id'], $contact_id];
        $query = (new QueryBuilder())
            ->select(['u.id', 'u.login', 'u.avatar_path'])
            ->from('user u')
            ->join('LEFT', 'subscription s', 's.user_id = u.id AND s.author_id = ?')
            ->where('=', 'u.id', '?')
            ->groupBy('u.id')
            ->having('>', 'COUNT(s.id)', '0');
        $contact = $query->one($stmt_data) ?? null;

        if ($contact) {
            array_unshift($contacts, $contact);
            return setcookie('new_contact', $contact_id);
        }

        return false;
    }

    public function getItemsCount(string $content_type): int
    {
        $stmt_data = array_filter([$content_type]);
        $query = (new QueryBuilder())
            ->select(['COUNT(p.id)'])
            ->from('post p')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->filterWhere($content_type, 'ct.class_name = ?');

        return $query->one($stmt_data)['COUNT(p.id)'];
    }

    public function getPopularPosts(array $stmt_data, string $order): array
    {
        $post_fields = get_post_fields();
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['u.login AS author', 'u.avatar_path', 'ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id')
            ->andWhere('=', 'pl2.author_id', '?')
            ->filterWhere($stmt_data[1], 'ct.class_name = ?')
            ->groupBy('p.id')
            ->orderBy($order)->limit('?')->offset('?');

        return $query->all(array_filter($stmt_data, function ($val) {
            return $val !== '';
        }));
    }

    public function insertComment(array $stmt_data)
    {
        $comment_fields = ['content', 'author_id', 'post_id'];
        $query = (new QueryBuilder())
            ->insert('comment', $comment_fields, ['?', '?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    public function getPostAuthorId(int $post_id): int
    {
        $query = (new QueryBuilder())
            ->select(['author_id'])
            ->from('post')
            ->where('=', 'id', '?');

        return intval($query->one([$post_id])['author_id']);
    }

    public function updatePostShowCount(int $post_id)
    {
        $query = (new QueryBuilder())
            ->update('post', ['show_count' => 'show_count + 1'])
            ->where('=', 'id', '?');

        $this->executeQuery($query->getQuery(), [$post_id]);
    }

    public function getPostDetails(int $post_id): array
    {
        $stmt_data = [$_SESSION['user']['id'], $post_id];
        $post_fields = get_post_fields();
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT p2.id) AS repost_count',
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post p2', 'p2.origin_post_id = p.id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id AND pl2.author_id = ?')
            ->where('=', 'p.id', '?')
            ->groupBy('p.id');
        $post = $query->one($stmt_data);
        $post['display_mode'] = 'details';

        return $post;
    }

    public function getPostAuthor(int $post_id): array
    {
        $stmt_data = [$_SESSION['user']['id'], $post_id];
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT s.id) AS is_subscription',
                'COUNT(DISTINCT s2.id) AS subscriber_count',
                'COUNT(DISTINCT p2.id) AS publication_count'
            ])
            ->addSelect(['u.dt_add', 'u.login', 'u.avatar_path'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'post p2', 'p2.author_id = p.author_id')
            ->join('LEFT', 'subscription s', 's.user_id = p.author_id AND s.author_id = ?')
            ->join('LEFT', 'subscription s2', 's2.user_id = p.author_id')
            ->where('=', 'p.id', '?')
            ->groupBy('p.id');

        return $query->one($stmt_data);
    }

    public function getPostComments(int $post_id, int $limit): array
    {
        $stmt_data = array_filter([$post_id, $limit], function ($val) {
            return $val > 0;
        });
        $query = (new QueryBuilder())
            ->select(['c.id', 'c.dt_add', 'c.content', 'c.author_id', 'c.post_id'])
            ->addSelect(['u.login', 'u.avatar_path'])
            ->from('comment c')
            ->join('LEFT', 'user u', 'u.id = c.author_id')
            ->where('=', 'c.post_id', '?')
            ->orderBy('c.dt_add DESC')
            ->filterLimit($limit, '?');

        return $query->all($stmt_data);
    }

    public function getUserProfile(int $profile_id): array
    {
        $stmt_data = [$_SESSION['user']['id'], $profile_id];
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT s.id) AS is_subscription',
                'COUNT(DISTINCT s2.id) AS subscriber_count',
                'COUNT(DISTINCT p.id) AS publication_count'
            ])
            ->addSelect(['u.id', 'u.dt_add', 'u.login', 'u.avatar_path'])
            ->from('user u')
            ->join('LEFT', 'post p', 'p.author_id = u.id')
            ->join('LEFT', 'subscription s', 's.user_id = u.id AND s.author_id = ?')
            ->join('LEFT', 'subscription s2', 's2.user_id = u.id')
            ->where('=', 'u.id', '?')
            ->groupBy('u.id');

        return $query->one($stmt_data);
    }

    public function getRepost(int $post_id): array
    {
        $query = (new QueryBuilder())
            ->select(['p.dt_add', 'p.author_id', 'u.login AS author', 'u.avatar_path'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->where('=', 'p.id', '?');

        return $query->one([$post_id]);
    }

    public function getProfilePosts(int $profile_id, int $limit): array
    {
        $stmt_data = [$_SESSION['user']['id'], $profile_id];
        $post_fields = get_post_fields();
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT p2.id) AS repost_count',
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post p2', 'p2.origin_post_id = p.id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id AND pl2.author_id = ?')
            ->where('=', 'p.author_id', '?')
            ->groupBy('p.id')
            ->orderBy('p.dt_add ASC');
        $posts = $query->all($stmt_data);

        for ($i = 0; $i < count($posts); $i++) {
            $post = $posts[$i];
            $posts[$i]['hashtags'] = $this->getPostHashtags($post['id']);
            $posts[$i]['comments'] = $this->getPostComments($post['id'], $limit);

            $is_repost = $post['is_repost'] && $post_id = $post['origin_post_id'];
            $posts[$i]['origin'] = $is_repost ? $this->getRepost($post_id) : [];
        }

        return $posts;
    }

    public function getProfileLikes(int $profile_id): array
    {
        $post_fields = get_post_fields();
        $query = (new QueryBuilder())
            ->select($post_fields, 'p.')
            ->addSelect(['u.id AS user_id', 'u.login AS author', 'u.avatar_path'])
            ->addSelect(['ct.type_name', 'ct.class_name', 'pl.dt_add'])
            ->from('post p')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'user u', 'u.id = pl.author_id')
            ->where('=', 'p.author_id', '?')
            ->groupBy('p.id, pl.id, u.id')
            ->having('>', 'COUNT(pl.id)', '0')
            ->orderBy('pl.dt_add DESC');

        return $query->all([$profile_id]);
    }

    public function getProfileSubscriptions(int $profile_id): array
    {
        $stmt_data = [$_SESSION['user']['id'], $profile_id];
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT s2.id) AS is_subscription',
                'COUNT(DISTINCT s3.id) AS subscriber_count',
                'COUNT(DISTINCT p.id) AS publication_count'
            ])
            ->addSelect(['u.id', 'u.dt_add', 'u.login', 'u.avatar_path'])
            ->from('subscription s')
            ->join('LEFT', 'user u', 'u.id = s.user_id')
            ->join('LEFT', 'post p', 'p.author_id = u.id')
            ->join('LEFT', 'subscription s2', 's2.user_id = u.id AND s2.author_id = ?')
            ->join('LEFT', 'subscription s3', 's3.user_id = u.id')
            ->where('=', 's.author_id', '?')
            ->groupBy('s.id');

        return $query->all($stmt_data);
    }

    public function isEmailExist(string $email): bool {
        $query = (new QueryBuilder())
            ->select(['id'])
            ->from('user')
            ->where('=', 'email', '?');

        return $query->exists([$email]);
    }

    public function insertUser(array $stmt_data)
    {
        $user_fields = ['email', 'login', 'password', 'avatar_path'];
        $query = (new QueryBuilder())
            ->insert('user', $user_fields, ['?', '?', '?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    public function getPost(int $post_id): array
    {
        $post_fields = get_post_fields('insert');
        $query = (new QueryBuilder())
            ->select($post_fields)
            ->from('post')
            ->where('=', 'id', '?');

        return $query->one([$post_id]);
    }

    public function getPostHashtagIds(int $post_id): array
    {
        $query = (new QueryBuilder())
            ->select(['hashtag_id AS id'])
            ->from('post_hashtag')
            ->where('=', 'post_id', '?');

        return $query->all([$post_id]);
    }

    public function getPostsByHashtag(string $hashtag): array
    {
        $stmt_data = [$_SESSION['user']['id'], $hashtag];
        $post_fields = get_post_fields();
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT p2.id) AS repost_count',
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['u.login AS author', 'u.avatar_path', 'ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post p2', 'p2.origin_post_id = p.id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id AND pl2.author_id = ?')
            ->join('LEFT', 'post_hashtag ph', 'ph.post_id = p.id')
            ->join('LEFT', 'hashtag h', 'h.id = ph.hashtag_id')
            ->where('=', 'h.name', '?')
            ->groupBy('p.id')
            ->orderBy('p.dt_add DESC');
        $posts = $query->all($stmt_data);

        for ($i = 0; $i < count($posts); $i++) {
            $hashtags = $this->getPostHashtags($posts[$i]['id']);
            $posts[$i]['hashtags'] = $hashtags;
        }

        return $posts;
    }

    public function getPostsByQueryString(string $query): array
    {
        $stmt_data = [$query, $_SESSION['user']['id'], $query];
        $post_fields = get_post_fields();
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT p2.id) AS repost_count',
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect(['MATCH (p.title, p.text_content) AGAINST (?) AS score'])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['u.login AS author', 'u.avatar_path', 'ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post p2', 'p2.origin_post_id = p.id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id AND pl2.author_id = ?')
            ->where('AGAINST', 'MATCH (p.title, p.text_content)', '(? IN BOOLEAN MODE)')
            ->groupBy('p.id')
            ->orderBy('score DESC');
        $posts = $query->all($stmt_data);

        for ($i = 0; $i < count($posts); $i++) {
            $hashtags = $this->getPostHashtags($posts[$i]['id']);
            $posts[$i]['hashtags'] = $hashtags;
        }

        return $posts;
    }

    public function isSubscription(array $stmt_data): bool
    {
        $query = (new QueryBuilder())
            ->select(['id'])
            ->from('subscription')
            ->where('=', 'author_id', '?')
            ->andWhere('=', 'user_id', '?');

        return $query->exists($stmt_data);
    }

    public function insertSubscription(array $stmt_data)
    {
        $query = (new QueryBuilder())
            ->insert('subscription', ['author_id', 'user_id'], ['?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    public function deleteSubscription(array $stmt_data)
    {
        $query = (new QueryBuilder())
            ->delete('subscription')
            ->where('=', 'author_id', '?')
            ->andWhere('=', 'user_id', '?');

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    public function getSubscription(int $profile_id): array
    {
        $query = (new QueryBuilder())
            ->select(['id', 'email', 'login'])
            ->from('user')
            ->where('=', 'id', '?');

        return $query->one([$profile_id]);
    }
}
