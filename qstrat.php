<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Quiz</title>

</head>
<body>
    <h2>Welcome to the Quiz</h2>
    <p>Please enable your Camera and Microphone to start the quiz.</p>
    
    <form action="quiz.php" method="POST" id="quizForm">
        <button id="startQuiz" disabled>Start Quiz</button>
    </form>

    <script src="st.js"></script> <!-- Compiled TypeScript File -->
</body>
</html>
