<?php

// Start session for storing messages and user data
session_start();

require 'includes/config.php'; // Include database connection
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// ADD THIS LINE to always have $action available for sidebar highlighting
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Pagination setup
$categories_per_page = 6; // Number of categories per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get the current page from the URL
$offset = ($current_page - 1) * $categories_per_page; // Calculate the offset

// Fetch total number of categories
$total_categories_query = "SELECT COUNT(*) AS total FROM quiz_categories";
$total_categories_result = $conn->query($total_categories_query);
$total_categories = $total_categories_result->fetch_assoc()['total'];

// Fetch categories for the current page
$categories_query = "SELECT * FROM quiz_categories LIMIT $categories_per_page OFFSET $offset";
$categories_result = $conn->query($categories_query);

// Calculate total pages
$total_pages = ceil($total_categories / $categories_per_page);

// Fetch total number of registered users
$total_users_query = "SELECT COUNT(*) AS total_users FROM quiz_results1 WHERE DATE(created_at) = CURDATE()";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total_users'];

// Fetch performance data for quiz categories
$category_performance_query = "
    SELECT 
        qc.name AS category_name, 
        COUNT(qr.id) AS attempts 
    FROM quiz_results qr
    JOIN quiz_categories qc ON qr.category_id = qc.id
    GROUP BY qc.id
    ORDER BY qc.name
";
$category_performance_result = $conn->query($category_performance_query);

