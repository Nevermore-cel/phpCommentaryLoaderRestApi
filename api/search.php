<?php
// Включаем настройки БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';

// Устанавливаем заголовок ответа как JSON
header(header: 'Content-Type: application/json');

// Инициализация переменных
$searchTerm = isset($_GET['q']) ? trim(string: $_GET['q']) : '';
$results = [];
$error = null;

// Проверяем, что поисковый запрос не пуст и имеет достаточную длину
if (strlen(string: $searchTerm) >= 3) {
    try {
        $pdo = getDbConnection();
        
        // SQL-запрос для поиска комментариев, связанных с постами
        
        $sql = "SELECT p.id as post_id, p.title as post_title, c.body as comment_body, c.name as comment_author, c.email as comment_email
                FROM posts p
                INNER JOIN comments c ON p.id = c.post_id
                WHERE c.body LIKE :search
                ORDER BY p.id, c.id";
        
        $stmt = $pdo->prepare(query: $sql);
        $stmt->execute(params: ['search' => '%' . $searchTerm . '%']);
        
        $results = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        // Логируем ошибку, но возвращаем пользователю общую ошибку
        error_log(message: "API Database error: " . $e->getMessage());
        $error = "Произошла ошибка при обработке запроса.";
        http_response_code(response_code: 500); // Internal Server Error
    } catch (Exception $e) {
        // Обработка других возможных ошибок
        error_log(message: "API General error: " . $e->getMessage());
        $error = "Произошла непредвиденная ошибка.";
        http_response_code(response_code: 500);
    }
} else if (!empty($_GET)) { // Если GET-параметры были, но 'q' невалиден
    $error = "Пожалуйста, введите минимум 3 символа для поиска.";
    http_response_code(response_code: 400); // Bad Request
}

// Формируем ответ в формате JSON
if ($error) {
    echo json_encode(value: ['success' => false, 'message' => $error, 'results' => []]);
} else {
    echo json_encode(value: ['success' => true, 'searchTerm' => $searchTerm, 'results' => $results]);
}
?>