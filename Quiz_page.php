<?php
require 'includes/config.php';

// Get category_id and token from the URL
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Validate the token and fetch the email
$stmt = $conn->prepare("SELECT email FROM quiz_links WHERE category_id = ? AND token = ?");
$stmt->bind_param("is", $category_id, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired quiz link.");
}

$user = $result->fetch_assoc();
$user_email = $user['email'];

// Fetch questions for the quiz
$stmt = $conn->prepare("SELECT id, question_text, option_1, option_2, option_3, option_4, correct_option FROM questions WHERE quiz_category = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aptitude Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <main class="max-w-[80%] mx-auto flex-col flex justify-center h-full min-h-[81.3vh] mt-1 mb-1">
        <!-- Quiz Title -->
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 text-start mt-6">Aptitude Quiz</h1>

        <div class="flex flex-col sm:flex-row justify-between items-center mt-6">
            <h2 id="question-number" class="text-red-600 font-bold text-lg text-center sm:text-left mb-4">
                Question 1 of <?php echo count($questions); ?>
            </h2>
            <div class="flex items-center bg-blue-900 text-white px-4 py-2 rounded-full shadow-lg space-x-4 mt-4 sm:mt-0">
                <svg width="25px" height="25px" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" fill="#000000">
                    <circle id="Oval-3" stroke="#000000" fill="#2F88FF" fill-rule="nonzero" cx="17" cy="24" r="17"></circle>
                    <path d="M11,1 L23,1" id="Path-13" stroke="#000000" stroke-linecap="round"></path>
                    <path d="M17,16 L17,24" id="Path-14" stroke="#FFFFFF" stroke-linecap="round"></path>
                    <path d="M25,24 L17,24" id="Path-14" stroke="#FFFFFF" stroke-linecap="round"></path>
                    <path d="M17,1 L17,5" id="Path-14" stroke="#000000" stroke-linecap="round"></path>
                </svg>
                <div class="text-xs">Quiz Time Start</div>
                <div class="text-lg font-bold flex space-x-1 bg-white text-blue-900 px-2 py-1 rounded-md">
                    <span id="minutes">00</span> <span>MIN</span> | <span id="seconds">00</span> <span>SEC</span>
                </div>
            </div>
        </div>
        <div class="bg-white shadow-lg p-6 rounded-lg border mt-4">
            <p id="question-text" class="mt-2 text-gray-700"></p>
            <div class="bg-blue-900 text-white p-2 mt-4 rounded-md text-sm">
                Select the most appropriate option from the given choices.
            </div>
            <!-- Quiz Form -->
            <form id="quiz-form" method="POST" action="submit_quiz.php">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div id="questions-container">
                    <?php foreach ($questions as $question): ?>
                        <div class="mt-4">
                            <p class="font-medium"><?= htmlspecialchars($question['question_text']) ?></p>
                            <?php foreach (['option_1', 'option_2', 'option_3', 'option_4'] as $index => $option): ?>
                                <label class="block mt-2">
                                    <input type="radio" name="answers[<?= $question['id'] ?>]" value="<?= $index + 1 ?>" class="form-radio text-blue-600">
                                    <?= htmlspecialchars($question[$option]) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md mt-6 hover:bg-green-600">Submit Quiz</button>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-10 bg-blue-900 text-white text-center p-4">
        &copy; 2025 Quiz Management System
    </footer>
</body>
</html>
