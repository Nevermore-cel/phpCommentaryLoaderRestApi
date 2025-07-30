<?php
// Подключаем файл с настройками базы данных
require_once 'config/database.php';

// Инициализируем переменные
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$results = []; // По умолчанию массив результатов пуст

// Проверяем, что поисковый запрос не пуст и имеет достаточную длину
if (strlen(string: $searchTerm) >= 3) {
    try {
        // Получаем соединение с базой данных
        $pdo = getDbConnection();
        
        // SQL-запрос для поиска комментариев, связанных с постами
        $sql = "SELECT DISTINCT p.id, p.title, c.body as comment_body, c.name as comment_author, c.email as comment_email
                FROM posts p
                INNER JOIN comments c ON p.id = c.post_id
                WHERE c.body LIKE :search
                ORDER BY p.id, c.id";
        
        // Подготавливаем и выполняем запрос
        $stmt = $pdo->prepare(query: $sql);
        $stmt->execute(params: ['search' => '%' . $searchTerm . '%']);
        
        // Получаем все найденные результаты
        $results = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        // Обработка ошибок подключения или запроса к БД
        error_log(message: "Database error: " . $e->getMessage());
    }
}
?>