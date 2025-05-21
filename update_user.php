<?php
require 'includes/config.php';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
if ($id && $email) {
    $result = $conn->query("UPDATE users SET email='$email' WHERE id=$id");
    echo json_encode(['success' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}
$conn->close();
?>