// Prepare data for the chart
$categories = [];
$attempts = [];
while ($row = $category_performance_result->fetch_assoc()) {
    $categories[] = $row['category_name'];
    $attempts[] = $row['attempts'];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process Add Category form submission
    if (isset($_POST['add_category'])) {
        $category_name = $conn->real_escape_string($_POST['category_name']);
        $description = $conn->real_escape_string($_POST['description']);
        $timer = (int)$_POST['timer'];

        $stmt = $conn->prepare("INSERT INTO quiz_categories (name, description, timer) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $category_name, $description, $timer);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Category added successfully!";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    // Process Add Quiz form submission
    if (isset($_POST['add_quiz'])) {
        $category_id = (int)$_POST['category_id'];
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['quiz_description']);
        $timer = (int)$_POST['quiz_timer'];

        $stmt = $conn->prepare("INSERT INTO quizzes (category_id, title, description, timer) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $category_id, $title, $description, $timer);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Quiz added successfully!";
            $_SESSION['new_quiz_id'] = $conn->insert_id; // Store the new quiz ID
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    // Process Add Question form submission
    if (isset($_POST['add_all_questions'])) {
        $quiz_category = (int)$_POST['quiz_category'];
        $quiz_title = (int)$_POST['quiz_title'];
        $questions = $_POST['questions'];

        foreach ($questions as $question) {
            $question_text = $conn->real_escape_string($question['question_text']);
            $question_type = $conn->real_escape_string($question['question_type']);

            if ($question_type === 'checkbox') {
                $options = [];
                $correct_keys = [];

                foreach ($question['options'] as $key => $opt) {
                    $options[] = $conn->real_escape_string($opt['text']);
                    if (isset($opt['correct'])) {
                        $correct_keys[] = $key;
                    }
                }

                while (count($options) < 4) $options[] = '';

                $correct_json = json_encode($correct_keys);

                $stmt = $conn->prepare("INSERT INTO questions (
                    quiz_category, quiz_title_id, question_text, question_type, 
                    option_1, option_2, option_3, option_4, correct_option
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param(
                    "iisssssss",
                    $quiz_category,
                    $quiz_title,
                    $question_text,
                    $question_type,
                    $options[0],
                    $options[1],
                    $options[2],
                    $options[3],
                    $correct_json
                );
            } elseif ($question_type === 'radio') {
                $options = [];
                for ($i = 1; $i <= 4; $i++) {
                    $optKey = $i;
                    $options[] = isset($question['options'][$optKey]['text']) ? $conn->real_escape_string($question['options'][$optKey]['text']) : '';
                }

                $correct_option = isset($question['correct_option']) ? (int)$question['correct_option'] : 0;

                $stmt = $conn->prepare("INSERT INTO questions (
                    quiz_category, quiz_title_id, question_text, question_type, 
                    option_1, option_2, option_3, option_4, correct_option
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param(
                    "iissssssi",
                    $quiz_category,
                    $quiz_title,
                    $question_text,
                    $question_type,
                    $options[0],
                    $options[1],
                    $options[2],
                    $options[3],
                    $correct_option
                );
            } elseif ($question_type === 'text') {
                $text_answer = isset($question['text_answer']) ? $conn->real_escape_string($question['text_answer']) : '';

                $stmt = $conn->prepare("INSERT INTO questions (
                    quiz_category, quiz_title_id, question_text, question_type, correct_option
                ) VALUES (?, ?, ?, ?, ?)");

                $stmt->bind_param(
                    "iisss",
                    $quiz_category,
                    $quiz_title,
                    $question_text,
                    $question_type,
                    $text_answer
                );
            }

            if (!$stmt->execute()) {
                $_SESSION['message'] = "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $_SESSION['message'] = "All questions added successfully!";
        header("Location: dashboard.php?action=create_quiz");
        exit();
    }
}

if (
    isset($_POST['add_quiz']) &&
    (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest')
) {
    // Only redirect if NOT AJAX
    header("Location: dashboard.php?action=create_quiz");
    exit;
}

// Fetch data for dashboard display
$categories = $conn->query("SELECT id, name FROM quiz_categories")->fetch_all(MYSQLI_ASSOC);
$quizzes = $conn->query("SELECT q.id, q.title, c.name as category_name FROM quizzes q JOIN quiz_categories c ON q.category_id = c.id")->fetch_all(MYSQLI_ASSOC);

// Fetch quiz results grouped by category, user ID, and user email
$query = "
    SELECT 
        qr.user_id, 
        qr.user_email, 
        qc.name AS category_name, 
        SUM(qr.score) AS total_score, 
        SUM(qr.total_questions) AS total_questions,
        COUNT(qr.id) AS attempts
    FROM quiz_results qr
    JOIN quiz_categories qc ON qr.category_id = qc.id
    GROUP BY qr.user_id, qr.user_email, qr.category_id
    ORDER BY qc.name, qr.user_email
";
$result = $conn->query($query);

// Pagination for questions
$questions_per_page = 5; // Number of questions per page
$current_page = isset($_GET['question_page']) ? (int)$_GET['question_page'] : 1; // Get the current page from the URL
$offset = ($current_page - 1) * $questions_per_page; // Calculate the offset

$filter_category = isset($_GET['filter_category']) && !empty($_GET['filter_category']) ? (int)$_GET['filter_category'] : 0;

// Fetch questions for the current page
$questions_query = "SELECT question_text, question_type, option_1, option_2, option_3, option_4, correct_option FROM questions";
if ($filter_category > 0) {
    $questions_query .= " WHERE quiz_category = $filter_category";
}
$questions_query .= " LIMIT $questions_per_page OFFSET $offset";

$questions_result = $conn->query($questions_query);

// Fetch total number of questions for pagination
$total_questions_query = "SELECT COUNT(*) AS total FROM questions";
if ($filter_category > 0) {
    $total_questions_query .= " WHERE quiz_category = $filter_category";
}
$total_questions_result = $conn->query($total_questions_query);
$total_questions = $total_questions_result->fetch_assoc()['total'];

$total_question_pages = ceil($total_questions / $questions_per_page); // Calculate total pages
$quiz_count_query = "
    SELECT qc.name AS category_name, COUNT(q.id) AS quiz_count
    FROM quiz_categories qc
    LEFT JOIN quizzes q ON qc.id = q.category_id
    GROUP BY qc.id
    ORDER BY qc.name
";
$quiz_count_result = $conn->query($quiz_count_query);

$quiz_chart_categories = [];
$quiz_chart_counts = [];
while ($row = $quiz_count_result->fetch_assoc()) {
    $quiz_chart_categories[] = $row['category_name'];
    $quiz_chart_counts[] = (int)$row['quiz_count'];
}


$total_users_all_query = "SELECT COUNT(*) AS total FROM users";
$total_users_all_result = $conn->query($total_users_all_query);
$total_users_all = $total_users_all_result ? $total_users_all_result->fetch_assoc()['total'] : 0;
$conn->close();

// Determine which section to show based on URL parameters
$show_quiz_form = isset($_GET['action']) && $_GET['action'] === 'create_quiz';
$show_results = isset($_GET['action']) && $_GET['action'] === 'results';
$show_categories = isset($_GET['action']) && $_GET['action'] === 'categories';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta tags and page title -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Dashboard</title>

    <!-- External resources -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets\css\styles.css">
    <script src="assets/js/dashboard.js" defer></script>
    <!-- Inline styles for dynamic elements -->
    <style>
        /* Custom color definitions */
        :root {
            --primary-color: #2D3748;
            /* Dark slate for sidebar and accents */
            --secondary-color: #EF4444;
            /* Red for buttons and highlights */
            --gradient-start: #7F9CF5;
            /* Indigo gradient start */
            --gradient-end: #A78BFA;
            /* Purple gradient end */
        }

        /* Active navigation style */
        .active-nav {
            background-color: var(--secondary-color);
            font-weight: 600;
            border-radius: 0.5rem;
        }

        /* General styles */
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            background-color: #F7FAFC;
            /* Light gray background */
        }

        /* Header styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 64px;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        /* Sidebar styles */
        aside {
            position: fixed;
            top: 64px;
            left: 0;
            width: 260px;
            height: calc(100% - 128px);
            background: var(--primary-color);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            border-top-right-radius: 1rem;
            border-bottom-right-radius: 1rem;
        }

        /* Footer styles */
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 64px;
            background: var(--primary-color);
            color: white;
            text-align: center;
            line-height: 64px;
            z-index: 1000;
        }

        /* Main content area */
        .main-content {
            margin-top: 64px;
            margin-left: 260px;
            margin-bottom: 64px;
            padding: 2rem;
            width: calc(100% - 260px);
            height: calc(100% - 128px);
            overflow-y: auto;
            background-color: #F7FAFC;
            box-sizing: border-box;
        }

        /* Custom button styles */
        button,
        .btn {
            transition: all 0.3s ease;
            border-radius: 0.5rem;
        }

        button:hover,
        .btn:hover {
            background-color: var(--secondary-color);
        }

        /* Card hover effect */
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        /* Chart styles */
        canvas {
            border-radius: 0.5rem;
        }

        .section-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 8rem);
            /* Adjust for header/footer if present */
            padding: 2rem;
        }

        .section-content {
            max-width: 800px;
            /* Constrain width for better readability */
            width: 100%;
        }

        tr:hover {
            background: none !important;
        }
    </style>


</head>


<!-- JavaScript for chart initialization -->


<!-- JavaScript for dynamic functionality -->
<script>
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

    document.addEventListener('DOMContentLoaded', () => {
        const mailLink = document.querySelector('[href*="action=mail"]');
        const dashboardOverview = document.getElementById('dashboard-overview');
        const quizCreation = document.getElementById('quiz-creation');
        const resultsSection = document.getElementById('results-section');
        const categoriesSection = document.getElementById('categories-section');
        const mailSection = document.getElementById('mail-section');

        mailLink.addEventListener('click', (e) => {
            e.preventDefault();
            dashboardOverview.classList.add('hidden');
            quizCreation.classList.add('hidden');
            resultsSection.classList.add('hidden');
            categoriesSection.classList.add('hidden');
            mailSection.classList.remove('hidden');
        });
    });

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
        const questions_containern = document.getElementById('questions-containern');
        if (quizId) {
            fetch(`view_questions.php?quiz_id=${quizId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('questions-containern').innerHTML = data;
                    quizDisplaySection.classList.add('hidden');
                    questions_containern.classList.remove('hidden');
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

    document.querySelectorAll('[href*="action=create_quiz"]').forEach(link => {
        link.addEventListener('click', function(e) {
            document.getElementById('dashboard-overview').classList.add('hidden');
            document.getElementById('quiz-creation').classList.remove('hidden');
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const createQuizLink = document.querySelector('[href*="action=create_quiz"]');
        const resultsLink = document.querySelector('[href*="action=results"]');
        const categoriesLink = document.querySelector('[href*="action=categories"]');
        const mailLink = document.querySelector('[href*="action=mail"]');
        const dashboardOverview = document.getElementById('dashboard-overview');
        const quizCreation = document.getElementById('quiz-creation');
        const resultsSection = document.getElementById('results-section');
        const categoriesSection = document.getElementById('categories-section');
        const mailSection = document.getElementById('mail-section');

        createQuizLink.addEventListener('click', (e) => {
            e.preventDefault();
            dashboardOverview.classList.add('hidden');
            resultsSection.classList.add('hidden');
            categoriesSection.classList.add('hidden');
            mailSection.classList.add('hidden');
            quizCreation.classList.remove('hidden');
        });

        resultsLink.addEventListener('click', (e) => {
            e.preventDefault();
            dashboardOverview.classList.add('hidden');
            quizCreation.classList.add('hidden');
            categoriesSection.classList.add('hidden');
            mailSection.classList.add('hidden');
            resultsSection.classList.remove('hidden');
        });

        categoriesLink.addEventListener('click', (e) => {
            e.preventDefault();
            dashboardOverview.classList.add('hidden');
            quizCreation.classList.add('hidden');
            resultsSection.classList.add('hidden');
            mailSection.classList.add('hidden');
            categoriesSection.classList.remove('hidden');
        });

        mailLink.addEventListener('click', (e) => {
            e.preventDefault();
            dashboardOverview.classList.add('hidden');
            quizCreation.classList.add('hidden');
            resultsSection.classList.add('hidden');
            categoriesSection.classList.add('hidden');
            mailSection.classList.remove('hidden');
        });
    });

    function toggleCategory(categoryId) {
        fetch('toggle_category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `category_id=${categoryId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                } else {
                    alert('Failed to update category status.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the category status.');
            });
    }

    function fetchQuizTitles(categoryId) {
        const quizDropdown = document.getElementById('quiz_title');
        quizDropdown.innerHTML = '<option value="">Loading...</option>';
        if (categoryId) {
            fetch(`fetch_quizzes.php?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    quizDropdown.innerHTML = '<option value="">Select a quiz</option>';
                    if (data.success && Array.isArray(data.quizzes)) {
                        data.quizzes.forEach(quiz => {
                            const option = document.createElement('option');
                            option.value = quiz.id;
                            option.textContent = quiz.title;
                            quizDropdown.appendChild(option);
                        });
                    } else {
                        quizDropdown.innerHTML = '<option value="">No quizzes found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching quizzes:', error);
                    quizDropdown.innerHTML = '<option value="">Failed to load quizzes</option>';
                });
        } else {
            quizDropdown.innerHTML = '<option value="">Select a quiz</option>';
        }
    }

    function toggleQuizStatus(quizId, newStatus) {
        const onButton = document.getElementById(`on-btn-${quizId}`);
        const offButton = document.getElementById(`off-btn-${quizId}`);
        onButton.innerHTML = 'Updating...';
        offButton.innerHTML = 'Updating...';
        fetch('toggle_quiz_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `quiz_id=${quizId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (newStatus === 1) {
                        onButton.classList.remove('hidden');
                        offButton.classList.add('hidden');
                    } else {
                        onButton.classList.add('hidden');
                        offButton.classList.remove('hidden');
                    }
                    alert(data.message || 'Quiz status updated successfully.');
                } else {
                    alert(data.message || 'Failed to update quiz status.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the quiz status.');
            })
            .finally(() => {
                onButton.innerHTML = 'On';
                offButton.innerHTML = 'Off';
            });
    }

    function loadCategories(page) {
        const categoriesContainer = document.getElementById('categories-container');
        categoriesContainer.innerHTML = '<p class="text-center text-gray-500">Loading...</p>';
        fetch(`fetch_categories.php?page=${page}`)
            .then(response => response.text())
            .then(data => {
                categoriesContainer.innerHTML = data;
            })
            .catch(error => {
                console.error('Error loading categories:', error);
                categoriesContainer.innerHTML = '<p class="text-center text-red-500">Failed to load categories. Please try again.</p>';
            });
    }

    function generateQuizLink(quizId) {
        // The link should start from index.php, passing quiz_title_id as a parameter
        // The rest of the flow (user_form.php, instruction.php, Quizz_page.php, thank you page) is handled by your backend/app logic
        const link = `index.php?quiz_title_id=${quizId}`;
        const linkSpan = document.getElementById(`quiz-link-${quizId}`);
        linkSpan.innerHTML = `
            <a href="${link}" class="text-blue-500 underline hover:text-blue-700" target="_blank">
                Start Quiz
            </a>
        `;
        linkSpan.classList.remove('hidden');
    }

    function deleteQuiz(quizId) {
        if (confirm('Are you sure you want to delete this quiz?')) {
            fetch(`delete_quiz.php?id=${quizId}`, {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Quiz deleted successfully!');
                        // Remove the row from the DOM
                        const row = document.querySelector(`[data-quiz-id="${quizId}"]`);
                        if (row) row.remove();
                    } else {
                        alert('Failed to delete quiz: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error deleting quiz:', error);
                    alert('An error occurred while deleting the quiz.');
                });
        }
    }

    function openEditModal(quizId, title, description, timer) {
        document.getElementById('edit-quiz-id').value = quizId;
        document.getElementById('edit-title').value = title;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-timer').value = timer;
        document.getElementById('edit-modal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }

    function edit_form_submit(e) {
        e.preventDefault();
        const quizId = document.getElementById('edit-quiz-id').value;
        const title = document.getElementById('edit-title').value;
        const description = document.getElementById('edit-description').value;
        const timer = document.getElementById('edit-timer').value;

        fetch('update_quiz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    quiz_id: quizId,
                    title,
                    description,
                    timer
                }),
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Quiz updated successfully!');
                    const row = document.querySelector(`tr[data-id="${quizId}"]`);
                    if (row) {
                        row.querySelector('.quiz-title').textContent = title;
                        row.querySelector('.quiz-description').textContent = description;
                        row.querySelector('.quiz-timer').textContent = `${timer} minutes`;
                    }
                    closeEditModal();
                } else {
                    alert('Failed to update quiz: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error updating quiz:', error);
                alert('An error occurred while updating the quiz.');
            });
    }

    function openEditModal1(id, questionText, questionType, option1, option2, option3, option4, correctOption) {
        document.getElementById('edit-question-id').value = id;
        document.getElementById('edit-question-text').value = questionText;
        document.getElementById('edit-question-type').value = questionType;
        document.getElementById('edit-option-1').value = option1;
        document.getElementById('edit-option-2').value = option2;
        document.getElementById('edit-option-3').value = option3;
        document.getElementById('edit-option-4').value = option4;
        document.getElementById('edit-correct-option').value = correctOption;
        document.getElementById('edit-question-modal').classList.remove('hidden');
    }

    function closeEditModal1() {
        document.getElementById('edit-question-modal').classList.add('hidden');
    }

    function editQuestion(event) {
        event.preventDefault();
        const questionId = document.getElementById('edit-question-id').value;
        const questionText = document.getElementById('edit-question-text').value;
        const questionType = document.getElementById('edit-question-type').value;
        const option1 = document.getElementById('edit-option-1').value;
        const option2 = document.getElementById('edit-option-2').value;
        const option3 = document.getElementById('edit-option-3').value;
        const option4 = document.getElementById('edit-option-4').value;
        const correctOption = document.getElementById('edit-correct-option').value;

        fetch('update_question.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: questionId,
                    question_text: questionText,
                    question_type: questionType,
                    option_1: option1,
                    option_2: option2,
                    option_3: option3,
                    option_4: option4,
                    correct_option: correctOption
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Question updated successfully!');
                    const row = document.querySelector(`tr[data-id="${questionId}"]`);
                    if (row) {
                        row.querySelector('.question-text').textContent = questionText;
                        row.querySelector('.question-type').textContent = questionType;
                        row.querySelector('.option-1').textContent = option1;
                        row.querySelector('.option-2').textContent = option2;
                        row.querySelector('.option-3').textContent = option3;
                        row.querySelector('.option-4').textContent = option4;
                        if (questionType === 'checkbox') {
                            const correctOptions = JSON.parse(correctOption);
                            row.querySelector('.correct-option').textContent = correctOptions.map(opt => `Option ${opt}`).join(', ');
                        } else {
                            row.querySelector('.correct-option').textContent = `Option ${correctOption}`;
                        }
                    }
                    closeEditModal1();
                } else {
                    alert('Failed to update question: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error updating question:', error);
                alert('An error occurred while updating the question.');
            });
    }

    function deleteQuestion(id) {
        if (confirm('Are you sure you want to delete this question?')) {
            fetch(`delete_question.php?id=${id}`, {
                    method: 'GET',
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Question deleted successfully!');
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) {
                            row.remove();
                        }
                    } else {
                        alert('Failed to delete question: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error deleting question:', error);
                    alert('An error occurred while deleting the question.');
                });
        }
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


    function loadCategories1(page = 1) {
        document.getElementById('categories-modal').classList.remove('hidden');
        document.getElementById('categories-content').innerHTML = '<p class="text-center text-gray-500">Loading...</p>';
        fetch(`fetch_categories.php?page=${page}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('categories-content').innerHTML = html;
            })
            .catch(() => {
                document.getElementById('categories-content').innerHTML = '<p class="text-center text-red-500">Failed to load categories.</p>';
            });
    }





    function closeCategories1() {
        document.getElementById('categories-modal').classList.add('hidden');
    }

    function openUpdateCategoryModal(categoryId, categoryName) {
        document.getElementById('update-category-id').value = categoryId;
        document.getElementById('update-category-name').value = categoryName;
        document.getElementById('update-category-modal').classList.remove('hidden');
    }

    function closeUpdateCategoryModal() {
        document.getElementById('update-category-modal').classList.add('hidden');
    }

    function updateCategory(event) {
        event.preventDefault();
        const id = document.getElementById('update-category-id').value;
        const name = document.getElementById('update-category-name').value.trim();
        if (!name) return alert("Please enter a valid category name.");
        fetch('update_category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${id}&name=${encodeURIComponent(name)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update the name in the DOM
                    const card = document.querySelector(`[data-category-id="${id}"]`);
                    if (card) {
                        const nameElem = card.querySelector('.category-name');
                        if (nameElem) nameElem.textContent = name;
                    }
                    closeUpdateCategoryModal();
                    alert('Category updated successfully.');
                } else {
                    alert("Failed to update category: " + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("An error occurred while updating.");
            });
    }

    function deleteCategory(categoryId) {
        if (confirm('Are you sure you want to delete this category?')) {
            fetch(`delete_category.php?id=${categoryId}`, {
                    method: 'GET',
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the category card from the DOM
                        const card = document.querySelector(`[data-category-id="${categoryId}"]`);
                        if (card) card.remove();
                        alert('Category deleted successfully!');
                    } else {
                        alert('Failed to delete category: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error deleting category:', error);
                    alert('An error occurred while deleting the category.');
                });
        }
    }
</script>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const questionsContainer = document.getElementById('questions-container');
        const addNewQuestionButton = document.getElementById('add-new-question');

        function addNewQuestionForm() {
            const questionIndex = questionsContainer.children.length;
            const questionForm = document.createElement('div');
            questionForm.classList.add('mb-4', 'p-4', 'border', 'rounded-lg', 'bg-white', 'shadow-md');
            questionForm.innerHTML = `
                    <h3 class="font-semibold text-lg mb-2 text-[var(--primary-color)]">Question ${questionIndex + 1}</h3>
                    <div class="mb-4">
                        <label class="block font-medium mb-2 text-gray-700">Question Text</label>
                        <textarea name="questions[${questionIndex}][question_text]" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)]" rows="2" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium mb-2 text-gray-700">Question Type</label>
                        <select name="questions[${questionIndex}][question_type]" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] question-type" required onchange="updateInputFields(this)">
                            <option value="radio">Radio Button</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="text">Input Text</option>
                        </select>
                    </div>
                    <div class="dynamic-input-fields grid grid-cols-1 md:grid-cols-2 gap-4 mb-4"></div>
                    <button type="button" class="bg-[var(--secondary-color)] text-white px-4 py-2 rounded-lg hover:bg-red-700 remove-question">
                        Remove Question
                    </button>
                `;
            questionsContainer.appendChild(questionForm);
            questionForm.querySelector('.remove-question').addEventListener('click', () => {
                questionForm.remove();
            });
            updateInputFields(questionForm.querySelector('.question-type'));
        }

        addNewQuestionButton.addEventListener('click', addNewQuestionForm);

        window.updateInputFields = function(selectElement) {
            const questionIndex = selectElement.name.match(/\[(\d+)\]/)[1];
            const questionType = selectElement.value;
            const inputFieldsContainer = selectElement.closest('div').nextElementSibling;
            inputFieldsContainer.innerHTML = '';

            if (questionType === 'checkbox' || questionType === 'radio') {
                for (let i = 1; i <= 4; i++) {
                    const optionDiv = document.createElement('div');
                    const inputName = `questions[${questionIndex}][options][${i}][text]`;
                    const correctName = questionType === 'checkbox' ?
                        `questions[${questionIndex}][options][${i}][correct]` :
                        `questions[${questionIndex}][correct_option]`;
                    optionDiv.innerHTML = `
                            <label class="flex items-center">
                                <input type="${questionType}" name="${correctName}" value="${i}" class="mr-2" ${questionType === 'checkbox' ? '' : 'required'}>
                                <input type="text" name="${inputName}" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)]" placeholder="Option ${i}" required>
                            </label>
                        `;
                    inputFieldsContainer.appendChild(optionDiv);
                }
            } else if (questionType === 'text') {
                const textInputDiv = document.createElement('div');
                textInputDiv.innerHTML = `
                        <label class="block font-medium mb-2 text-gray-700">Answer</label>
                        <input type="text" name="questions[${questionIndex}][text_answer]" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)]" placeholder="Enter the answer" required>
                    `;
                inputFieldsContainer.appendChild(textInputDiv);
            }
        };
    });


    document.addEventListener('DOMContentLoaded', () => {
        const addCategoryBtn = document.getElementById('add-category-btn');
        const addCategoryForm = document.getElementById('add-category-form');
        const saveCategoryBtn = document.getElementById('save-category-btn');
        const newCategoryName = document.getElementById('new-category-name');

        if (addCategoryBtn) {
            addCategoryBtn.addEventListener('click', () => {
                addCategoryForm.classList.toggle('hidden');
            });
        }

        if (saveCategoryBtn) {
            saveCategoryBtn.addEventListener('click', () => {
                const categoryName = newCategoryName.value.trim();
                if (!categoryName) {
                    alert('Please enter a category name.');
                    return;
                }

                saveCategoryBtn.innerHTML = 'Saving...';
                saveCategoryBtn.disabled = true;

                fetch('add_category.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `name=${encodeURIComponent(categoryName)}`,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 1. Add to all category dropdowns
                            document.querySelectorAll('select[name="category_id"], #quiz_category').forEach(dropdown => {
                                const newOption = document.createElement('option');
                                newOption.value = data.category_id;
                                newOption.textContent = categoryName;
                                dropdown.appendChild(newOption);
                            });

                            // 2. Add to the Manage Tests grid (categories-section)
                            const grid = document.querySelector('#categories-section .grid');
                            if (grid) {
                                const cardHtml = `
                            <div class="relative bg-white border border-gray-200 rounded-xl p-5 shadow-sm card-hover" data-category-id="${data.category_id}">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-xl font-semibold text-gray-800 category-name truncate">${categoryName}</h3>
                                    <label class="custom-toggle">
                                        <input type="checkbox" class="sr-only peer"
                                            onchange="toggleCategory(${data.category_id})"
                                            checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="mb-4 flex gap-2">
                                    <a href="javascript:void(0);"
                                        class="block flex-1 text-center px-4 py-2 bg-indigo-500 text-white font-medium rounded-lg hover:bg-indigo-600 transition"
                                        onclick="loadQuizzes(${data.category_id})">
                                        View Quizzes
                                    </a>
                                </div>
                                <div class="flex justify-between items-end border-t border-gray-100 pt-3">
                                    <button
                                        class="block px-4 py-2 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 transition"
                                        style="margin-right:auto;"
                                        onclick="openAddQuestionModal(${data.category_id}, '${categoryName.replace(/'/g, "\\'")}')">
                                        Add Questions
                                    </button>
                                    <div class="flex space-x-3">
                                        <button onclick="openUpdateCategoryModal(${data.category_id}, '${categoryName.replace(/'/g, "\\'")}')"
                                            class="icon-btn text-indigo-500 hover:text-indigo-600">
                                            <i class="fas fa-edit text-lg"></i>
                                            <span class="tooltip">Update</span>
                                        </button>
                                        <button onclick="deleteCategory(${data.category_id})"
                                            class="icon-btn text-red-500 hover:text-red-600">
                                            <i class="fas fa-trash text-lg"></i>
                                            <span class="tooltip">Delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                                grid.insertAdjacentHTML('afterbegin', cardHtml);
                            }

                            // 3. Reset form and hide
                            newCategoryName.value = '';
                            addCategoryForm.classList.add('hidden');

                            // 4. Show success alert
                            alert(data.message); // This will show "Category added successfully!"
                        } else {
                            // Show the error message from the server (e.g., duplicate name or other errors)
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error adding category:', error);
                        alert('An error occurred while adding the category.');
                    })
                    .finally(() => {
                        saveCategoryBtn.innerHTML = 'Save';
                        saveCategoryBtn.disabled = false;
                    });
            });
        }
    });


    document.getElementById('quiz-create-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        fetch('dashboard.php?action=create_quiz', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(html => {
                // Try to extract the success message from the returned HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const msg = doc.querySelector('#success-message');
                if (msg) {
                    const successDiv = document.getElementById('quiz-success-message');
                    successDiv.innerHTML = msg.innerHTML;
                    successDiv.classList.remove('hidden');
                    setTimeout(() => {
                        successDiv.classList.add('hidden');
                    }, 5000);
                }
                // Optionally, clear the form fields here
                form.reset();
            })
            .catch(() => {
                alert('Failed to add quiz.');
            });
    });



    function openEditUserModal(userId, userEmail) {
        const newEmail = prompt('Update Email:', userEmail);
        if (newEmail && newEmail !== userEmail) {
            fetch('update_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${userId}&email=${encodeURIComponent(newEmail)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('User updated successfully!');
                        loadUsersTableWithActions(); // reload table
                    } else {
                        alert('Failed to update user: ' + data.message);
                    }
                });
        }
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch(`delete_user.php?id=${userId}`, {
                    method: 'GET'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('User deleted successfully!');
                        loadUsersTableWithActions(); // reload table
                    } else {
                        alert('Failed to delete user: ' + data.message);
                    }
                });
        }
    }
