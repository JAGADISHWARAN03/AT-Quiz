<?php
// filepath: c:\xampp\htdocs\AT-Quiz-main\get_quiz_usage_stats.php
require 'includes/config.php';

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$params = [];
$on = "q.id = qr.quiz_title_id";
if (!empty($start_date) && !empty($end_date)) {
    $on .= " AND qr.created_at BETWEEN ? AND ?";
    $params[] = $start_date . " 00:00:00";
    $params[] = $end_date . " 23:59:59";
}

$sql = "
    SELECT q.title, COUNT(qr.id) as attempts
    FROM quizzes q
    LEFT JOIN quiz_results1 qr ON $on
    GROUP BY q.id
    ORDER BY q.title
";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$titles = [];
$counts = [];
while ($row = $result->fetch_assoc()) {
    $titles[] = $row['title'];
    $counts[] = (int)$row['attempts'];
}

header('Content-Type: application/json');
echo json_encode([
    'titles' => $titles,
    'counts' => $counts
]);
