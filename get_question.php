<?php
require 'includes/config.php';

if (isset($_GET['id'])) {
    $question_id = intval($_GET['id']);
    $query = "SELECT * FROM questions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $question = $result->fetch_assoc();
        echo json_encode(['success' => true, 'question' => $question]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Question not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>