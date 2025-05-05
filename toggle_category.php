<?php
require 'includes/config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_id'])) {
    $category_id = intval($_POST['category_id']);

    // Toggle the status of the category
    $query = "UPDATE quiz_categories SET status = NOT status WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);

    if ($stmt->execute()) {
        // Fetch the updated status
        $status_query = "SELECT status FROM quiz_categories WHERE id = ?";
        $status_stmt = $conn->prepare($status_query);
        $status_stmt->bind_param("i", $category_id);
        $status_stmt->execute();
        $status_result = $status_stmt->get_result();
        $status_row = $status_result->fetch_assoc();

        $new_status = $status_row['status'] ? 'activated' : 'deactivated';
        echo json_encode(['success' => true, 'message' => "Quiz category has been $new_status."]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update category status.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>