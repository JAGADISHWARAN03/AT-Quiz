<?php
require 'includes/config.php';

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

$where = [];
if ($categoryId) $where[] = "category_id = $categoryId";
if ($quizId) $where[] = "quiz_id = $quizId";
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stats = [
    'num_participants' => 0,
    'avg_score' => 0,
    'avg_time' => 'N/A',
    'quiz_name' => $quizId ? '' : ($categoryId ? 'All Quizzes in Category' : 'All Quizzes')
];

$res = $conn->query("SELECT COUNT(DISTINCT user_id) as num_participants, AVG(score) as avg_score
    FROM quiz_results1
    $whereSql
");
if ($row = $res->fetch_assoc()) {
    $stats['num_participants'] = $row['num_participants'] ?? 0;
    $stats['avg_score'] = round($row['avg_score'] ?? 0, 2);
}

// Chart data (participants over time)
$labels = [];
$data = [];
$chartRes = $conn->query("
    SELECT DATE(created_at) as date, COUNT(DISTINCT user_id) as unique_participants
    FROM quiz_results1
    $whereSql
    GROUP BY DATE(created_at)
    ORDER BY date
");
while ($row = $chartRes->fetch_assoc()) {
    $labels[] = $row['date'];
    $data[] = (int)$row['unique_participants'];
}
$stats['labels'] = $labels;
$stats['data'] = $data;

header('Content-Type: application/json');
echo json_encode($stats);
?>
