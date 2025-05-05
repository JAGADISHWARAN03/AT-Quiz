<?php
include 'includes/config.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;

    if ($quiz_id > 0) {
        $stmt = $conn->prepare("UPDATE quizzes SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $quiz_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Quiz status updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update quiz status.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid quiz ID.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>