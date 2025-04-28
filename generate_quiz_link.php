<?php
include 'includes/config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $quiz_id = (int)$_GET['id'];

    // Generate a unique token
    $token = bin2hex(random_bytes(16));

    // Store the token in the database
    $stmt = $conn->prepare("INSERT INTO quiz_links (quiz_id, token) VALUES (?, ?)");
    $stmt->bind_param("is", $quiz_id, $token);

    if ($stmt->execute()) {
        // Return the generated link
        echo json_encode(['success' => true, 'link' => "Quiz_page.php?token=$token"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert token into database.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>