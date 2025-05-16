<?php
require 'includes/config.php'; // Database connection

$query = "SELECT id, name FROM quiz_categories ORDER BY name";
$result = $conn->query($query);
$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
echo json_encode($categories);
?>