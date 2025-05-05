<?php
include 'includes/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['quiz_id'], $data['title'], $data['description'], $data['timer'])) {
    $quiz_id = (int)$data['quiz_id'];
    $title = $conn->real_escape_string($data['title']);
    $description = $conn->real_escape_string($data['description']);
    $timer = (int)$data['timer'];

    $stmt = $conn->prepare("UPDATE quizzes SET title = ?, description = ?, timer = ? WHERE id = ?");
    $stmt->bind_param("ssii", $title, $description, $timer, $quiz_id);

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