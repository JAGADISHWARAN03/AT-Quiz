<?php
include 'includes/config.php'; // Database connection

// Check if the request method is DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    // Get the category ID from POST data
    $categoryId = $_POST['id'] ?? null;

    if ($categoryId) {
        // Prepare the DELETE query
        $stmt = $conn->prepare("DELETE FROM quiz_categories WHERE id = ?");
        $stmt->bind_param("i", $categoryId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid category ID']);
    }
}
?>

