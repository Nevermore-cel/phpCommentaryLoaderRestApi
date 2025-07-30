<?php
define(constant_name: 'DB_HOST', value: 'localhost'); // Замените на ваш хост
define(constant_name: 'DB_NAME', value: 'blog_db'); // Замените на имя вашей базы данных
define(constant_name: 'DB_USER', value: 'root'); // Замените на имя пользователя вашей базы данных
define(constant_name: 'DB_PASS', value: ''); // Замените на пароль вашей базы данных
define(constant_name: 'DB_CHARSET', value: 'utf8mb4'); // Замените на кодировку вашей базы данных

/**
 * подключение к бд и создание пдо обхъекта 
 *
 * @return PDO пдо объект соединения.
 * @throws PDOException если соединение не удалось.
 */
function getDbConnection(): PDO {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO(dsn: $dsn, username: DB_USER, password: DB_PASS);
        $pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(attribute: PDO::ATTR_DEFAULT_FETCH_MODE, value: PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Ошибка подключения к БД: " . $e->getMessage());
    }
}