<?php
// Start session for storing messages and user data

use function PHPSTORM_META\type;

session_start();

require 'includes/config.php'; // Include database connection

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
$total_users_query = "SELECT COUNT(*) AS total_users FROM users";
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
    
                $stmt->bind_param("iisssssss", 
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
    
                $stmt->bind_param("iissssssi", 
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
    
                $stmt->bind_param("iisss", 
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
    <meta name="viewport" width="device-width, initial-scale=1.0">
    <title>Quiz Dashboard</title>

    <!-- External resources -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Inline styles for dynamic elements -->
    <style>
        .active-nav {
            background-color: #4f46e5;
            /* Indigo color for active tab */
            font-weight: 600;
            /* Bold font for active tab */
        }

        .quiz-form {
            display: <?= $show_quiz_form ? 'block' : 'none' ?>;
        }

        /* General styles */
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            /* Prevent scrolling on the entire page */
        }

        body {
            display: flex;
            flex-direction: column;
        }

        /* Header styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        /* Sidebar styles */
        aside {
            position: fixed;
            top: 60px;
            /* Below the header */
            left: 0;
            width: 16rem;
            /* Fixed width for the sidebar */
            height: calc(100% - 120px);
            /* Full height minus header and footer */
            background-color: #1e3a8a;
            /* Sidebar background color */
            color: white;
            overflow-y: auto;
            /* Enable scrolling for the sidebar if content overflows */
            z-index: 1000;
        }

        /* Footer styles */
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background-color: #1e3a8a;
            color: white;
            text-align: center;
            line-height: 60px;
            /* Vertically center the text */
            z-index: 1000;
        }

        /* Main content area */
        .main-content {
            margin-top: 60px;
            /* Below the header */
            margin-left: 16rem;
            /* To the right of the sidebar */
            margin-bottom: 60px;
            /* Above the footer */
            padding: 1rem;
            width: calc(100% - 16rem);
            /* Full width minus the sidebar width */
            height: calc(100% - 120px);
            /* Full height minus header and footer */
            overflow-y: auto;
            /* Enable scrolling for the main content */
            background-color: #f9fafb;
            /* Light background for better contrast */
            box-sizing: border-box;
            /* Include padding in height calculation */


            justify-content: flex-start;
            /* Align content to the top */
            align-items: stretch;
            /* Stretch content to fill the width */
        }
    </style>
    <script>
        // Function to dynamically load mail.php content
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

        // Event listener for the "Mail" link
        document.addEventListener('DOMContentLoaded', () => {
            const mailLink = document.querySelector('[href="mail.php"]');
            mailLink.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent default link behavior
                loadMailContent(); // Load mail content dynamically
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const mailLink = document.querySelector('[href*="action=mail"]');
            const dashboardOverview = document.getElementById('dashboard-overview');
            const quizCreation = document.getElementById('quiz-creation');
            const resultsSection = document.getElementById('results-section');
            const categoriesSection = document.getElementById('categories-section');
            const mailSection = document.getElementById('mail-section');

            // Event listener for "Mail" link
            mailLink.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent default link behavior

                // Hide other sections
                dashboardOverview.classList.add('hidden');
                quizCreation.classList.add('hidden');
                resultsSection.classList.add('hidden');
                categoriesSection.classList.add('hidden');

                // Show the mail section
                mailSection.classList.remove('hidden');
            });
        });

        // Function to load email content dynamically
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
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('quizChart').getContext('2d');
            const quizChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($categories) ?>, // Dynamic quiz categories
                    datasets: [{
                        label: 'Number of Attempts',
                        data: <?= json_encode($attempts) ?>, // Dynamic performance data
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 99, 132, 0.2)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</head>

<body class="bg-gray-50">
    <!-- Page header with logo and contact info -->
    <header class="p-4 bg-white shadow-md">
        <div class="flex flex-col md:flex-row  justify-between space-y-4 md:space-y-0 max-w-[90%] mx-auto">
            <div class="flex items-center space-x-4">
                <img src="assets\Arrow Thought (1) 1 (1).png" alt="Logo" class="h-10">
            </div>
            <div class="flex items-center space-x-4 text-blue-900">
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 p-2 rounded-full">
                    <svg fill="#000000" height="24px" width="24px" viewBox="0 0 473.806 473.806">
                        <!-- Phone icon SVG -->
                    </svg>
                </div>
                <div class="text-center md:text-left text-sm">
                    <span class="block font-bold">Call any time</span>
                    <span>+1 916 284 9204</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main page layout with sidebar and content area -->
    <div class="flex min-h-screen bg-gray-50 min-w-[1320px]">
        <!-- Sidebar navigation -->
        <aside class="w-64 bg-gradient-to-b from-indigo-900 to-indigo-800 text-white shadow-xl">
            <nav class="flex-1 p-4">
                <div class="mb-8 mt-4">
                    <div class="flex items-center space-x-4 p-3 rounded-lg bg-indigo-700">
                        <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Admin</p>
                            <p class="text-xs text-indigo-200">Administrator</p>
                        </div>
                    </div>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="dashboard.php"
                            class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= !isset($_GET['action']) ? 'active-nav' : '' ?>" onclick="document.getElementById('quiz-display-section').classList.add('hidden'); document.getElementById('quiz-display-content').innerHTML = ''; document.getElementById('questions-containern').classList.add('hidden');"
                            onclick="document.getElementById('quiz-display-section').classList.add('hidden'); document.getElementById('quiz-display-content').innerHTML = ''; document.getElementById('questions-containern').classList.add('hidden');">
                            <i class="fas fa-tachometer-alt w-5 text-center"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="mt-6 mb-2 text-xs font-semibold text-indigo-300 uppercase tracking-wider">Quick Actions</li>
                    <li>
                        <a href="dashboard.php?action=create_quiz"
                            class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= isset($_GET['action']) && $_GET['action'] === 'create_quiz' ? 'active-nav' : '' ?>"
                            onclick="document.getElementById('quiz-display-section').classList.add('hidden'); document.getElementById('quiz-display-content').innerHTML = ''; document.getElementById('questions-containern').classList.add('hidden');">
                            <i class="fas fa-plus-circle w-5 text-center"></i>
                            <span>Create Quiz</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=results"
                            class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= isset($_GET['action']) && $_GET['action'] === 'results' ? 'active-nav' : '' ?>"
                            onclick="document.getElementById('quiz-display-section').classList.add('hidden'); document.getElementById('quiz-display-content').innerHTML = ''; document.getElementById('questions-containern').classList.add('hidden');">
                            <i class="fas fa-chart-bar w-5 text-center"></i>
                            <span>Results</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=categories"
                            class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= isset($_GET['action']) && $_GET['action'] === 'categories' ? 'active-nav' : '' ?>"
                            onclick="document.getElementById('quiz-display-section').classList.add('hidden'); document.getElementById('quiz-display-content').innerHTML = ''; document.getElementById('questions-containern').classList.add('hidden');">
                            <i class="fas fa-list w-5 text-center"></i>
                            <span>Quiz Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=mail"
                            class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= isset($_GET['action']) && $_GET['action'] === 'mail' ? 'active-nav' : '' ?>"
                            onclick="document.getElementById('quiz-display-section').classList.add('hidden'); document.getElementById('quiz-display-content').innerHTML = ''; document.getElementById('questions-containern').classList.add('hidden');">
                            <i class="fas fa-envelope w-5 text-center"></i>
                            <span>Mail</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main content area that changes based on navigation -->
        <div class="main-content">
            <div class="max-w-[80%] mx-auto">
                <!-- Display session messages if any -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
                        <?= $_SESSION['message'];
                        unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Dashboard Overview Section -->
                <div id="dashboard-overview" class="<?= $show_categories || $show_quiz_form || $show_results || (isset($_GET['action']) && $_GET['action'] === 'mail') ? 'hidden' : 'block' ?>">
                    <h1 class="text-4xl font-bold mb-6 text-center text-indigo-700">Welcome to the Quiz Management Dashboard</h1>

                    <!-- Total Users Card -->
                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-700 text-white p-6 rounded-lg shadow mb-6">
                        <div class="flex items-center space-x-4">
                            <i class="fas fa-users text-4xl"></i>
                            <div>
                                <h3 class="font-semibold text-lg">Total Registered Users</h3>
                                <p class="text-3xl font-bold"><?= $total_users ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats cards showing totals -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                            <div class="flex items-center space-x-4">
                                <i class="fas fa-list text-4xl"></i>
                                <div>
                                    <h3 class="font-semibold text-lg">Total Categories</h3>
                                    <p class="text-3xl font-bold"><?= count($categories) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-green-500 to-green-700 text-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                            <div class="flex items-center space-x-4">
                                <i class="fas fa-book text-4xl"></i>
                                <div>
                                    <h3 class="font-semibold text-lg">Total Quizzes</h3>
                                    <p class="text-3xl font-bold"><?= count($quizzes) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Section -->
                    <div class="bg-white p-6 rounded-lg shadow mb-6">
                        <h2 class="text-2xl font-bold mb-4">Quiz Categories Performance</h2>
                        <canvas id="quizChart" class="w-full h-64"></canvas>
                    </div>
                </div>

                <!-- Quiz Creation Section (shown when Create Quiz is clicked) -->
                <div id="quiz-creation" class="<?= $show_quiz_form ? 'block' : 'hidden' ?>">
                    <!-- Quiz Category Form -->
                    <div class="bg-white shadow-lg p-6 rounded-lg mb-6 ">
                        <h2 class="text-2xl font-bold mb-4">Add Quiz Category</h2>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block font-medium">Category Name:</label>
                                <input type="text" name="category_name" class="w-full p-2 border rounded" required>
                            </div>
                            <div>
                                <label class="block font-medium">Description:</label>
                                <textarea name="description" class="w-full p-2 border rounded" required></textarea>
                            </div>
                            <div>
                                <label class="block font-medium">Timer (in minutes):</label>
                                <input type="number" name="timer" class="w-full p-2 border rounded" required>
                            </div>
                            <button type="submit" name="add_category" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Add Category
                            </button>
                        </form>
                    </div>

                    <!-- Main Quiz Creation Form -->
                    <div class="bg-white shadow-lg p-6 rounded-lg mb-6">
                        <h2 class="text-2xl font-bold mb-4">Quiz Information</h2>
                        <form method="POST">
                            <input type="hidden" name="add_quiz" value="1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-medium mb-2">Category</label>
                                    <select name="category_id" class="w-full p-2 border rounded" required>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium mb-2">Quiz Title</label>
                                    <input type="text" name="title" class="w-full p-2 border rounded" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block font-medium mb-2">Description</label>
                                    <textarea name="quiz_description" class="w-full p-2 border rounded" rows="3"></textarea>
                                </div>
                                <div>
                                    <label class="block font-medium mb-2">Timer (minutes)</label>
                                    <input type="number" name="quiz_timer" class="w-full p-2 border rounded" value="10" required>
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                                    Create Quiz
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="bg-white shadow-lg p-6 rounded-lg">
                        <h2 class="text-2xl font-bold mb-4">Add Questions</h2>
                        <?php if (isset($_SESSION['message'])): ?>
                            <div id="success-message" class="bg-green-100 text-green-800 p-4 rounded mb-4">
                                <?= $_SESSION['message'];
                                unset($_SESSION['message']); ?>
                            </div>
                            <script>
                                setTimeout(() => {
                                    document.getElementById('success-message').style.display = 'none';
                                }, 3000); // Hide after 3 seconds
                            </script>
                        <?php endif; ?>
                        <form id="questions-form" method="POST" action="dashboard.php?action=create_quiz">
                            <!-- Select Quiz Category -->
                            <div class="mb-4">
                                <label class="block font-medium mb-2">Select Quiz Category</label>
                                <select id="quiz_category" name="quiz_category" class="w-full p-2 border rounded" required onchange="fetchQuizTitles(this.value)">
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Select Quiz Title -->
                            <div class="mb-4">
                                <label class="block font-medium mb-2">Select Quiz Title</label>
                                <select id="quiz_title" name="quiz_title" class="w-full p-2 border rounded" required>
                                    <option value="">Select a quiz</option>
                                    <!-- Quiz titles will be dynamically populated here -->
                                </select>
                            </div>

                            <!-- Questions Container -->
                            <div id="questions-container">
                                <!-- Individual question forms will be dynamically added here -->
                            </div>

                            <!-- Add New Question Button -->
                            <button type="button" id="add-new-question" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 mb-4">
                                Add New Question
                            </button>

                            <!-- Submit All Questions Button -->
                            <button type="submit" name="add_all_questions" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                                Add All Questions
                            </button>
                        </form>
                    </div>

                    <script>
                     
                     document.addEventListener('DOMContentLoaded', () => {
    const questionsContainer = document.getElementById('questions-container');
    const addNewQuestionButton = document.getElementById('add-new-question');

    function addNewQuestionForm() {
        const questionIndex = questionsContainer.children.length;
        const questionForm = document.createElement('div');
        questionForm.classList.add('mb-4', 'p-4', 'border', 'rounded', 'bg-gray-100');

        questionForm.innerHTML = `
            <h3 class="font-medium mb-2">Question ${questionIndex + 1}</h3>
            <div class="mb-4">
                <label class="block font-medium mb-2">Question Text</label>
                <textarea name="questions[${questionIndex}][question_text]" class="w-full p-2 border rounded" rows="2" required></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-2">Question Type</label>
                <select name="questions[${questionIndex}][question_type]" class="w-full p-2 border rounded question-type" required onchange="updateInputFields(this)">
                    <option value="radio">Radio Button</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="text">Input Text</option>
                </select>
            </div>
            <div class="dynamic-input-fields grid grid-cols-1 md:grid-cols-2 gap-4 mb-4"></div>
            <button type="button" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 remove-question">
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

    window.updateInputFields = function (selectElement) {
        const questionIndex = selectElement.name.match(/\[(\d+)\]/)[1];
        const questionType = selectElement.value;
        const inputFieldsContainer = selectElement.closest('div').nextElementSibling;

        inputFieldsContainer.innerHTML = '';

        if (questionType === 'checkbox' || questionType === 'radio') {
            for (let i = 1; i <= 4; i++) {
                const optionDiv = document.createElement('div');
                const inputName = `questions[${questionIndex}][options][${i}][text]`;
                const correctName = questionType === 'checkbox'
                    ? `questions[${questionIndex}][options][${i}][correct]`
                    : `questions[${questionIndex}][correct_option]`;

                optionDiv.innerHTML = `
                    <label class="flex items-center">
                        <input type="${questionType}" name="${correctName}" value="${i}" class="mr-2" ${questionType === 'checkbox' ? '' : 'required'}>
                        <input type="text" name="${inputName}" class="w-full p-2 border rounded" placeholder="Option ${i}" required>
                    </label>
                `;
                inputFieldsContainer.appendChild(optionDiv);
            }
        } else if (questionType === 'text') {
            const textInputDiv = document.createElement('div');
            textInputDiv.innerHTML = `
                <label class="block font-medium mb-2">Answer</label>
                <input type="text" name="questions[${questionIndex}][text_answer]" class="w-full p-2 border rounded" placeholder="Enter the answer" required>
            `;
            inputFieldsContainer.appendChild(textInputDiv);
        }
    };
});

                    </script>
                    <!-- Filter Questions by Category -->
                    <!-- <div class="bg-white shadow-lg p-6 rounded-lg mb-6">
                        <h2 class="text-2xl font-bold mb-4">Filter Questions by Category</h2>
                        <form id="filter-form" method="GET" action="dashboard.php">
                            <input type="hidden" name="action" value="create_quiz">
                            <div class="mb-4">
                                <label class="block font-medium mb-2">Select Quiz Category</label>
                                <select id="filter-category" name="filter_category" class="w-full p-2 border rounded" required>
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= isset($_GET['filter_category']) && $_GET['filter_category'] == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                View Questions
                            </button>
                        </form>
                    </div> -->

                    <!-- Questions Table -->
                    <!-- <div class="bg-white shadow-lg p-6 rounded-lg mt-6">
                        <h2 class="text-2xl font-bold mb-4">Questions</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left p-2">Question</th>
                                        <th class="text-left p-2">Type</th>
                                        <th class="text-left p-2">Options</th>
                                        <th class="text-left p-2">Correct Option</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($questions_result->num_rows > 0): ?>
                                        <?php while ($row = $questions_result->fetch_assoc()): ?>
                                            <tr class="border-b">
                                                <td class="p-2"><?= htmlspecialchars($row['question_text']) ?></td>
                                                <td class="p-2"><?= htmlspecialchars($row['question_type']) ?></td>
                                                <td class="p-2">
                                                    1. <?= htmlspecialchars($row['option_1']) ?><br>
                                                    2. <?= htmlspecialchars($row['option_2']) ?><br>
                                                    3. <?= htmlspecialchars($row['option_3']) ?><br>
                                                    4. <?= htmlspecialchars($row['option_4']) ?>
                                                </td>
                                                <td class="p-2">
                                                    <?php if ($row['question_type'] === 'checkbox'): ?>
                                                        <?php $correct_options = json_decode($row['correct_option'], true); ?>
                                                        <?php foreach ($correct_options as $option): ?>
                                                            Option <?= htmlspecialchars($option) ?><br>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($row['correct_option']) ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center p-4">No questions found for the selected category.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div> -->

                    <!-- Pagination Controls -->
                    <!-- <div class="flex justify-between items-center mt-6">
                            <?php if ($current_page > 1): ?>
                                <a href="dashboard.php?action=create_quiz<?= $filter_category ? '&filter_category=' . $filter_category : '' ?>&question_page=<?= $current_page - 1 ?>" 
                                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                    Previous
                                </a>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Previous</span>
                            <?php endif; ?>

                            <span class="text-gray-700">Page <?= $current_page ?> of <?= $total_question_pages ?></span>

                            <?php if ($current_page < $total_question_pages): ?>
                                <a href="dashboard.php?action=create_quiz&filter_category=<?= $filter_category ?>&question_page=<?= $current_page + 1 ?>" 
                                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                    Next
                                </a>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Next</span>
                            <?php endif; ?>
                        </div> -->
                </div>
            </div>

            <!-- Results Section (shown when Results is clicked) -->
            <div id="results-section" class="<?= $show_results ? 'block' : 'hidden' ?>">


                <!-- Results table showing user performance -->
                <div class="bg-white shadow-lg p-6 rounded-lg">
                    <h1 class="text-3xl font-bold text-center mb-6">Quiz Results</h1>

                    <!-- Filter Section -->
                    <?php include 'filter_section.php'; ?>

                    <!-- Results Table -->
                    <?php include 'results_table.php'; ?>
                </div>
            </div>

            <!-- Categories Section (shown when Quiz Categories is clicked) -->
            <div id="categories-section" class="<?= $show_categories ? 'block' : 'hidden' ?>">
                <div id="categories-container">
                    <h2 class="text-3xl font-bold text-center mb-6">Select Quiz Category</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        <?php while ($row = $categories_result->fetch_assoc()): ?>
                            <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col justify-between">
                                <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($row['name']) ?></h3>
                                <div class="flex justify-between items-center">
                                    <a href="javascript:void(0);"
                                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                        onclick="loadQuizzes(<?= $row['id'] ?>)">
                                        View Quizzes
                                    </a>

                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer"
                                            onchange="toggleCategory(<?= $row['id'] ?>)"
                                            <?= $row['status'] ? 'checked' : '' ?>>
                                        <div class="w-11 h-6 bg-gray-300 peer-focus:ring-4 rounded-full 
                                                        peer-checked:bg-green-500 peer-checked:ring-green-300 
                                                        peer:bg-red-500 peer:ring-red-300"></div>
                                    </label>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="flex justify-between items-center mt-6">
                        <?php if ($current_page > 1): ?>
                            <a href="javascript:void(0);"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
                                onclick="loadCategories(<?= $current_page - 1 ?>)">
                                Previous
                            </a>
                        <?php else: ?>
                            <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Previous</span>
                        <?php endif; ?>

                        <span class="text-gray-700">Page <?= $current_page ?> of <?= $total_pages ?></span>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="javascript:void(0);"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
                                onclick="loadCategories(<?= $current_page + 1 ?>)">
                                Next
                            </a>
                        <?php else: ?>
                            <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Next</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div id="quiz-display-section" class="hidden">
                <button onclick="goBackToCategories()" class="bg-gray-500 text-white px-4 py-2 rounded mb-4">
                    Back to Categories
                </button>
                <div id="quiz-display-content"></div>
            </div>
            <div id="questions-containern" class="hidden">

            </div>
            <!-- Mail Center Section (shown when Mail is clicked) -->
            <div id="mail-section" class="<?= isset($_GET['action']) && $_GET['action'] === 'mail' ? 'block' : 'hidden' ?>">
                <div class="flex flex-col p-6 md:p-10 space-y-6">
                    <h1 class="text-3xl font-bold text-gray-700">📨 Mail Center</h1>

                    <!-- Panels -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Mail List -->
                        <div class="col-span-1 bg-white rounded-lg shadow p-4 overflow-y-auto max-h-[600px]">
                            <h2 class="text-lg font-semibold mb-4 text-indigo-600">Inbox</h2>
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

                        <!-- Email Viewer -->
                        <div id="email-viewer" class="col-span-2 bg-white rounded-lg shadow p-6 overflow-y-auto max-h-[600px]">
                            <h2 class="text-lg font-semibold text-indigo-600 mb-4">Open Mail</h2>
                            <p class="text-gray-500">Click on a message to view its contents.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- JavaScript for dynamic functionality -->
    <script>
        function loadQuizzes(categoryId) {
            const quizDisplaySection = document.getElementById('quiz-display-section');
            const quizDisplayContent = document.getElementById('quiz-display-content');
            // quizDisplayContent.innerHTML = '<p class="text-center text-gray-500">Loading quizzes...</p>'; // Show loading message

            if (categoryId) {
                fetch(`quiz_display.php?category_id=${categoryId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(data => {
                        quizDisplayContent.innerHTML = data; // Insert the fetched content
                        document.getElementById('categories-section').classList.add('hidden'); // Hide categories section
                        quizDisplaySection.classList.remove('hidden'); // Show quiz display section
                    })
                    .catch(error => {
                        console.error('Error loading quizzes:', error);
                        quizDisplayContent.innerHTML = '<p class="text-center text-red-500">Failed to load quizzes. Please try again.</p>';
                    });
            } else {
                // quizDisplayContent.innerHTML = '<p class="text-center text-red-500">Invalid category selected.</p>';
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
                        // document.getElementById('data').classList.add('hidden'); // Hide categories section
                        quizDisplaySection.classList.add('hidden');
                        questions_containern.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error loading quizzes:', error);
                    });
            } else {
                // quizDisplayContent.innerHTML = '<p class="text-center text-red-500">Invalid category selected.</p>';
            }

        }

        function goBackToCategories() {
            document.getElementById('categories-section').classList.remove('hidden'); // Show categories section
            document.getElementById('quiz-display-section').classList.add('hidden'); // Hide quiz display section
        }

        // Toggle between dashboard and quiz creation
        document.querySelectorAll('[href*="action=create_quiz"]').forEach(link => {
            link.addEventListener('click', function(e) {
                document.getElementById('dashboard-overview').classList.add('hidden');
                document.getElementById('quiz-creation').classList.remove('hidden');
            });
        });

        // Handle navigation between sections
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

            // Event listener for "Create Quiz" link
            createQuizLink.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent default link behavior

                // Hide other sections
                dashboardOverview.classList.add('hidden');
                resultsSection.classList.add('hidden');
                categoriesSection.classList.add('hidden');
                mailSection.classList.add('hidden');

                // Show the quiz creation section
                quizCreation.classList.remove('hidden');
            });

            // Event listener for "Results" link
            resultsLink.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent default link behavior

                // Hide other sections
                dashboardOverview.classList.add('hidden');
                quizCreation.classList.add('hidden');
                categoriesSection.classList.add('hidden');
                mailSection.classList.add('hidden');

                // Show the results section
                resultsSection.classList.remove('hidden');
            });

            // Event listener for "Quiz Categories" link
            categoriesLink.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent default link behavior

                // Hide other sections
                dashboardOverview.classList.add('hidden');
                quizCreation.classList.add('hidden');
                resultsSection.classList.add('hidden');
                mailSection.classList.add('hidden');

                // Show the categories section
                categoriesSection.classList.remove('hidden');
            });

            // Event listener for "Mail" link
            mailLink.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent default link behavior

                // Hide other sections
                dashboardOverview.classList.add('hidden');
                quizCreation.classList.add('hidden');
                resultsSection.classList.add('hidden');
                categoriesSection.classList.add('hidden');

                // Show the mail section
                mailSection.classList.remove('hidden');
            });
        });

        // Function to toggle the status of a quiz category via AJAX
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

       
        // Initialize the input fields on page load
        document.addEventListener('DOMContentLoaded', updateInputFields);

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

        function replyToEmail(emailId) {
            fetch(`reply_mail.php?email_id=${emailId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('email-viewer').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('email-viewer').innerHTML = '<p class="text-red-500">Failed to load reply form.</p>';
                });
        }

        function fetchQuizTitles(categoryId) {
            const quizDropdown = document.getElementById('quiz_title');
            quizDropdown.innerHTML = '<option value="">Loading...</option>'; // Show loading message

            if (categoryId) {
                fetch(`fetch_quizzes.php?category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        quizDropdown.innerHTML = '<option value="">Select a quiz</option>'; // Reset dropdown
                        if (data.success) {
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
                quizDropdown.innerHTML = '<option value="">Select a quiz</option>'; // Reset if no category is selected
            }
        }

        function toggleQuizStatus(quizId, newStatus) {
            const onButton = document.getElementById(`on-btn-${quizId}`);
            const offButton = document.getElementById(`off-btn-${quizId}`);

            // Show a loading state
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
                        // Update the button visibility based on the new status
                        if (newStatus === 1) {
                            onButton.classList.remove('hidden');
                            offButton.classList.add('hidden');
                        } else {
                            onButton.classList.add('hidden');
                            offButton.classList.remove('hidden');
                        }
                        // Display success message
                        alert(data.message || 'Quiz status updated successfully.');
                    } else {
                        // Display failure message
                        alert(data.message || 'Failed to update quiz status.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the quiz status.');
                })
                .finally(() => {
                    // Reset the button text
                    onButton.innerHTML = 'On';
                    offButton.innerHTML = 'Off';
                });
        }

        function loadCategories(page) {
            const categoriesContainer = document.getElementById('categories-container');
            categoriesContainer.innerHTML = '<p class="text-center text-gray-500">Loading...</p>'; // Show loading message

            fetch(`fetch_categories.php?page=${page}`)
                .then(response => response.text())
                .then(data => {
                    categoriesContainer.innerHTML = data; // Update the categories container with the fetched HTML
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                    categoriesContainer.innerHTML = '<p class="text-center text-red-500">Failed to load categories. Please try again.</p>';
                });
        }

        function generateQuizLink(quizId) {
            // Generate the link dynamically
            const link = `user_form.php?quiz_title_id=${quizId}`;

            // Update the corresponding span with the generated link
            const linkSpan = document.getElementById(`quiz-link-${quizId}`);
            linkSpan.innerHTML = `
            <a href="${link}" 
               class="text-blue-500 underline hover:text-blue-700" 
               target="_blank">
               View Quiz
            </a>
        `;
            linkSpan.classList.remove('hidden'); // Make the link visible
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
                            location.reload(); // Reload the page to reflect changes
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
            console.log(quizId, title, description, timer);

            document.getElementById('edit-quiz-id').value = quizId;
            document.getElementById('edit-title').value = title;
            document.getElementById('edit-description').value = description;
            document.getElementById('edit-timer').value = timer;
            document.getElementById('edit-modal').classList.remove('hidden');
        }

        // Close the edit modal
        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }

        // Handle form submission
        function edit_form_submit(e) {
            e.preventDefault(); // Prevent the default form submission

            const quizId = document.getElementById('edit-quiz-id').value;
            const title = document.getElementById('edit-title').value;
            const description = document.getElementById('edit-description').value;
            const timer = document.getElementById('edit-timer').value;

            // Send the updated data to the server using fetch
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

                        // Dynamically update the table row without reloading the page
                        const row = document.querySelector(`tr[data-id="${quizId}"]`);
                        if (row) {
                            row.querySelector('.quiz-title').textContent = title;
                            row.querySelector('.quiz-description').textContent = description;
                            row.querySelector('.quiz-timer').textContent = `${timer} minutes`;
                        }

                        // Close the modal
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

        // Open the question edit modal
        function openEditModal1(id, questionText, questionType, option1, option2, option3, option4, correctOption) {
            console.log(id, questionText, questionType, option1, option2, option3, option4, correctOption);
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

        // Close the question edit modal
        function closeEditModal1() {
            document.getElementById('edit-question-modal').classList.add('hidden');
        }

        // Submit the question edit form
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

    console.log({
        id: questionId,
        question_text: questionText,
        question_type: questionType,
        option_1: option1,
        option_2: option2,
        option_3: option3,
        option_4: option4,
        correct_option: correctOption
    });

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

                // Update the question row dynamically in the DOM
                const row = document.querySelector(`tr[data-id="${questionId}"]`);
                if (row) {
                    row.querySelector('.question-text').textContent = questionText;
                    row.querySelector('.question-type').textContent = questionType;
                    row.querySelector('.option-1').textContent = option1;
                    row.querySelector('.option-2').textContent = option2;
                    row.querySelector('.option-3').textContent = option3;
                    row.querySelector('.option-4').textContent = option4;
                   

                    // Update correct option dynamically
                    if (questionType === 'checkbox') {
                        const correctOptions = JSON.parse(correctOption);
                        row.querySelector('.correct-option').textContent = correctOptions.map(opt => `Option ${opt}`).join(', ');
                    } else {
                        row.querySelector('.correct-option').textContent = `Option ${correctOption}`;
                    }
                }

                // Close the modal
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

                            // Remove the question row dynamically from the DOM
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
     
        
    </script>


    <!-- Container for dynamically loaded quizzes -->

</body>
<footer class="mt-0 bg-blue-900 text-white text-center p-4">
    &copy; 2025 Quiz Management System
</footer>

</html>