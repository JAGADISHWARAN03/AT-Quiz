<?php
require 'includes/config.php';

session_start();

// Check if quiz_title_id is provided in the URL
if (!isset($_GET['quiz_title_id']) || !isset($_SESSION['user_name'])) {
    header("Location: user_form.php?quiz_title_id=" . $_GET['quiz_title_id']);
    exit;
}

$quiz_title_id = (int)$_GET['quiz_title_id'];

// Fetch category_id for the quiz
$stmt = $conn->prepare("SELECT category_id FROM quizzes WHERE id = ?");
$stmt->bind_param("i", $quiz_title_id);
$stmt->execute();
$stmt->bind_result($category_id);
$stmt->fetch();
$stmt->close();

// Fetch timer (in seconds) from categories table
$stmt = $conn->prepare("SELECT timer FROM quizzes WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$stmt->bind_result($timer_seconds);
$stmt->fetch();
$stmt->close();

// Default to 10 minutes if not set
if (!$timer_seconds) $timer_seconds = 600;

// Fetch questions for the quiz based on quiz_title_id
$stmt = $conn->prepare("SELECT id, question_text, option_1, option_2, option_3, option_4, question_type FROM questions WHERE quiz_title_id = ?");
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
    <header class="w-full flex items-center justify-between px-10 py-4 bg-white shadow-md relative">
        <div class="flex items-center space-x-4">
            <img src="assets/Arrow Thought (1) 1 (1).png" alt="Logo" class="h-10">
            
        </div>
        <!-- Timer in header right -->
        <div class="flex items-center">
            <div class="timer-box flex items-center bg-[#0a3880] text-white rounded-full px-6 py-2 shadow-lg">
                <svg class="w-7 h-7 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="#fff" stroke-width="2" fill="none"/>
                    <path stroke="#fff" stroke-width="2" d="M12 6v6l4 2"/>
                </svg>
                <div class="mr-4 text-center">
                    <div class="text-xs font-semibold leading-tight">Quiz<br>Time Start</div>
                </div>
                <div class="flex items-center bg-white text-black rounded-full px-4 py-1 font-mono font-bold">
                    <span id="timer-min" class="text-2xl">00</span>
                    <span class="mx-1 text-base font-normal">MIN</span>
                    <span class="border-l-2 border-[#0a3880] h-6 mx-2"></span>
                    <span id="timer-sec" class="text-2xl">00</span>
                    <span class="ml-1 text-base font-normal">SEC</span>
                </div>
            </div>
        </div>
    </header>
  
   
    </div>
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
                <input type="hidden" name="quiz_title_id" value="<?= htmlspecialchars($quiz_title_id) ?>">
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
    <script>
        let currentQuestionIndex = 0;
        const questions = <?= json_encode($questions) ?>;

        function showQuestion(index) {
            const questionContainer = document.getElementById('questions-container');
            const questionNumber = document.getElementById('question-number');
            const submitButton = document.getElementById('submit-button');
            const nextButton = document.getElementById('next-button');
            const prevButton = document.getElementById('prev-button');

            const question = questions[index];

            let optionsHtml = '';
            if (question.question_type === 'checkbox') {
                optionsHtml = ['option_1', 'option_2', 'option_3', 'option_4']
                    .map((option, i) => `
                        <label class="block mt-2">
                            <input type="checkbox" name="answers[${question.id}][]" value="${i + 1}" class="form-checkbox text-blue-600" onchange="markAnswered(${index})">
                            ${question[option]}
                        </label>
                    `).join('');
            } else if (question.question_type === 'text') {
                optionsHtml = `
                    <label class="block mt-2">
                        <input type="text" name="answers[${question.id}]" class="form-input w-full border rounded-md text-gray-800" onchange="markAnswered(${index})" placeholder="Type your answer here">
                    </label>
                `;
            } else { // default to radio
                optionsHtml = ['option_1', 'option_2', 'option_3', 'option_4']
                    .map((option, i) => `
                        <label class="block mt-2">
                            <input type="radio" name="answers[${question.id}]" value="${i + 1}" class="form-radio text-blue-600" onchange="markAnswered(${index})">
                            ${question[option]}
                        </label>
                    `).join('');
            }

            questionContainer.innerHTML = `
                <div class="mt-4">
                    <p class="font-medium">${question.question_text}</p>
                    ${optionsHtml}
                </div>
            `;

            questionNumber.textContent = `Question ${index + 1} of ${questions.length}`;
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
    <script>
    let timer = <?= (int)$timer_seconds ?>;
    let timerInterval;

    function startTimer() {
        updateTimerDisplay();
        timerInterval = setInterval(() => {
            timer--;
            updateTimerDisplay();
            if (timer <= 0) {
                clearInterval(timerInterval);
                document.getElementById('quiz-form').submit();
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        const min = String(Math.floor(timer / 60)).padStart(2, '0');
        const sec = String(timer % 60).padStart(2, '0');
        document.getElementById('timer-min').textContent = min;
        document.getElementById('timer-sec').textContent = sec;
    }

    document.addEventListener('DOMContentLoaded', () => {
        startTimer();
    });
</script>
</body>
</html>
