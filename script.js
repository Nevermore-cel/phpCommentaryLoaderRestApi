document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('resultsContainer');

    // Функция для отрисовки результатов
    function renderResults(data) {
        resultsContainer.innerHTML = ''; // Очищаем предыдущие результаты

        if (!data || !data.results || data.results.length === 0) {
            if (data && data.searchTerm && data.searchTerm.length >= 3) {
                resultsContainer.innerHTML = `<p class="no-results">По вашему запросу "${data.searchTerm}" ничего не найдено</p>`;
            } else if (data && data.message) { // Для ошибок вроде "минимум 3 символа"
                 resultsContainer.innerHTML = `<p class="warning">${data.message}</p>`;
            }
            return;
        }

        let html = `
            <div class="results">
                <h2>Результаты поиска для "${data.searchTerm}"</h2>
        `;
        
        let currentPostId = null;
        data.results.forEach(result => {
            // Если ID поста изменился, закрываем предыдущий div.post и открываем новый
            if (currentPostId !== result.post_id) {
                if (currentPostId !== null) {
                    html += '</div>'; // Закрываем предыдущий пост
                }
                currentPostId = result.post_id;
                html += `
                    <div class="post">
                        <h3>Пост: ${escapeHtml(result.post_title)}</h3>
                `;
            }

            // Отрисовка комментария
            html += `
                <div class="comment">
                    <div class="comment-author">
                        <strong>${escapeHtml(result.comment_author)}</strong>
                        ${result.comment_email ? `<a href="mailto:${escapeHtml(result.comment_email)}" class="comment-email-link">${escapeHtml(result.comment_email)}</a>` : ''}
                    </div>
                    <div class="comment-body">
                        ${highlightText(result.comment_body, data.searchTerm)}
                    </div>
                </div>
            `;
        });
        
        html += '</div>'; // Закрываем последний пост, если он был
        html += '</div>'; // Закрываем общий results
        resultsContainer.innerHTML = html;
    }

    // Функция для подсвечивания текста
    function highlightText(text, term) {
        if (!term) return escapeHtml(text);
        const regex = new RegExp(`(${escapeHtml(term)})`, 'gi'); // gi - глобальный, регистронезависимый
        return escapeHtml(text).replace(regex, '<mark>$1</mark>');
    }

    // Функция для экранирования HTML (защита от XSS)
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return unsafe; // Если это не строка, возвращаем как есть
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // Обработчик отправки формы
    searchForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Предотвращаем стандартную отправку формы

        const searchTerm = searchInput.value.trim();
        
        // Валидация на стороне клиента
        if (searchTerm.length < 3) {
            resultsContainer.innerHTML = `<p class="warning">Введите минимум 3 символа для поиска</p>`;
            return;
        }

        // Очистка предыдущих сообщений перед новым запросом
        resultsContainer.innerHTML = ''; 

        // Отправка запроса к API
        fetch(`api/search.php?q=${encodeURIComponent(searchTerm)}`)
            .then(response => {
                if (!response.ok) {
                    // Если ответ не успешный (например, 404, 500)
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json(); // Парсим JSON ответ
            })
            .then(data => {
                renderResults(data); // Отображаем полученные данные
            })
            .catch(error => {
                console.error('Ошибка при выполнении запроса:', error);
                resultsContainer.innerHTML = `<p class="warning">Не удалось загрузить результаты. Попробуйте позже.</p>`;
            });
    });
});