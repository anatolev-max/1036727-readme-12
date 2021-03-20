--
-- База данных: readme
--

CREATE DATABASE IF NOT EXISTS readme
	DEFAULT CHARACTER SET utf8
	DEFAULT COLLATE utf8_general_ci;

USE readme;

-- --------------------------------------------------------

--
-- Структура таблицы user
--

CREATE TABLE user (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	dt_add TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	email VARCHAR(128) NOT NULL,
	login VARCHAR(128) NOT NULL,
	password CHAR(64) NOT NULL,
	avatar_path VARCHAR(128),
	UNIQUE INDEX email(email)
);

-- --------------------------------------------------------

--
-- Структура таблицы post
--

CREATE TABLE post (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	dt_add TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	title VARCHAR(128) NOT NULL,
	text_content TEXT,
	quote_author VARCHAR(128),
	image_path VARCHAR(128),
	video_path VARCHAR(128),
	link VARCHAR(128),
	show_count INT UNSIGNED NOT NULL DEFAULT 0,
	author_id INT UNSIGNED NOT NULL,
	is_repost BOOLEAN NOT NULL DEFAULT 0,
	origin_post_id INT UNSIGNED,
	content_type_id INT UNSIGNED NOT NULL,
	FOREIGN KEY (author_id) REFERENCES user(id),
	FOREIGN KEY (origin_post_id) REFERENCES post(id),
	FOREIGN KEY (content_type_id) REFERENCES content_type(id),
	FULLTEXT INDEX post_ft_search(title, text_content)
);

-- --------------------------------------------------------

--
-- Структура таблицы comment
--

CREATE TABLE comment (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	dt_add TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	content TEXT NOT NULL,
	author_id INT UNSIGNED NOT NULL,
	post_id INT UNSIGNED NOT NULL,
	FOREIGN KEY (author_id) REFERENCES user(id),
	FOREIGN KEY (post_id) REFERENCES post(id)
);

-- --------------------------------------------------------

--
-- Структура таблицы post_like
--

CREATE TABLE post_like (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	dt_add TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	author_id INT UNSIGNED NOT NULL,
	post_id INT UNSIGNED NOT NULL,
	FOREIGN KEY (author_id) REFERENCES user(id),
	FOREIGN KEY (post_id) REFERENCES post(id),
	UNIQUE INDEX post_like(post_id, author_id)
);

-- --------------------------------------------------------

--
-- Структура таблицы subscription
--

CREATE TABLE subscription (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	author_id INT UNSIGNED NOT NULL,
	user_id INT UNSIGNED NOT NULL,
	FOREIGN KEY (author_id) REFERENCES user(id),
	FOREIGN KEY (user_id) REFERENCES user(id)
);

-- --------------------------------------------------------

--
-- Структура таблицы message
--

CREATE TABLE message (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	dt_add TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	content TEXT NOT NULL,
	status BOOLEAN NOT NULL DEFAULT 0,
	sender_id INT UNSIGNED NOT NULL,
	recipient_id INT UNSIGNED NOT NULL,
	FOREIGN KEY (sender_id) REFERENCES user(id),
	FOREIGN KEY (recipient_id) REFERENCES user(id)
);

-- --------------------------------------------------------

--
-- Структура таблицы hashtag
--

CREATE TABLE hashtag (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(128) NOT NULL,
	UNIQUE INDEX hashtag_name(name)
);

-- --------------------------------------------------------

--
-- Структура таблицы post_hashtag
--

CREATE TABLE post_hashtag (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	hashtag_id INT UNSIGNED NOT NULL,
	post_id INT UNSIGNED NOT NULL,
	FOREIGN KEY (hashtag_id) REFERENCES hashtag(id),
	FOREIGN KEY (post_id) REFERENCES post(id)
);

-- --------------------------------------------------------

--
-- Структура таблицы content_type
--

CREATE TABLE content_type (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	type_name VARCHAR(128) NOT NULL,
	class_name VARCHAR(128) NOT NULL,
	icon_width INT UNSIGNED NOT NULL,
	icon_height INT UNSIGNED NOT NULL
);

-- --------------------------------------------------------

--
-- Структура таблицы form
--

CREATE TABLE form (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(128) NOT NULL,
	modifier VARCHAR(128)
);

-- --------------------------------------------------------

--
-- Структура таблицы form_input
--

CREATE TABLE form_input (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	form_id INT UNSIGNED NOT NULL,
	input_id INT UNSIGNED NOT NULL,
	FOREIGN KEY (form_id) REFERENCES form(id),
	FOREIGN KEY (input_id) REFERENCES input(id)
);

-- --------------------------------------------------------

--
-- Структура таблицы input
--

CREATE TABLE input (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	label VARCHAR(128),
	type VARCHAR(128),
	name VARCHAR(128) NOT NULL,
	placeholder VARCHAR(128),
	required BOOLEAN
);

-- --------------------------------------------------------
