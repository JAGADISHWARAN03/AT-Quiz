<?php
require 'includes/config.php';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$where = $category_id ? "WHERE category_id = $category_id" : "";
$res = $conn->query("SELECT id, title FROM quizzes $where ORDER BY title");
$titles = [];
while ($row = $res->fetch_assoc()) {
    $titles[] = $row;
}
header('Content-Type: application/json');
echo json_encode($titles);