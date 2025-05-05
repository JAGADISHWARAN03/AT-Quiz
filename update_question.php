<?php
require 'includes/config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'], $data['question_text'], $data['question_type'], $data['option_1'], $data['option_2'], $data['option_3'], $data['option_4'], $data['correct_option'])) {
    $id = (int)$data['id'];
    $question_text = $conn->real_escape_string($data['question_text']);
    $question_type = $conn->real_escape_string($data['question_type']);
    $option_1 = $conn->real_escape_string($data['option_1']);
    $option_2 = $conn->real_escape_string($data['option_2']);
    $option_3 = $conn->real_escape_string($data['option_3']);
    $option_4 = $conn->real_escape_string($data['option_4']);
    $correct_option = $conn->real_escape_string($data['correct_option']);

    $stmt = $conn->prepare("UPDATE questions SET question_text = ?, question_type = ?, option_1 = ?, option_2 = ?, option_3 = ?, option_4 = ?, correct_option = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $question_text, $question_type, $option_1, $option_2, $option_3, $option_4, $correct_option, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
}
?>