<?php
// Start session
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'quiz_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set JSON header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = $_POST['category_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $timer = $_POST['timer'] ?? 0;

    if (empty($category_name)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO quiz_categories (name, description, timer) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $category_name, $description, $timer);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category added successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add category: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>