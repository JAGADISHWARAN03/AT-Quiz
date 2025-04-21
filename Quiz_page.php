<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'quiz_system'); // Replace 'quiz_system' with your database name
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch questions and options from the database
$questions = [];
$stmt = $conn->prepare("SELECT question_text, option_1, option_2, option_3, option_4 FROM questions"); // Replace 'questions' with your table name
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            "question" => $row['question_text'],
            "options" => [$row['option_1'], $row['option_2'], $row['option_3'], $row['option_4']]
        ];
    }
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
    <?php include 'includes/header.php'; // Updated path to the footer file ?>

    <!-- Timer -->
    

    <!-- Main Content -->
    <main class="max-w-[80%] mx-auto flex-col flex justify-center h-full min-h-[81.3vh] mt-1 mb-1">
        <!-- Quiz Title -->
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 text-start mt-6">Aptitude Quiz</h1>

        <div class="flex flex-col sm:flex-row justify-between items-center mt-6">
       
        <h2 id="question-number" class="text-red-600 font-bold text-lg text-center sm:text-left mb-4">
    Question 1 of <?php echo count($questions); ?>
</h2>
            <div class="flex items-center bg-blue-900 text-white px-4 py-2 rounded-full shadow-lg space-x-4 mt-4 sm:mt-0">
            <svg width="25px" height="25px" viewBox="0 0 48 48" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools --> <title>stopwatch</title> <desc>Created with Sketch.</desc> <g id="stopwatch" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" stroke-linejoin="round"> <rect width="48" height="48" fill="white" fill-opacity="0.01"></rect> <g id="编组" transform="translate(7.000000, 3.000000)" stroke-width="4"> <circle id="Oval-3" stroke="#000000" fill="#2F88FF" fill-rule="nonzero" cx="17" cy="24" r="17"> </circle> <path d="M11,1 L23,1" id="Path-13" stroke="#000000" stroke-linecap="round"> </path> <path d="M17,16 L17,24" id="Path-14" stroke="#FFFFFF" stroke-linecap="round"> </path> <path d="M25,24 L17,24" id="Path-14" stroke="#FFFFFF" stroke-linecap="round"> </path> <path d="M17,1 L17,5" id="Path-14" stroke="#000000" stroke-linecap="round"> </path> </g> </g> </g></svg>                <div class="text-xs">Quiz Time Start</div>
                <div class="text-lg font-bold flex space-x-1 bg-white text-blue-900 px-2 py-1 rounded-md">
                    <span id="minutes">00</span> <span>MIN</span> | <span id="seconds">00</span> <span>SEC</span>
                </div>
            </div>
        </div>
        <div class="bg-white shadow-lg p-6 rounded-lg border mt-4">
            <!-- Display current question number and total questions -->
            <!-- <h2 id="question-number" class="text-2xl font-bold mb-4">Question 1 of <?php echo count($questions); ?></h2> -->
            <p id="question-text" class="mt-2 text-gray-700"></p>
            <div class="bg-blue-900 text-white p-2 mt-4 rounded-md text-sm">
                Select the most appropriate option from the given choices.
            </div>
            <form id="options-container" class="mt-4 space-y-2">
                <!-- Options will be dynamically inserted here -->
            </form>
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-6 flex justify-between">
            <button id="prev" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md flex items-center space-x-2 hover:bg-gray-100" style="display: none;">
                ← Previous
            </button>
            <button id="next" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md flex items-center space-x-2 hover:bg-gray-100">
                Next →
            </button>
            <button id="submit" class="bg-green-500 text-white px-4 py-2 rounded-md flex items-center space-x-2 hover:bg-green-600" style="display: none;">
                Submit
            </button>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-10 bg-blue-900 text-white text-center p-4">
        &copy; 2025 Quiz Management System
    </footer>

    <!-- Timer Script -->
    <script>
        let timer = 0;
        setInterval(() => {
            timer++;
            document.getElementById('minutes').innerText = String(Math.floor(timer / 60)).padStart(2, '0');
            document.getElementById('seconds').innerText = String(timer % 60).padStart(2, '0');
        }, 1000);

        const questions = <?php echo json_encode($questions); ?>;
        let currentQuestionIndex = 0;

        // Function to load a question
        function loadQuestion(index) {
            const question = questions[index];
            document.getElementById("question-number").textContent = `Question ${index + 1} of ${questions.length}`;
            document.getElementById("question-text").textContent = question.question;

            const optionsContainer = document.getElementById("options-container");
            optionsContainer.innerHTML = ""; // Clear previous options

            question.options.forEach((option, i) => {
                const optionElement = document.createElement("label");
                optionElement.className = "flex items-center space-x-2";
                optionElement.innerHTML = `
                    <input type="radio" name="answer" class="form-radio text-blue-600" value="${i}">
                    <span>${option}</span>
                `;
                optionsContainer.appendChild(optionElement);
            });

            // Show/Hide navigation buttons
            document.getElementById("prev").style.display = index === 0 ? "none" : "inline-flex";
            document.getElementById("next").style.display = index === questions.length - 1 ? "none" : "inline-flex";
            document.getElementById("submit").style.display = index === questions.length - 1 ? "inline-flex" : "none";
        }

        // Event listeners for navigation buttons
        document.getElementById("prev").addEventListener("click", () => {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                loadQuestion(currentQuestionIndex);
            }
        });

        document.getElementById("next").addEventListener("click", () => {
            if (currentQuestionIndex < questions.length - 1) {
                currentQuestionIndex++;
                loadQuestion(currentQuestionIndex);
            }
        });

        // Event listener for the "Submit" button
        document.getElementById("submit").addEventListener("click", () => {
            alert("Quiz submitted successfully!");
            // Add form submission logic here
        });

        // Load the first question on page load
        loadQuestion(currentQuestionIndex);
    </script>
</body>
</html>
