<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

// Initialize variables
$show_quiz_form = false;
$show_results = false;
$show_categories = false;

// Handle actions
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'create_quiz') {
        $show_quiz_form = true;
    } elseif ($_GET['action'] == 'results') {
        $show_results = true;
    } elseif ($_GET['action'] == 'categories') {
        $show_categories = true;
    }
}

// Fetch categories and quizzes from the database
$categories_result = $db->query("SELECT * FROM categories");
$quizzes_result = $db->query("SELECT * FROM quizzes");

// Fetch questions if needed
$questions_result = $db->query("SELECT * FROM questions");

// Include the add question form template
include 'templates/add_question_form.php';
?>

<body class="bg-gray-50">
    <div class="flex min-h-screen bg-gray-50 min-w-[1320px]">
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
                        <a href="dashboard.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= !$show_quiz_form ? 'active-nav' : '' ?>">
                            <i class="fas fa-tachometer-alt w-5 text-center"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="mt-6 mb-2 text-xs font-semibold text-indigo-300 uppercase tracking-wider">Quick Actions</li>
                    <li>
                        <a href="dashboard.php?action=create_quiz" class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= $show_quiz_form ? 'active-nav' : '' ?>">
                            <i class="fas fa-plus-circle w-5 text-center"></i>
                            <span>Create Quiz</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=results" class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= $show_results ? 'active-nav' : '' ?>">
                            <i class="fas fa-chart-bar w-5 text-center"></i>
                            <span>Results</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?action=categories" class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-700 transition-colors <?= $show_categories ? 'active-nav' : '' ?>">
                            <i class="fas fa-list w-5 text-center"></i>
                            <span>Quiz Categories</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <div class="flex-1 overflow-x-hidden overflow-y-auto p-6">
            <div class="max-w-[80%] mx-auto">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
                        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>

                <div id="quiz-creation" class="<?= $show_quiz_form ? 'block' : 'hidden' ?>">
                    <h2 class="text-2xl font-bold mb-4">Add Questions</h2>
                    <form method="POST" action="dashboard.php?action=create_quiz">
                        <div class="mb-4">
                            <label class="block font-medium mb-2">Select Question Type</label>
                            <select name="question_type" id="question_type" class="w-full p-2 border rounded" required>
                                <option value="text">Text Input</option>
                                <option value="radio">Radio Button</option>
                                <option value="checkbox">Checkbox</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block font-medium mb-2">Question Text</label>
                            <textarea name="question_text" class="w-full p-2 border rounded" rows="2" required></textarea>
                        </div>
                        <div id="options-container" class="mb-4">
                            <label class="block font-medium mb-2">Options (comma separated)</label>
                            <input type="text" name="options" class="w-full p-2 border rounded" placeholder="Option 1, Option 2, Option 3" required>
                        </div>
                        <button type="submit" name="add_question" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                            Add Question
                        </button>
                    </form>
                </div>

                <!-- Other sections like results and categories would go here -->
            </div>
        </div>
    </div>
</body>
</html>