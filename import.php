<?php
require_once 'config/database.php';

function fetchData($url): mixed {
    $ch = curl_init();
    curl_setopt(handle: $ch, option: CURLOPT_URL, value: $url);
    curl_setopt(handle: $ch, option: CURLOPT_RETURNTRANSFER, value: true);
    curl_setopt(handle: $ch, option: CURLOPT_SSL_VERIFYPEER, value: false);
    curl_setopt(handle: $ch, option: CURLOPT_TIMEOUT, value: 30);

    $response = curl_exec(handle: $ch);
    $httpCode = curl_getinfo(handle: $ch, option: CURLINFO_HTTP_CODE);
    curl_close(handle: $ch);

    if ($httpCode !== 200) {
        throw new Exception(message: "Ошибка загрузки данных с $url. HTTP код: $httpCode");
    }

    $data = json_decode(json: $response, associative: true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception(message: "Ошибка парсинга JSON: " . json_last_error_msg());
    }

    return $data;
}

function importData(): void {
    $pdo = getDbConnection();

    if (!$pdo) {
        echo "Ошибка подключения к базе данных!\n";
        exit(1);
    }

    try {
        // Начинаем транзакцию
        $pdo->beginTransaction();

        // Очищаем таблицы перед импортом
        $pdo->exec(statement: "SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec(statement: "TRUNCATE TABLE comments");
        $pdo->exec(statement: "TRUNCATE TABLE posts");
        $pdo->exec(statement: "SET FOREIGN_KEY_CHECKS = 1");

        // Загружаем записи
        echo "Загрузка записей...\n";
        $posts = fetchData(url: 'https://jsonplaceholder.typicode.com/posts');

        $stmtPost = $pdo->prepare(query: "INSERT INTO posts (id, user_id, title, body) VALUES (?, ?, ?, ?)");

        foreach ($posts as $post) {
            $stmtPost->execute(params: [
                $post['id'],
                $post['userId'],
                $post['title'],
                $post['body']
            ]);
        }

        $postsCount = count(value: $posts);
        echo "Загружено записей: $postsCount\n";

        // Загружаем комментарии
        echo "Загрузка комментариев...\n";
        $comments = fetchData(url: 'https://jsonplaceholder.typicode.com/comments');

        $stmtComment = $pdo->prepare(query: "INSERT INTO comments (id, post_id, name, email, body) VALUES (?, ?, ?, ?, ?)");

        foreach ($comments as $comment) {
            $stmtComment->execute(params: [
                $comment['id'],
                $comment['postId'],
                $comment['name'],
                $comment['email'],
                $comment['body']
            ]);
        }

        $commentsCount = count(value: $comments);
        echo "Загружено комментариев: $commentsCount\n";

        // Подтверждаем транзакцию
        $pdo->commit();

        echo "\nЗагружено $postsCount записей и $commentsCount комментариев\n";

    } catch (Exception $e) {
        if ($pdo && $pdo->inTransaction()) { //Проверка, что транзакция была начата.
            $pdo->rollBack();
        }
        echo "Транзакция уже закрыта " . $e->getMessage() . "\n";
        
        exit(1);
    }
}

// Запускаем импорт
importData();