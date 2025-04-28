<?php
include 'includes/config.php'; // Database connection

if (isset($_GET['category_id'])) {
    $category_id = (int)$_GET['category_id'];

    // Fetch quizzes for the selected category
    $stmt = $conn->prepare("SELECT id, title FROM quizzes WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $quizzes = [];
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }

    echo json_encode(['success' => true, 'quizzes' => $quizzes]);
} else {
    echo json_encode(['success' => false, 'message' => 'Category ID not provided']);
}
?>