<?php
require 'includes/config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $result = $conn->query("DELETE FROM users WHERE id=$id");
    echo json_encode(['success' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
}
$conn->close();
?>