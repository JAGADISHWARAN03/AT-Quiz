<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'quiz_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all questions from the database
$questions = [];
$stmt = $conn->prepare("SELECT id, question_text, option_1, option_2, option_3, option_4, correct_option FROM questions");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            "id" => $row['id'],
            "question" => $row['question_text'],
            "options" => [$row['option_1'], $row['option_2'], $row['option_3'], $row['option_4']],
            "answer" => $row['correct_option']
        ];
    }
} else {
    echo "No questions found in the database."; // Debugging output
}
$stmt->close();
$conn->close();

$_SESSION['questions'] = $questions;
$_SESSION['total_questions'] = count($questions);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Exam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        const questions = <?php echo json_encode($questions); ?>;
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let currentQuestionIndex = 0;
            const questionContainer = document.getElementById("question-container");
            const prevButton = document.getElementById("prev");
            const nextButton = document.getElementById("next");
            const submitButton = document.getElementById("submit");
            const timerDisplay = document.getElementById("timer");
            let timer = 300; // Timer in seconds (5 minutes)

            // Load a question based on the current index
            function loadQuestion(index) {
                const question = questions[index];
                let html = `<h3 class="text-lg font-bold mb-2">${index + 1}. ${question.question}</h3>`;
                question.options.forEach((option, i) => {
                    html += `
                        <label class="flex items-center space-x-2 p-2 border rounded cursor-pointer hover:bg-gray-100">
                            <input type="radio" name="q${question.id}" value="${i + 1}" class="form-radio text-blue-600">
                            <span>${option}</span>
                        </label>`;
                });
                questionContainer.innerHTML = html;

                // Show/Hide navigation buttons
                prevButton.style.display = index === 0 ? "none" : "inline-block";
                nextButton.style.display = index === questions.length - 1 ? "none" : "inline-block";
                submitButton.style.display = index === questions.length - 1 ? "inline-block" : "none";
            }

            // Update the timer display
            function updateTimer() {
                const minutes = Math.floor(timer / 60);
                const seconds = timer % 60;
                timerDisplay.textContent = `${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;
                if (timer > 0) {
                    timer--;
                } else {
                    alert("Time's up!");
                    document.getElementById("quiz-form").submit();
                }
            }

            // Event listeners for navigation buttons
            prevButton.addEventListener("click", function () {
                if (currentQuestionIndex > 0) {
                    currentQuestionIndex--;
                    loadQuestion(currentQuestionIndex);
                }
            });

            nextButton.addEventListener("click", function () {
                if (currentQuestionIndex < questions.length - 1) {
                    currentQuestionIndex++;
                    loadQuestion(currentQuestionIndex);
                }
            });

            // Initialize the quiz
            loadQuestion(currentQuestionIndex);
            setInterval(updateTimer, 1000); // Start the timer
        });
    </script>
</head>
<body class="bg-white">
    <header class="p-4 bg-blue-900 text-white shadow-md">
        <div class="max-w-[80%] mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Quiz Exam</h1>
            <div id="timer" class="text-lg font-bold">05:00</div>
        </div>
    </header>
    <main class="max-w-[80%] mx-auto flex-col flex justify-center h-full min-h-[81.3vh] mt-4">
        <div class="bg-white shadow-lg p-6 rounded-lg border">
            <form id="quiz-form">
                <div id="question-container" class="mb-4"></div>
                <div class="flex justify-between mt-6">
                    <button type="button" id="prev" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                        ← Previous
                    </button>
                    <button type="button" id="next" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        Next →
                    </button>
                    <button type="submit" id="submit" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600" style="display: none;">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </main>
    <footer class="mt-10 bg-blue-900 text-white text-center p-4">
        &copy; 2025 Quiz System
    </footer>
</body>
</html>