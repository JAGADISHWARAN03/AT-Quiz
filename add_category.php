<?php
require 'includes/config.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO quiz_categories (name) VALUES (?)");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }

        $stmt->bind_param("s", $name);
        $success = $stmt->execute();

        if ($success) {
            $category_id = $conn->insert_id; // Get the ID of the newly inserted category
            echo json_encode([
                'success' => true,
                'category_id' => $category_id,
                'message' => 'Category added successfully!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add category: ' . $stmt->error
            ]);
        }

        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) { // Duplicate entry error
            echo json_encode([
                'success' => false,
                'message' => 'Category name already exists.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Unexpected error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}

$conn->close();
exit;
?>