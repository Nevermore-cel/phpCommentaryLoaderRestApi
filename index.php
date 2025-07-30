<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск записей по комментариям (API)</title>
    <link rel="stylesheet" href="css/style.css">
  
</head>
<body>
    <div class="container">
        <h1>Поиск записей по комментариям</h1>
        
        <!-- Форма поиска -->
        <form id="searchForm" class="search-form">
            <input type="text" 
                   id="searchInput"
                   placeholder="Введите текст для поиска (минимум 3 символа)"
                   minlength="3"
                   required>
            <button type="submit">Найти</button>
        </form>
        
        <!-- Область для вывода результатов -->
        <div id="resultsContainer">
            <!-- Сюда будут динамически добавляться результаты -->
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>