</script>





<body class="bg-gray-50 text-gray-900 font-sans">
    <!-- Header -->
    <header class="shadow-lg p-0 bg-gradient-to-r from-indigo-400 to-purple-400">
        <div class="max-w-7xl mx-auto flex justify-between items-center h-16 px-6">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-white tracking-wide">Arrow Thoughts</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="font-bold text-white">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
                <form method="post" action="logout.php" class="ml-2">
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main page layout with sidebar and content area -->
    <div class="flex min-h-screen">
        <!-- Sidebar navigation -->
        <aside class="w-64 text-white shadow-2xl p-6">
            <nav class="flex-1">
                <ul class="space-y-4">
                    <li>
                        <a href="dashboard.php"
                            class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-700 transition-all duration-300 <?php echo !$action ? 'active-nav' : ''; ?>"
                            id="sidebar-dashboard"
                            onclick="document.getElementById('quiz-display-section').classList.add('hidden'); document.getElementById('quiz-display-content').innerHTML = ''; document.getElementById('questions-containern').classList.add('hidden');">
                            <i class="fas fa-tachometer-alt w-6 text-center"></i>
                            <span class="text-lg">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=create_quiz"
                            class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-700 transition-all duration-300 <?php echo $action === 'create_quiz' ? 'active-nav' : ''; ?>"
                            id="sidebar-create-quiz"
                            onclick="document.getElementById('quiz-display-section').classList.add('hidden'); document.getElementById('quiz-display-content').innerHTML = ''; document.getElementById('questions-containern').classList.add('hidden');">
                            <i class="fas fa-plus-square w-6 text-center"></i>
                            <span class="text-lg">Create Quiz</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=results"
                            class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-700 transition-all duration-300 <?php echo $action === 'results' ? 'active-nav' : ''; ?>"
                            id="sidebar-results"
                            onclick="document.getElementById('quiz-display-section').classList.add('hidden'); document.getElementById('quiz-display-content').innerHTML = ''; document.getElementById('questions-containern').classList.add('hidden');">
                            <i class="fas fa-chart-bar w-6 text-center"></i>
                            <span class="text-lg">Results</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=categories"
                            class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-700 transition-all duration-300 <?php echo $action === 'categories' ? 'active-nav' : ''; ?>"
                            id="sidebar-categories"
                            onclick="document.getElementById('quiz-display-section').classList.add('hidden'); document.getElementById('quiz-display-content').innerHTML = ''; document.getElementById('questions-containern').classList.add('hidden');">
                            <i class="fas fa-tasks w-6 text-center"></i>
                            <span class="text-lg">Manage Tests</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=mail"
                            class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-700 transition-all duration-300 <?php echo $action === 'mail' ? 'active-nav' : ''; ?>"
                            id="sidebar-mail"
                            onclick="document.getElementById('quiz-display-section').classList.add('hidden'); document.getElementById('quiz-display-content').innerHTML = ''; document.getElementById('questions-containern').classList.add('hidden');">
                            <i class="fas fa-envelope w-6 text-center"></i>
                            <span class="text-lg">Mail</span>
                        </a>
                    </li>


                </ul>
            </nav>
        </aside>

        <!-- Main content area -->
        <main class="flex-1 p-6 main-content">
            <div class="max-w-[80%] mx-auto">
                <!-- Dashboard Overview Section -->
                <div id="dashboard-overview" class="<?= $show_categories || $show_quiz_form || $show_results || (isset($_GET['action']) && $_GET['action'] === 'mail') ? 'hidden' : 'block' ?>">
                    <h1 class="text-2xl font-semibold mb-6">Dynamic Campaign > Quiz</h1>



                    <!-- Stats cards      -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-xl shadow-sm card-hover"
                            onclick="loadUsersTable(1)">
                            <div class="flex items-center space-x-4">
                                <div class="bg-red-100 p-3 rounded-full">
                                    <i class="fas fa-users text-2xl text-red-500"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-600">Quiz Attempts Today</h3>
                                    <p class="text-3xl font-bold text-gray-800"><?= $total_users ?></p>

                                </div>
                            </div>
                        </div>
                        <div id="users-table-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                            <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl p-6 relative">
                                <button onclick="closeUsersTable()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">×</button>
                                <h2 class="text-2xl font-bold mb-4 text-[var(--primary-color)]"> Today Attempts </h2>
                                <div id="users-table-content"></div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm card-hover">
                            <div class="flex items-center space-x-4" onclick="loadCategories1(1)">
                                <div class="bg-blue-100 p-3 rounded-full">
                                    <i class="fas fa-list text-2xl text-blue-500"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-600">New Participants</h3>
                                    <p class="text-3xl font-bold text-gray-800"><?= $total_users ?></p>
                                </div>
                            </div>
                        </div>
                        <div id="categories-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                            <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl p-6 relative">
                                <button onclick="closeCategories1()" class="absolute top-2 right-2 text-gray-500 hover:text-[var(--secondary-color)] text-2xl">×</button>
                                <h2 class="text-2xl font-bold mb-4 text-[var(--primary-color)]"> New Participants </h2>
                                <div id="categories-content">
                                    <!-- Content will be loaded here via fetch_categories.php -->
                                </div>
                            </div>
                        </div>

                        <!-- Total Users Card -->
                        <!-- In your HTML where you want the card -->
                        <div class="bg-white p-6 rounded-xl shadow-sm card-hover" onclick="loadUsersTableWithActions(1)">
                            <div class="flex items-center space-x-4">
                                <div class="bg-yellow-100 p-3 rounded-full">
                                    <i class="fas fa-book text-2xl text-yellow-500"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-600">Total Users</h3>
                                    <p class="text-3xl font-bold text-gray-800" id="total-users-count">
                                        <?= $total_users_all ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Users Table Modal with Update/Delete/Search -->
                        <div id="users-table-modal-actions" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                            <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl p-6 relative">
                                <button onclick="closeUsersTableWithActions()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">×</button>
                                <h2 class="text-2xl font-bold mb-4 text-[var(--primary-color)]">All Users</h2>
                                <div class="mb-4 flex flex-col md:flex-row gap-2">
                                    <input type="text" id="user-search-input" class="border rounded-lg p-2 flex-1" placeholder="Search by ID or Email...">
                                    <button onclick="searchUsersTable()" class="bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600 transition">Search</button>
                                </div>
                                <div id="users-table-content-actions"></div>
                            </div>
                        </div>

                        <script>
                            // Open users table modal with actions
                            function loadUsersTableWithActions(page = 1, search = '') {
                                document.getElementById('users-table-modal-actions').classList.remove('hidden');
                                document.getElementById('users-table-content-actions').innerHTML = '<p class="text-center text-gray-500">Loading...</p>';
                                fetch(`fetch_users_table.php?page=${page}&search=${encodeURIComponent(search)}`)
                                    .then(res => res.text())
                                    .then(html => {
                                        document.getElementById('users-table-content-actions').innerHTML = html;
                                    })
                                    .catch(() => {
                                        document.getElementById('users-table-content-actions').innerHTML = '<p class="text-center text-red-500">Failed to load users.</p>';
                                    });
                            }

                            function closeUsersTableWithActions() {
                                document.getElementById('users-table-modal-actions').classList.add('hidden');
                            }

                            function searchUsersTable() {
                                const search = document.getElementById('user-search-input').value.trim();
                                loadUsersTableWithActions(1, search);
                            }
                            // Update user
                            function openEditUserModal(userId, userEmail) {
                                const newEmail = prompt('Update Email:', userEmail);
                                if (newEmail && newEmail !== userEmail) {
                                    fetch('update_user.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded'
                                            },
                                            body: `id=${userId}&email=${encodeURIComponent(newEmail)}`
                                        })
                                        .then(res => res.json())
                                        .then(data => {
                                            if (data.success) {
                                                alert('User updated successfully!');
                                                searchUsersTable();
                                            } else {
                                                alert('Failed to update user: ' + data.message);
                                            }
                                        });
                                }
                            }
                            // Delete user
                            function deleteUser(userId) {
                                if (confirm('Are you sure you want to delete this user?')) {
                                    fetch(`delete_user.php?id=${userId}`, {
                                            method: 'GET'
                                        })
                                        .then(res => res.json())
                                        .then(data => {
                                            if (data.success) {
                                                alert('User deleted successfully!');
                                                searchUsersTable();
                                            } else {
                                                alert('Failed to delete user: ' + data.message);
                                            }
                                        });
                                }
                            }
                            // Enter key triggers search
                            document.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('user-search-input').addEventListener('keydown', function(e) {
                                    if (e.key === 'Enter') searchUsersTable();
                                });
                            });
                        </script>
                    </div>
                    <!-- Quiz Stats Section -->
                    <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-semibold">Quiz Stats</h2>
                            <div class="flex gap-2">
                                <select id="quiz-category" class="border rounded-lg p-2 text-sm" onchange="loadQuizTitlesAndStats()">
                                    <option value="">All Categories</option>
                                </select>
                                <select id="quiz-title" class="border rounded-lg p-2 text-sm" onchange="loadStatsAndChart()">
                                    <option value="">All Quizzes</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div class="flex items-center space-x-4">
                                <div class="bg-blue-50 p-3 rounded-full">
                                    <i class="fas fa-users text-xl text-blue-500"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Number Of Participants</p>
                                    <p id="num-participants" class="text-2xl font-bold text-gray-800">0</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="bg-yellow-50 p-3 rounded-full">
                                    <i class="fas fa-star text-xl text-yellow-500"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Average Score</p>
                                    <p id="avg-score" class="text-2xl font-bold text-gray-800">0</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="bg-red-50 p-3 rounded-full">
                                    <i class="fas fa-clock text-xl text-red-500"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Average Time</p>
                                    <p id="avg-time" class="text-2xl font-bold text-gray-800">N/A</p>
                                </div>
                            </div>
                        </div>
                       

                        <div class="flex space-x-4 mb-4">
                            <div>
                                <label class="text-sm text-gray-600">Start Date</label>
                                <input type="date" id="start-date" class="border rounded-lg p-2 w-full">
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">End Date</label>
                                <input type="date" id="end-date" class="border rounded-lg p-2 w-full">
                            </div>
                        </div>
                        <button onclick="loadChartData()" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition mb-6">Load Data</button>
                        <div class="w-full" style="max-width: 100%;">
                            <canvas id="quizChart" width="800" height="300" style="max-width:100%;"></canvas>
                        </div>
                    </div>

                    <script>
                        let quizChart;

                        function loadChartData() {
                            const startDate = document.getElementById('start-date').value;
                            const endDate = document.getElementById('end-date').value;
                            const params = new URLSearchParams();
                            if (startDate) params.append('start_date', startDate);
                            if (endDate) params.append('end_date', endDate);

                            fetch('get_quiz_usage_stats.php?' + params.toString())
                                .then(res => res.json())
                                .then(data => {
                                    const ctx = document.getElementById('quizChart').getContext('2d');
                                    if (quizChart) quizChart.destroy();

                                    quizChart = new Chart(ctx, {
                                        type: 'bar',
                                        data: {
                                            labels: data.titles,
                                            datasets: [{
                                                label: 'Quiz Attempts',
                                                data: data.counts,
                                                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                                borderColor: 'rgba(54, 162, 235, 1)',
                                                borderWidth: 1
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false, // <-- This keeps the chart size fixed
                                            plugins: {
                                                legend: {
                                                    display: false
                                                },
                                                title: {
                                                    display: true,
                                                    text: 'Quiz Performance by Title'
                                                }
                                            },
                                            scales: {
                                                y: {
                                                    beginAtZero: true,
                                                    stepSize: 1
                                                }
                                            }
                                        }
                                    });
                                });
                        }

                        // Optionally, load chart on page load:
                        document.addEventListener('DOMContentLoaded', loadChartData);
                    </script>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Load categories on page load
                            fetch('get_categories.php')
                                .then(res => res.json())
                                .then(data => {
                                    const catSelect = document.getElementById('quiz-category');
                                    catSelect.innerHTML = '<option value="">All Categories</option>';
                                    data.forEach(cat => {
                                        const option = document.createElement('option');
                                        option.value = cat.id;
                                        option.text = cat.name;
                                        catSelect.appendChild(option);
                                    });
                                    loadQuizTitlesAndStats(); // Load quizzes for the first category (or all)
                                });
                        });

                        // When category changes, load quizzes for that category
                        function loadQuizTitlesAndStats() {
                            const categoryId = document.getElementById('quiz-category').value;
                            const quizSelect = document.getElementById('quiz-title');
                            quizSelect.innerHTML = '<option value="">All Quizzes</option>';
                            if (!categoryId) {
                                // If no category selected, don't load quizzes
                                loadStatsAndChart();
                                return;
                            }
                            fetch('get_quiz_titles.php?category_id=' + encodeURIComponent(categoryId))
                                .then(res => res.json())
                                .then(data => {
                                    data.forEach(quiz => {
                                        const option = document.createElement('option');
                                        option.value = quiz.id;
                                        option.text = quiz.title;
                                        quizSelect.appendChild(option);
                                    });
                                    loadStatsAndChart(); // Load stats for the selected category (and default quiz)
                                });
                        }

                        // When quiz changes, or after loading quizzes, load stats/chart
                        function loadStatsAndChart() {
                            const categoryId = document.getElementById('quiz-category').value;
                            const quizId = document.getElementById('quiz-title').value;
                            fetch(`get_quiz_stats.php?category_id=${categoryId}&quiz_id=${quizId}`)
                                .then(res => res.json())
                                .then(data => {
                                    document.getElementById('num-participants').innerText = data.num_participants;
                                    document.getElementById('avg-score').innerText = data.avg_score;
                                    document.getElementById('avg-time').innerText = data.avg_time;
                                    document.getElementById('quiz-name').innerText = data.quiz_name;

                                    // Chart
                                    const ctx = document.getElementById('quizChart').getContext('2d');
                                    if (window.quizChart) window.quizChart.destroy();
                                    window.quizChart = new Chart(ctx, {
                                        type: 'line',
                                        data: {
                                            labels: data.labels,
                                            datasets: [{
                                                label: 'Unique Participants',
                                                data: data.data,
                                                backgroundColor: 'rgba(52, 47, 144, 0.1)',
                                                borderColor: '#342F90',
                                                fill: true,
                                                tension: 0.3
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            scales: {
                                                y: {
                                                    beginAtZero: true,
                                                    ticks: {
                                                        precision: 0
                                                    }
                                                }
                                            }
                                        }
                                    });
                                });
                        }
                    </script>
                </div>

                <div id="quiz-creation" class="<?= $show_quiz_form ? 'block' : 'hidden' ?>">
                    <!-- Quiz Information Section -->
                    <div id="quiz-info-section" class="section-container">
                        <div class="section-content">
                            <?php if (isset($_SESSION['message']) && $show_quiz_form): ?>
                                <div id="success-message" class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
                                    <?= $_SESSION['message'];
                                    unset($_SESSION['message']); ?>
                                </div>
                                <script>
                                    setTimeout(() => {
                                        const msg = document.getElementById('success-message');
                                        if (msg) msg.style.display = 'none';
                                    }, 5000);
                                </script>
                            <?php endif; ?>

                            <div id="quiz-success-message" class="hidden bg-green-100 text-green-800 p-4 rounded-lg mb-4"></div>

                            <h2 class="text-xl font-semibold mb-4">Quiz Information</h2>
                            <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
                                <form id="quiz-create-form" method="POST">
                                    <input type="hidden" name="add_quiz" value="1">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Quiz Title -->
                                        <div class="md:col-span-2">
                                            <label for="quiz-title" class="block font-medium mb-2 text-gray-700">Quiz Title</label>
                                            <input
                                                type="text"
                                                id="quiz-title"
                                                name="title"
                                                class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                                required
                                                minlength="3"
                                                maxlength="100"
                                                aria-label="Quiz Title"
                                                placeholder="Enter quiz title">
                                        </div>

                                        <!-- Category Selection -->
                                        <div>
                                            <label for="category-id" class="block font-medium mb-2 text-gray-700">Category</label>
                                            <div class="flex items-center gap-2">
                                                <select
                                                    id="category-id"
                                                    name="category_id"
                                                    class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                                    required
                                                    aria-label="Select quiz category">
                                                    <?php foreach ($categories as $cat): ?>
                                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button
                                                    type="button"
                                                    id="add-category-btn"
                                                    class="bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600 transition"
                                                    aria-label="Add new category">
                                                    Add New Category
                                                </button>
                                            </div>
                                        </div>

                                        <!-- New Category Form -->
                                        <div id="add-category-form" class="hidden md:col-span-2">
                                            <label for="new-category-name" class="block font-medium mb-2 text-gray-700">New Category Name</label>
                                            <div class="flex items-center gap-2">
                                                <input
                                                    type="text"
                                                    id="new-category-name"
                                                    class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                                    placeholder="Enter new category name"
                                                    minlength="2"
                                                    maxlength="50"
                                                    aria-label="New category name">
                                                <button
                                                    type="button"
                                                    id="save-category-btn"
                                                    class="bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600 transition"
                                                    aria-label="Save new category">
                                                    Save
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <div class="md:col-span-2">
                                            <label for="quiz-description" class="block font-medium mb-2 text-gray-700">Description</label>
                                            <textarea
                                                id="quiz-description"
                                                name="quiz_description"
                                                class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                                rows="4"
                                                maxlength="500"
                                                aria-label="Quiz description"
                                                placeholder="Enter quiz description"></textarea>
                                        </div>

                                        <!-- Timer -->
                                        <div>
                                            <label for="quiz-timer" class="block font-medium mb-2 text-gray-700">Timer (minutes)</label>
                                            <input
                                                type="number"
                                                id="quiz-timer"
                                                name="quiz_timer"
                                                class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                                value="10"
                                                min="1"
                                                max="120"
                                                required
                                                aria-label="Quiz timer in minutes">
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="mt-6 flex gap-4">
                                        <button
                                            type="submit"
                                            class="bg-indigo-500 text-white px-6 py-2 rounded-lg hover:bg-indigo-600 transition"
                                            aria-label="Create quiz">
                                            Create Quiz
                                        </button>
                                        <button
                                            type="button"
                                            id="go-to-add-questions"
                                            class="bg-indigo-500 text-white px-6 py-2 rounded-lg hover:bg-indigo-600 transition"
                                            aria-label="Go to add questions">
                                            Add Questions
                                        </button>
                                    </div>
                                </form>
                                <div id="quiz-error-message" class="hidden bg-red-100 text-red-800 p-4 rounded-lg mt-4"></div>
                                <script>
                                    document.getElementById('quiz-create-form').addEventListener('submit', async function(e) {
                                        e.preventDefault();
                                        const form = e.target;
                                        const formData = new FormData(form);
                                        const errorDiv = document.getElementById('quiz-error-message');
                                        errorDiv.classList.add('hidden');
                                        errorDiv.innerHTML = '';
                                        try {
                                            const response = await fetch('dashboard.php?action=create_quiz', {
                                                method: 'POST',
                                                body: formData
                                            });
                                            if (!response.ok) {
                                                throw new Error('Network error. Please try again later.');
                                            }
                                            const html = await response.text();
                                            const parser = new DOMParser();
                                            const doc = parser.parseFromString(html, 'text/html');
                                            const msg = doc.querySelector('#success-message');
                                            if (msg) {
                                                const successDiv = document.getElementById('quiz-success-message');
                                                successDiv.innerHTML = msg.innerHTML;
                                                successDiv.classList.remove('hidden');
                                                setTimeout(() => {
                                                    successDiv.classList.add('hidden');
                                                }, 5000);
                                                form.reset();
                                            } else {
                                                let errorMsg = doc.querySelector('.error-message')?.textContent ||
                                                    "Something went wrong. Please check your input and try again.";
                                                errorDiv.innerHTML = errorMsg;
                                                errorDiv.classList.remove('hidden');
                                            }
                                        } catch (err) {
                                            let userMsg = "An unexpected error occurred. Please try again.";
                                            errorDiv.innerHTML = userMsg;
                                            errorDiv.classList.remove('hidden');
                                        }
                                    });

                                    document.getElementById('go-to-add-questions').addEventListener('click', function() {
                                        document.getElementById('quiz-info-section').classList.add('hidden');
                                        document.getElementById('add-questions-section').classList.remove('hidden');
                                    });
                                </script>
                            </div>
                        </div>
                    </div>

                    <!-- Add Questions Section -->
                    <div id="add-questions-section" class="section-container hidden">
                        <div class="section-content">
                            <h2 class="text-xl font-semibold mb-4">Add Questions</h2>
                            <div class="bg-white p-6 rounded-xl shadow-sm">
                                <form id="questions-form" method="POST" action="dashboard.php?action=create_quiz">
                                    <input type="hidden" name="add_all_questions" value="1">
                                    <div class="mb-4">
                                        <label class="block font-medium mb-2 text-gray-700">Select Quiz Category</label>
                                        <select id="quiz_category" name="quiz_category" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-500" required onchange="fetchQuizTitles(this.value)">
                                            <option value="">Select a category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block font-medium mb-2 text-gray-700">Select Quiz Title</label>
                                        <select id="quiz_title" name="quiz_title" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-500" required>
                                            <option value="">Select a quiz</option>
                                        </select>
                                    </div>
                                    <div id="questions-container"></div>
                                    <div class="mt-4 flex gap-4">
                                        <button type="button" id="add-new-question" class="bg-indigo-500 text-white px-6 py-2 rounded-lg hover:bg-indigo-600 transition">
                                            Add New Question
                                        </button>
                                        <button type="submit" name="add_all_questions" class="bg-indigo-500 text-white px-6 py-2 rounded-lg hover:bg-indigo-600 transition">
                                            Add All Questions
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="mt-4">
                                <button type="button" id="back-to-quiz-info" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                                    Back to Quiz Information
                                </button>
                            </div>
                            <script>
                                document.getElementById('back-to-quiz-info').addEventListener('click', function() {
                                    document.getElementById('add-questions-section').classList.add('hidden');
                                    document.getElementById('quiz-info-section').classList.remove('hidden');
                                });
                            </script>
                        </div>
                    </div>
                </div>
                <!-- Results Section -->
                <div id="results-section" class="<?= $show_results ? 'block' : 'hidden' ?>">
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <h1 class="text-2xl font-semibold text-center mb-6">Quiz Results</h1>
                        <?php include 'filter_section.php'; ?>
                        <?php include 'results_table.php'; ?>
                    </div>
                </div>
                <script>
                    document.getElementById('results-filter-form').addEventListener('submit', function(e) {
                        e.preventDefault();
                        const form = e.target;
                        const formData = new FormData(form);
                        const params = new URLSearchParams(formData).toString();

                        // Show loading indicator
                        const tableContainer = document.getElementById('results-table-container');
                        tableContainer.innerHTML = '<p class="text-center text-gray-500">Loading...</p>';

                        fetch('results_table.php?' + params)
                            .then(res => res.text())
                            .then(html => {
                                tableContainer.innerHTML = html;
                            })
                            .catch(() => {
                                tableContainer.innerHTML = '<p class="text-center text-red-500">Failed to load results.</p>';
                            });
                    });
                </script>
                <!-- Categories Section -->
                <div id="categories-section" class="<?= $show_categories ? 'block' : 'hidden' ?>">
                    <div id="categories-container">
                        <h2 class="text-2xl font-semibold text-center mb-10">Select Quiz Category</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-6 px-4">
                            <?php while ($row = $categories_result->fetch_assoc()): ?>
                                <div class="relative bg-white border border-gray-200 rounded-xl p-5 shadow-sm card-hover" data-category-id="<?= $row['id'] ?>">
                                    <!-- Category Name and Toggle -->
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-xl font-semibold text-gray-800 category-name truncate"><?= htmlspecialchars($row['name']) ?></h3>
                                        <label class="custom-toggle">
                                            <input type="checkbox" class="sr-only peer"
                                                onchange="toggleCategory(<?= $row['id'] ?>)"
                                                <?= $row['status'] ? 'checked' : '' ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>

                                    <!-- View Quizzes Button -->
                                    <div class="mb-4 flex gap-2">
                                        <a href="javascript:void(0);"
                                            class="block flex-1 text-center px-4 py-2 bg-indigo-500 text-white font-medium rounded-lg hover:bg-indigo-600 transition"
                                            onclick="loadQuizzes(<?= $row['id'] ?>)">
                                            View Quizzes
                                        </a>

                                    </div>

                                    <!-- Update and Delete Icons -->
                                    <div class="flex justify-between items-end border-t border-gray-100 pt-3">
                                        <!-- Add Questions button at the left bottom -->
                                        <button
                                            class="block px-4 py-2 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 transition"
                                            style="margin-right:auto;"
                                            onclick="openAddQuestionModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['name'])) ?>')">
                                            Add Questions
                                        </button>
                                        <!-- Update and Delete icons at the right bottom -->
                                        <div class="flex space-x-3">
                                            <button onclick="openUpdateCategoryModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>')"
                                                class="icon-btn text-indigo-500 hover:text-indigo-600">
                                                <i class="fas fa-edit text-lg"></i>
                                                <span class="tooltip">Update</span>
                                            </button>
                                            <button onclick="deleteCategory(<?= $row['id'] ?>)"
                                                class="icon-btn text-red-500 hover:text-red-600">
                                                <i class="fas fa-trash text-lg"></i>
                                                <span class="tooltip">Delete</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Update Modal -->
                                    <div id="update-category-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
                                        <div class="bg-white rounded-xl p-6 shadow-xl w-96">
                                            <h2 class="text-xl font-semibold mb-4 text-gray-800">Update Category</h2>
                                            <form onsubmit="updateCategory(event)">
                                                <input type="hidden" id="update-category-id">
                                                <div class="mb-4">
                                                    <label for="update-category-name" class="block text-gray-700 font-medium mb-1">Category Name</label>
                                                    <input type="text" id="update-category-name" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500" required>
                                                </div>
                                                <div class="flex justify-end">
                                                    <button type="button" onclick="closeUpdateCategoryModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 mr-2">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition">
                                                        Update
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <!-- Pagination Controls -->
                        <div class="flex justify-between items-center mt-6">
                            <?php if ($current_page > 1): ?>
                                <a href="javascript:void(0);"
                                    class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition"
                                    onclick="loadCategories(<?= $current_page - 1 ?>)">
                                    Previous
                                </a>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded-lg">Previous</span>
                            <?php endif; ?>
                            <span class="text-gray-700">Page <?= $current_page ?> of <?= $total_pages ?></span>
                            <?php if ($current_page < $total_pages): ?>
                                <a href="javascript:void(0);"
                                    class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition"
                                    onclick="loadCategories(<?= $current_page + 1 ?>)">
                                    Next
                                </a>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded-lg">Next</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quiz Display Section -->
                <div id="quiz-display-section" class="hidden">
                    <button onclick="goBackToCategories()" class="bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600 transition mb-4">
                        Back to Categories
                    </button>
                    <div id="quiz-display-content"></div>
                </div>

                <!-- Questions Container -->
                <div id="questions-containern" class="hidden">
                    <button onclick="goBackToQuizzes()" class="bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600 transition mb-4">
                        Back to Quizzes
                    </button>
                    <div id="questions-content"></div>
                </div>

                <!-- Mail Center Section -->
                <div id="mail-section" class="<?= isset($_GET['action']) && $_GET['action'] === 'mail' ? 'block' : 'hidden' ?>">
                    <div class="flex flex-col p-6 space-y-6">
                        <h1 class="text-2xl font-semibold">📨 Mail Center</h1>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="col-span-1 bg-white rounded-xl shadow-sm p-4 overflow-y-auto max-h-[600px]">
                                <h2 class="text-lg font-semibold mb-4 text-gray-800">Inbox</h2>
                                <?php
                                $hostname = "{imap.gmail.com:993/imap/ssl}INBOX";
                                $username = 'jagadishbit0@gmail.com';
                                $password = 'ughe ebfb ewky gqep';
                                $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to IMAP: ' . imap_last_error());
                                $emails = imap_search($inbox, 'SUBJECT "New User Registration"');
                                if ($emails) {
                                    rsort($emails);
                                    foreach ($emails as $email_number) {
                                        $overview = imap_fetch_overview($inbox, $email_number, 0);
                                        echo '<div class="border border-gray-200 rounded-lg p-3 hover:bg-indigo-50 cursor-pointer mb-2" onclick="loadEmailContent(' . $email_number . ')">';
                                        echo '<h4 class="font-medium truncate">' . htmlspecialchars($overview[0]->from) . '</h4>';
                                        echo '<p class="text-sm text-gray-500 truncate">' . htmlspecialchars($overview[0]->subject) . '</p>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<p class="text-gray-500">No emails found.</p>';
                                }
                                imap_close($inbox);
                                ?>
                            </div>
                            <div id="email-viewer" class="col-span-2 bg-white rounded-xl shadow-sm p-6 overflow-y-auto max-h-[600px]">
                                <h2 class="text-lg font-semibold text-gray-800 mb-4">Open Mail</h2>
                                <p class="text-gray-500">Click on a message to view its contents.</p>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Add Question Modal -->
                <div id="add-question-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
                    <div class="bg-white rounded-xl p-6 shadow-xl w-full max-w-4xl max-h-[90vh] relative overflow-y-auto">
                        <button onclick="closeAddQuestionModal()" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">×</button>
                        <h2 class="text-xl font-semibold mb-4 text-gray-800" id="add-question-modal-title">Add Questions</h2>
                        <form id="add-questions-form">
                            <input type="hidden" name="add_all_questions" value="1">
                            <input type="hidden" id="modal-category-id" name="quiz_category">
                            <div class="mb-4">
                                <label class="block font-medium mb-2 text-gray-700">Select Quiz Title</label>
                                <select id="modal-quiz-title" name="quiz_title" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-500" required>
                                    <option value="">Select a quiz</option>
                                </select>
                            </div>
                            <div id="modal-questions-container"></div>
                            <button type="button" id="modal-add-new-question" class="bg-indigo-500 text-white px-6 py-2 rounded-lg hover:bg-indigo-600 transition mb-4">
                                Add New Question
                            </button>
                            <button type="submit" class="bg-indigo-500 text-white px-6 py-2 rounded-lg hover:bg-indigo-600 transition">
                                Add All Questions
                            </button>
                        </form>
                        <div id="modal-question-success" class="hidden bg-green-100 text-green-800 p-4 rounded-lg mt-4"></div>
                        <div id="modal-question-error" class="hidden bg-red-100 text-red-800 p-4 rounded-lg mt-4"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        // Open modal and load quiz titles for the selected category
        function openAddQuestionModal(categoryId, categoryName) {
            document.getElementById('add-question-modal').classList.remove('hidden');
            document.getElementById('add-question-modal-title').textContent = 'Add Questions to "' + categoryName + '"';
            document.getElementById('modal-category-id').value = categoryId;
            // Fetch quiz titles for this category
            const quizTitleSelect = document.getElementById('modal-quiz-title');
            quizTitleSelect.innerHTML = '<option value="">Loading...</option>';
            fetch(`fetch_quizzes.php?category_id=${categoryId}`)
                .then(res => res.json())
                .then(data => {
                    quizTitleSelect.innerHTML = '<option value="">Select a quiz</option>';
                    if (data.success && Array.isArray(data.quizzes)) {
                        data.quizzes.forEach(quiz => {
                            const option = document.createElement('option');
                            option.value = quiz.id;
                            option.textContent = quiz.title;
                            quizTitleSelect.appendChild(option);
                        });
                    } else {
                        quizTitleSelect.innerHTML = '<option value="">No quizzes found</option>';
                    }
                })
                .catch(() => {
                    quizTitleSelect.innerHTML = '<option value="">Failed to load quizzes</option>';
                });
            // Reset questions
            document.getElementById('modal-questions-container').innerHTML = '';
            document.getElementById('modal-question-success').classList.add('hidden');
            document.getElementById('modal-question-error').classList.add('hidden');
        }

        function closeAddQuestionModal() {
            document.getElementById('add-question-modal').classList.add('hidden');
        }

        // Add new question form in modal
        document.getElementById('modal-add-new-question').addEventListener('click', function() {
            const questionsContainer = document.getElementById('modal-questions-container');
            const questionIndex = questionsContainer.children.length;
            const questionForm = document.createElement('div');
            questionForm.classList.add('mb-4', 'p-4', 'border', 'rounded-lg', 'bg-white', 'shadow-md');
            questionForm.innerHTML = `
            <h3 class="font-semibold text-lg mb-2 text-[var(--primary-color)]">Question ${questionIndex + 1}</h3>
            <div class="mb-4">
                <label class="block font-medium mb-2 text-gray-700">Question Text</label>
                <textarea name="questions[${questionIndex}][question_text]" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)]" rows="2" required></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-2 text-gray-700">Question Type</label>
                <select name="questions[${questionIndex}][question_type]" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] question-type" required onchange="updateInputFields(this)">
                    <option value="radio">Radio Button</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="text">Input Text</option>
                </select>
            </div>
            <div class="dynamic-input-fields grid grid-cols-1 md:grid-cols-2 gap-4 mb-4"></div>
            <button type="button" class="bg-[var(--secondary-color)] text-white px-4 py-2 rounded-lg hover:bg-red-700 remove-question">
                Remove Question
            </button>
        `;
            questionsContainer.appendChild(questionForm);
            questionForm.querySelector('.remove-question').addEventListener('click', () => {
                questionForm.remove();
            });
            updateInputFields(questionForm.querySelector('.question-type'));
        });

        // Handle form submit in modal
        document.getElementById('add-questions-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const successDiv = document.getElementById('modal-question-success');
            const errorDiv = document.getElementById('modal-question-error');
            successDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');
            fetch('dashboard.php?action=create_quiz', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const msg = doc.querySelector('#success-message');
                    if (msg) {
                        successDiv.innerHTML = msg.innerHTML;
                        successDiv.classList.remove('hidden');
                        setTimeout(() => {
                            successDiv.classList.add('hidden');
                            closeAddQuestionModal();
                        }, 2000);
                        form.reset();
                    } else {
                        let errorMsg = doc.querySelector('.error-message')?.textContent ||
                            "Something went wrong. Please check your input and try again.";
                        errorDiv.innerHTML = errorMsg;
                        errorDiv.classList.remove('hidden');
                    }
                })
                .catch(() => {
                    errorDiv.innerHTML = "An unexpected error occurred. Please try again.";
                    errorDiv.classList.remove('hidden');
                });
        });
    </script>

    <script>
        document.querySelectorAll('aside a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('aside a').forEach(a => a.classList.remove('active-nav'));
                this.classList.add('active-nav');
            });
        });
    </script>
    <footer class="text-white text-center p-4">
        © 2025 Quiz Management System
    </footer>
</body>

</html>