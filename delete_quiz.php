<!-- filepath: c:\xampp\htdocs\ar\delete_quiz.php -->
<?php
require 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $quiz_id = intval($_DELETE['id']);

    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quiz deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete quiz.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>