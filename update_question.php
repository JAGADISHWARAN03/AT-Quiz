<?php
require 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $question_text = $_POST['question_text'];
    $option_1 = $_POST['option_1'];
    $option_2 = $_POST['option_2'];
    $option_3 = $_POST['option_3'];
    $option_4 = $_POST['option_4'];
    $correct_option = $_POST['correct_option'];

    $query = "UPDATE questions SET question_text = ?, option_1 = ?, option_2 = ?, option_3 = ?, option_4 = ?, correct_option = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi", $question_text, $option_1, $option_2, $option_3, $option_4, $correct_option, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Question updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update question.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>          