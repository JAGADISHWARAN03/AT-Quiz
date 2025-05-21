<!-- filepath: c:\xampp\htdocs\ar\delete_quiz.php -->
<?php
ob_clean();
header('Content-Type: application/json');
include 'includes/config.php'; // Include database connection

if (isset($_GET['id'])) {
    $quiz_id = (int)$_GET['id'];

    // Delete the quiz
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid quiz ID.']);
}
?>