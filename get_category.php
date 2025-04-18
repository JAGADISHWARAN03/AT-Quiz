<?php
require 'includes/config.php';

if (isset($_GET['id'])) {
    $category_id = intval($_GET['id']);
    $query = "SELECT * FROM quiz_categories WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
        echo json_encode(['success' => true, 'category' => $category]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Category not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>