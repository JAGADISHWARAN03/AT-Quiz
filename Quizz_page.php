<?php
require 'includes/config.php';

session_start();

// Check if quiz_title_id is provided in the URL
if (!isset($_GET['quiz_title_id']) || !isset($_SESSION['user_name'])) {
    header("Location: user_form.php?quiz_title_id=" . $_GET['quiz_title_id']);
    exit;
}

$quiz_title_id = (int)$_GET['quiz_title_id'];

// Fetch questions for the quiz based on quiz_title_id
$stmt = $conn->prepare("SELECT id, question_text, option_1, option_2, option_3, option_4 FROM questions WHERE quiz_title_id = ?");
$stmt->bind_param("i", $quiz_title_id);
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
    <title>Quiz Questions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white">
    <!-- Header -->
    

    <script>
        let currentQuestionIndex = 0;
        const questions = <?= json_encode($questions) ?>;

        function showQuestion(index) {
            const questionContainer = document.getElementById('questions-container');
            const questionNumber = document.getElementById('question-number');
            const submitButton = document.getElementById('submit-button');
            const nextButton = document.getElementById('next-button');
            const prevButton = document.getElementById('prev-button');

            // Update question text and options
            const question = questions[index];
            questionContainer.innerHTML = `
                <div class="mt-4">
                    <p class="font-medium">${question.question_text}</p>
                    ${['option_1', 'option_2', 'option_3', 'option_4'].map((option, i) => `
                        <label class="block mt-2">
                            <input type="radio" name="answers[${question.id}]" value="${i + 1}" class="form-radio text-blue-600" onchange="markAnswered(${index})">
                            ${question[option]}
                        </label>
                    `).join('')}
                </div>
            `;

            // Update question number
            questionNumber.textContent = `Question ${index + 1} of ${questions.length}`;

            // Show/hide navigation buttons
            prevButton.style.display = index === 0 ? 'none' : 'inline-block';
            nextButton.style.display = index === questions.length - 1 ? 'none' : 'inline-block';
            submitButton.style.display = index === questions.length - 1 ? 'inline-block' : 'none';
        }

        function markAnswered(index) {
            const questionButton = document.getElementById(`question-btn-${index}`);
            questionButton.classList.remove('bg-red-500');
            questionButton.classList.add('bg-green-500');
        }

        function navigateToQuestion(index) {
            currentQuestionIndex = index;
            showQuestion(index);
        }

        document.addEventListener('DOMContentLoaded', () => {
            showQuestion(currentQuestionIndex);
        });
    </script>
</head>         
<body class="bg-white">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <main class="max-w-[80%] mx-auto flex-col flex justify-center h-full min-h-[81.3vh] mt-1 mb-1 relative">
        <!-- Quiz Title -->
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 text-start mt-6">Aptitude Quiz</h1>

        <!-- Question Navigation Panel -->
        <div class="absolute top-0 right-0 mt-6 mr-6 bg-white shadow-lg p-4 rounded-lg border">
            <h3 class="font-bold text-lg mb-2">Questions</h3>
            <div class="grid grid-cols-5 gap-2">
                <?php foreach ($questions as $index => $question): ?>
                    <button id="question-btn-<?= $index ?>" class="w-10 h-10 bg-red-500 text-white font-bold rounded-full" onclick="navigateToQuestion(<?= $index ?>)">
                        <?= $index + 1 ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Question Display -->
        <div class="bg-white shadow-lg p-6 rounded-lg border mt-4">
            <h2 id="question-number" class="text-red-600 font-bold text-lg text-center sm:text-left mb-4"></h2>
            <form id="quiz-form" method="POST" action="submit_quiz1.php">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div id="questions-container"></div>
                <div class="flex justify-between mt-6">
                    <button type="button" id="prev-button" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600" onclick="navigateToQuestion(currentQuestionIndex - 1)">Previous</button>
                    <button type="button" id="next-button" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600" onclick="navigateToQuestion(currentQuestionIndex + 1)">Next</button>
                    <button type="submit" id="submit-button" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 hidden">Submit Quiz</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-10 bg-blue-900 text-white text-center p-4">
        &copy; 2025 Quiz Management System
    </footer>
</body>
</html>
