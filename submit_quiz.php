<?php
require 'includes/config.php';

// Get submitted data
$token = isset($_POST['token']) ? $_POST['token'] : '';
$user_answers = isset($_POST['answers']) ? $_POST['answers'] : [];

// Validate the token and fetch the email and category_id
$stmt = $conn->prepare("SELECT email, category_id FROM quiz_links WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired quiz link.");
}

$link_data = $result->fetch_assoc();
$user_email = $link_data['email'];
$category_id = $link_data['category_id'];
$stmt->close();

// Fetch the user_id from the users table
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user_data = $result->fetch_assoc();
$user_id = $user_data['id'];
$stmt->close();

// Fetch correct answers from the database
$stmt = $conn->prepare("SELECT id, correct_option FROM questions WHERE quiz_category = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

$score = 0;
$total_questions = 0;

while ($row = $result->fetch_assoc()) {
    $total_questions++;
    $question_id = $row['id'];
    $correct_option = $row['correct_option'];

    // Check if the user's answer matches the correct option
    if (isset($user_answers[$question_id]) && $user_answers[$question_id] == $correct_option) {
        $score++;
    }
}
$stmt->close();

// Fetch user's city and skill from the users table
$stmt = $conn->prepare("SELECT city, skill FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user_data = $result->fetch_assoc();
$city = $user_data['city'];
$skill = $user_data['skill'];
$stmt->close();

// Store the results in the quiz_results table
$stmt = $conn->prepare("INSERT INTO quiz_results (user_id, user_email, category_id, score, total_questions, city, skill) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isiiiss", $user_id, $user_email, $category_id, $score, $total_questions, $city, $skill);
$stmt->execute();
$stmt->close();

$conn->close();

// Redirect to the results page
header("Location: result.php?success=1");
exit;
?>