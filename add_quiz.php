<?php
$conn = new mysqli('localhost', 'root', '', 'quiz_system');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}

$category_id = $_POST['category_id'] ?? '';
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$timer = $_POST['timer'] ?? '';

if (empty($category_id) || empty($title) || empty($description) || empty($timer)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO quizzes (category_id, title, description, timer) VALUES (?, ?, ?, ?)");
$stmt->bind_param("issi", $category_id, $title, $description, $timer);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Quiz added successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add quiz.']);
}
$stmt->close();
$conn->close();
?>