<?php
// Start session
session_start();

// Database connection
require 'includes/config.php';

// Set JSON header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the category name from the POST request
    $category_name = isset($_POST['name']) ? trim($_POST['name']) : '';

    // Validate the category name
    if (empty($category_name)) {
        echo json_encode(['success' => false, 'message' => 'Category name cannot be empty.']);
        exit;
    }

    // Prepare the SQL query to insert the category
    $stmt = $conn->prepare("INSERT INTO quiz_categories (name) VALUES (?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare the SQL statement.']);
        exit;
    }

    $stmt->bind_param("s", $category_name);

    // Execute the query and check for errors
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