<?php
session_start();
include 'includes/config.php'; // Include database connection

if (!isset($_SESSION['user_name']) || !isset($_SESSION['quiz_title_id'])) {
    header("Location: welcome.php");
    exit;
}

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_city = $_SESSION['user_city'];
$user_mobile = $_SESSION['user_mobile'];
$quiz_title_id = $_SESSION['quiz_title_id'];
$user_answers = isset($_POST['answers']) ? $_POST['answers'] : [];

// Fetch correct answers from the database
$stmt = $conn->prepare("SELECT id, correct_option FROM questions WHERE quiz_title_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $quiz_title_id);
$stmt->execute();
$result = $stmt->get_result();

$score = 0;
$total_questions = 0;

while ($row = $result->fetch_assoc()) {
    $total_questions++;
    $question_id = $row['id'];
    $correct_option = $row['correct_option'];

    if (isset($user_answers[$question_id]) && $user_answers[$question_id] == $correct_option) {
        $score++;
    }
}
$stmt->close();

// Fetch category_id from the quiz_titles table
$stmt = $conn->prepare("SELECT category_id FROM quizzes WHERE id = ?");
$stmt->bind_param("i", $quiz_title_id);
$stmt->execute();
$stmt->bind_result($category_id);
$stmt->fetch();
$stmt->close();

// Fetch skill from the users table
$stmt = $conn->prepare("SELECT skill FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($skill);
$stmt->fetch();
$stmt->close();

// Store the results in the database
$stmt = $conn->prepare("INSERT INTO quiz_results1 (user_email, category_id, score, total_questions, city, skill) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("siisss", $user_email, $category_id, $score, $total_questions, $user_city, $skill); // Corrected the type string and variables
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$stmt->close();

// Redirect to the thank-you page
header("Location: thankyou.php");
exit;
?>