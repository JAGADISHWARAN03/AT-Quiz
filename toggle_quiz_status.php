<?php
include 'includes/config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $quiz_id = (int)$_GET['id'];

    // Fetch the current status
    $stmt = $conn->prepare("SELECT status FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quiz = $result->fetch_assoc();
    $stmt->close();

    if ($quiz) {
        // Toggle the status
        $new_status = $quiz['status'] ? 0 : 1;

        // Update the status in the database
        $stmt = $conn->prepare("UPDATE quizzes SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $quiz_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>