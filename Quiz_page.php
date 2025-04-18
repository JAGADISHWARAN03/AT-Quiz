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
    <header class="p-4  flex-col  bg-white shadow-md">
        <!-- Logo -->
        <div class="flex items-center justify-between space-x-4 max-w-[80%] mx-auto">
            <div class="flex items-start space-x-4">
            <img src="assets\Arrow Thought (1) 1 (1).png" alt="Logo" class="h-10"> <!-- Replace 'logo.png' with the actual logo file path -->
            </div>
            <!-- Contact Information -->
            <div class="flex items-end justify-end space-x-2 text-blue-900">
            <!-- Phone Icon -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-700 p-2 rounded-full">
                <svg fill="#000000" height="24px" width="24px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 473.806 473.806" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M374.456,293.506c-9.7-10.1-21.4-15.5-33.8-15.5c-12.3,0-24.1,5.3-34.2,15.4l-31.6,31.5c-2.6-1.4-5.2-2.7-7.7-4 c-3.6-1.8-7-3.5-9.9-5.3c-29.6-18.8-56.5-43.3-82.3-75c-12.5-15.8-20.9-29.1-27-42.6c8.2-7.5,15.8-15.3,23.2-22.8 c2.8-2.8,5.6-5.7,8.4-8.5c21-21,21-48.2,0-69.2l-27.3-27.3c-3.1-3.1-6.3-6.3-9.3-9.5c-6-6.2-12.3-12.6-18.8-18.6 c-9.7-9.6-21.3-14.7-33.5-14.7s-24,5.1-34,14.7c-0.1,0.1-0.1,0.1-0.2,0.2l-34,34.3c-12.8,12.8-20.1,28.4-21.7,46.5 c-2.4,29.2,6.2,56.4,12.8,74.2c16.2,43.7,40.4,84.2,76.5,127.6c43.8,52.3,96.5,93.6,156.7,122.7c23,10.9,53.7,23.8,88,26 c2.1,0.1,4.3,0.2,6.3,0.2c23.1,0,42.5-8.3,57.7-24.8c0.1-0.2,0.3-0.3,0.4-0.5c5.2-6.3,11.2-12,17.5-18.1c4.3-4.1,8.7-8.4,13-12.9 c9.9-10.3,15.1-22.3,15.1-34.6c0-12.4-5.3-24.3-15.4-34.3L374.456,293.506z M410.256,398.806 C410.156,398.806,410.156,398.906,410.256,398.806c-3.9,4.2-7.9,8-12.2,12.2c-6.5,6.2-13.1,12.7-19.3,20 c-10.1,10.8-22,15.9-37.6,15.9c-1.5,0-3.1,0-4.6-0.1c-29.7-1.9-57.3-13.5-78-23.4c-56.6-27.4-106.3-66.3-147.6-115.6 c-34.1-41.1-56.9-79.1-72-119.9c-9.3-24.9-12.7-44.3-11.2-62.6c1-11.7,5.5-21.4,13.8-29.7l34.1-34.1c4.9-4.6,10.1-7.1,15.2-7.1 c6.3,0,11.4,3.8,14.6,7c0.1,0.1,0.2,0.2,0.3,0.3c6.1,5.7,11.9,11.6,18,17.9c3.1,3.2,6.3,6.4,9.5,9.7l27.3,27.3 c10.6,10.6,10.6,20.4,0,31c-2.9,2.9-5.7,5.8-8.6,8.6c-8.4,8.6-16.4,16.6-25.1,24.4c-0.2,0.2-0.4,0.3-0.5,0.5 c-8.6,8.6-7,17-5.2,22.7c0.1,0.3,0.2,0.6,0.3,0.9c7.1,17.2,17.1,33.4,32.3,52.7l0.1,0.1c27.6,34,56.7,60.5,88.8,80.8 c4.1,2.6,8.3,4.7,12.3,6.7c3.6,1.8,7,3.5,9.9,5.3c0.4,0.2,0.8,0.5,1.2,0.7c3.4,1.7,6.6,2.5,9.9,2.5c8.3,0,13.5-5.2,15.2-6.9 l34.2-34.2c3.4-3.4,8.8-7.5,15.1-7.5c6.2,0,11.3,3.9,14.4,7.3c0.1,0.1,0.1,0.1,0.2,0.2l55.1,55.1 C420.456,377.706,420.456,388.206,410.256,398.806z"></path> <path d="M256.056,112.706c26.2,4.4,50,16.8,69,35.8s31.3,42.8,35.8,69c1.1,6.6,6.8,11.2,13.3,11.2c0.8,0,1.5-0.1,2.3-0.2 c7.4-1.2,12.3-8.2,11.1-15.6c-5.4-31.7-20.4-60.6-43.3-83.5s-51.8-37.9-83.5-43.3c-7.4-1.2-14.3,3.7-15.6,11 S248.656,111.506,256.056,112.706z"></path> <path d="M473.256,209.006c-8.9-52.2-33.5-99.7-71.3-137.5s-85.3-62.4-137.5-71.3c-7.3-1.3-14.2,3.7-15.5,11 c-1.2,7.4,3.7,14.3,11.1,15.6c46.6,7.9,89.1,30,122.9,63.7c33.8,33.8,55.8,76.3,63.7,122.9c1.1,6.6,6.8,11.2,13.3,11.2 c0.8,0,1.5-0.1,2.3-0.2C469.556,223.306,474.556,216.306,473.256,209.006z"></path> </g> </g> </g></svg>
            </div>
            <!-- Contact Information -->
            <div class="text-sm">
                <span class="block font-bold">Call any time</span>
                <span>+1 916 284 9204</span>
            </div>
            </div>
        </div>
    </header>

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
