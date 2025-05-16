
function loadMailContent() {
    fetch('mail.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('dynamic-content').innerHTML = data;
        })
        .catch(error => {
            console.error('Error loading mail content:', error);
            document.getElementById('dynamic-content').innerHTML = '<p class="text-red-500">Failed to load mail content.</p>';
        });
}

function loadEmailContent(emailId) {
    fetch(`view_mail.php?email_id=${emailId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('email-viewer').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('email-viewer').innerHTML = '<p class="text-red-500">Failed to load content.</p>';
        });
}

function loadQuizzes(categoryId) {
    const quizDisplaySection = document.getElementById('quiz-display-section');
    const quizDisplayContent = document.getElementById('quiz-display-content');
    if (categoryId) {
        fetch(`quiz_display.php?category_id=${categoryId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                quizDisplayContent.innerHTML = data;
                document.getElementById('categories-section').classList.add('hidden');
                quizDisplaySection.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error loading quizzes:', error);
                quizDisplayContent.innerHTML = '<p class="text-center text-red-500">Failed to load quizzes. Please try again.</p>';
            });
    }
}

function loadQuizzes1(quizId) {
    const quizDisplaySection = document.getElementById('quiz-display-section');
    const questionsContainern = document.getElementById('questions-containern');
    if (quizId) {
        fetch(`view_questions.php?quiz_id=${quizId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('questions-containern').innerHTML = data;
                quizDisplaySection.classList.add('hidden');
                questionsContainern.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error loading quizzes:', error);
            });
    }
}

function goBackToCategories() {
    document.getElementById('categories-section').classList.remove('hidden');
    document.getElementById('quiz-display-section').classList.add('hidden');
}

function goBackToQuizzes() {
    document.getElementById('quiz-display-section').classList.remove('hidden');
    document.getElementById('questions-containern').classList.add('hidden');
}

function loadUsersTable(page = 1) {
    document.getElementById('users-table-modal').classList.remove('hidden');
    document.getElementById('users-table-content').innerHTML = '<p class="text-center text-gray-500">Loading...</p>';
    fetch(`fetch_users.php?page=${page}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('users-table-content').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('users-table-content').innerHTML = '<p class="text-center text-red-500">Failed to load users.</p>';
        });
}

function closeUsersTable() {
    document.getElementById('users-table-modal').classList.add('hidden');
}

function loadCategoriesTable(page = 1) {
    document.getElementById('categories-table-modal').classList.remove('hidden');
    document.getElementById('categories-table-content').innerHTML = '<p class="text-center text-gray-500">Loading...</p>';
    fetch(`fetch_categories_table.php?page=${page}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('categories-table-content').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('categories-table-content').innerHTML = '<p class="text-center text-red-500">Failed to load categories.</p>';
        });
}

function closeCategoriesTable() {
    document.getElementById('categories-table-modal').classList.add('hidden');
}