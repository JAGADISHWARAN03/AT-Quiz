<?php
// Start session for storing messages and user data
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
    if (isset($_POST['add_question'])) {
        $quiz_category = (int)$_POST['quiz_category'];
        $question_text = $conn->real_escape_string($_POST['question_text']);
        $question_type = $conn->real_escape_string($_POST['question_type']);

        if ($question_type === 'checkbox') {
            // Handle checkbox options
            $option_1 = $conn->real_escape_string($_POST['option_1']);
            $option_2 = $conn->real_escape_string($_POST['option_2']);
            $option_3 = $conn->real_escape_string($_POST['option_3']);
            $option_4 = $conn->real_escape_string($_POST['option_4']);
            
            // Convert the array of correct options to a JSON string
            $correct_options = isset($_POST['correct_option']) ? json_encode($_POST['correct_option']) : '[]';

            $stmt = $conn->prepare("INSERT INTO questions (quiz_category, question_text, question_type, option_1, option_2, option_3, option_4, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $quiz_category, $question_text, $question_type, $option_1, $option_2, $option_3, $option_4, $correct_options);
        } elseif ($question_type === 'radio') {
            // Handle radio button options
            $option_1 = $conn->real_escape_string($_POST['option_1']);
            $option_2 = $conn->real_escape_string($_POST['option_2']);
            $option_3 = $conn->real_escape_string($_POST['option_3']);
            $option_4 = $conn->real_escape_string($_POST['option_4']);
            
            // Get the selected correct option
            $correct_option = isset($_POST['correct_option']) ? (int)$_POST['correct_option'] : 0;

            $stmt = $conn->prepare("INSERT INTO questions (quiz_category, question_text, question_type, option_1, option_2, option_3, option_4, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssii", $quiz_category, $question_text, $question_type, $option_1, $option_2, $option_3, $option_4, $correct_option);
        } elseif ($question_type === 'text') {
            // Handle text input
            $text_answer = isset($_POST['text_answer']) ? $conn->real_escape_string($_POST['text_answer']) : '';
            $stmt = $conn->prepare("INSERT INTO questions (quiz_category, question_text, question_type, correct_option) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $quiz_category, $question_text, $question_type, $text_answer);
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = "Question added successfully!";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        $stmt->close();

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

$filter_category = isset($_GET['filter_category']) ? (int)$_GET['filter_category'] : 0;

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
            background-color: #4f46e5; /* Indigo color for active tab */
            font-weight: 600; /* Bold font for active tab */
        }
        .quiz-form {
            display: <?= $show_quiz_form ? 'block' : 'none' ?>;
        }

        /* General styles */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden; /* Prevent scrolling on the entire page */
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
            top: 60px; /* Below the header */
            left: 0;
            width: 16rem; /* Fixed width for the sidebar */
            height: calc(100% - 120px); /* Full height minus header and footer */
            background-color: #1e3a8a; /* Sidebar background color */
            color: white;
            overflow-y: auto; /* Enable scrolling for the sidebar if content overflows */
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
            line-height: 60px; /* Vertically center the text */
            z-index: 1000;
        }

        /* Main content area */
        .main-content {
            margin-top: 60px; /* Below the header */
            margin-left: 16rem; /* To the right of the sidebar */
            margin-bottom: 60px; /* Above the footer */
            padding: 1rem;
            width: calc(100% - 16rem); /* Full width minus the sidebar width */
            height: calc(100% - 120px); /* Full height minus header and footer */
            overflow-y: auto; /* Enable scrolling for the main content */
            background-color: #f9fafb; /* Light background for better contrast */
            box-sizing: border-box; /* Include padding in height calculation */
            
            
            justify-content: flex-start; /* Align content to the top */
            align-items: stretch; /* Stretch content to fill the width */
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
                           class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= !isset($_GET['action']) ? 'active-nav' : '' ?>">
                            <i class="fas fa-tachometer-alt w-5 text-center"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="mt-6 mb-2 text-xs font-semibold text-indigo-300 uppercase tracking-wider">Quick Actions</li>
                    <li>
                        <a href="dashboard.php?action=create_quiz" 
                           class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= isset($_GET['action']) && $_GET['action'] === 'create_quiz' ? 'active-nav' : '' ?>">
                            <i class="fas fa-plus-circle w-5 text-center"></i>
                            <span>Create Quiz</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=results" 
                           class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= isset($_GET['action']) && $_GET['action'] === 'results' ? 'active-nav' : '' ?>">
                            <i class="fas fa-chart-bar w-5 text-center"></i>
                            <span>Results</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=categories" 
                           class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= isset($_GET['action']) && $_GET['action'] === 'categories' ? 'active-nav' : '' ?>">
                            <i class="fas fa-list w-5 text-center"></i>
                            <span>Quiz Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=mail" 
                           class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= isset($_GET['action']) && $_GET['action'] === 'mail' ? 'active-nav' : '' ?>">
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
                        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
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
                    <div class="bg-white shadow-lg p-6 rounded-lg mb-6">
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

                    <!-- Add Questions Form -->
                    <div class="bg-white shadow-lg p-6 rounded-lg">
                        <h2 class="text-2xl font-bold mb-4">Add Questions</h2>
                        <form method="POST" action="dashboard.php?action=create_quiz">
                            <div class="mb-4">
                                <label class="block font-medium mb-2">Select Quiz Category</label>
                                <select name="quiz_category" class="w-full p-2 border rounded" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block font-medium mb-2">Question Text</label>
                                <textarea name="question_text" class="w-full p-2 border rounded" rows="2" required></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="block font-medium mb-2">Question Type</label>
                                <select id="question-type" name="question_type" class="w-full p-2 border rounded" required onchange="updateInputFields()">
                                    <option value="radio">Radio Button</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="text">Input Text</option>
                                </select>
                            </div>
                            <div id="dynamic-input-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Dynamic fields will be populated here -->
                            </div>
                            <button type="submit" name="add_question" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                                Add Question
                            </button>
                        </form>
                    </div>

                    <!-- Filter Questions by Category -->
                    <div class="bg-white shadow-lg p-6 rounded-lg mb-6">
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
                    </div>

                    <!-- Questions Table -->
                    <div class="bg-white shadow-lg p-6 rounded-lg mt-6">
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
                        </div>

                        <!-- Pagination Controls -->
                        <div class="flex justify-between items-center mt-6">
                            <?php if ($current_page > 1): ?>
                                <a href="dashboard.php?action=create_quiz&filter_category=<?= $filter_category ?>&question_page=<?= $current_page - 1 ?>" 
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
                        </div>
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
                    <h2 class="text-3xl font-bold text-center mb-6">Select Quiz Category</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        <?php while ($row = $categories_result->fetch_assoc()): ?>
                            <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col justify-between">
                                <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($row['name']) ?></h3>
                                <div class="flex justify-between items-center">
                                    <a href="quiz_display.php?category_id=<?= $row['id'] ?>" 
                                       class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
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
                            <a href="dashboard.php?action=categories&page=<?= $current_page - 1 ?>" 
                               class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                Previous
                            </a>
                        <?php else: ?>
                            <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Previous</span>
                        <?php endif; ?>

                        <span class="text-gray-700">Page <?= $current_page ?> of <?= $total_pages ?></span>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="dashboard.php?action=categories&page=<?= $current_page + 1 ?>" 
                               class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                Next
                            </a>
                        <?php else: ?>
                            <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Next</span>
                        <?php endif; ?>
                    </div>
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
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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

        // Function to update input fields dynamically based on question type
        function updateInputFields() {
            const questionType = document.getElementById('question-type').value;
            const inputFieldsContainer = document.getElementById('dynamic-input-fields');
            inputFieldsContainer.innerHTML = ''; // Clear existing fields

            if (questionType === 'checkbox') {
                // Add options for checkboxes
                for (let i = 1; i <= 4; i++) {
                    const optionDiv = document.createElement('div');
                    optionDiv.innerHTML = `
                        <label class="flex items-center">
                            <input type="checkbox" name="correct_option[]" value="${i}" class="mr-2">
                            <input type="text" name="option_${i}" class="w-full p-2 border rounded" placeholder="Option ${i}" required>
                        </label>
                    `;
                    inputFieldsContainer.appendChild(optionDiv);
                }
            } else if (questionType === 'radio') {
                // Add options for radio buttons
                for (let i = 1; i <= 4; i++) {
                    const optionDiv = document.createElement('div');
                    optionDiv.innerHTML = `
                        <label class="flex items-center">
                            <input type="radio" name="correct_option" value="${i}" class="mr-2" required>
                            <input type="text" name="option_${i}" class="w-full p-2 border rounded" placeholder="Option ${i}" required>
                        </label>
                    `;
                    inputFieldsContainer.appendChild(optionDiv);
                }
            } else if (questionType === 'text') {
                // Add a single text input field
                const textInputDiv = document.createElement('div');
                textInputDiv.innerHTML = `
                    <label class="block font-medium mb-2">Answer</label>
                    <input type="text" name="text_answer" class="w-full p-2 border rounded" placeholder="Enter the answer" required>
                `;
                inputFieldsContainer.appendChild(textInputDiv);
            }
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
    </script>
 
</body>
<footer class="mt-0 bg-blue-900 text-white text-center p-4">
        &copy; 2025 Quiz Management System
    </footer>
</html>