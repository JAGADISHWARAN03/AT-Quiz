<?php

require 'includes/config.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Get total quizzes
$total_quizzes_result = $conn->query("SELECT COUNT(*) as total FROM quizzes");
$total_quizzes = $total_quizzes_result->fetch_assoc()['total'];
$total_pages = ceil($total_quizzes / $per_page);

// Fetch quizzes with category name for this page
$query = "
    SELECT q.id, q.title, q.description, q.timer, q.status, c.name AS category_name
    FROM quizzes q
    LEFT JOIN quiz_categories c ON q.category_id = c.id
    ORDER BY q.id DESC
    LIMIT $per_page OFFSET $offset
";
$result = $conn->query($query);

echo '<table class="min-w-full border border-gray-300 rounded-lg shadow text-sm">';
echo '<thead class="bg-green-100 text-green-800">';
echo '<tr>
        <th class="p-3 border text-left">ID</th>
         <th class="p-3 border text-left">Category</th>
        <th class="p-3 border text-left">Title</th>
        <th class="p-3 border text-left">Description</th>
        <th class="p-3 border text-center">Timer (min)</th>
        <th class="p-3 border text-center">Status</th>
      </tr></thead><tbody>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr class="bg-white hover:bg-gray-50 transition">';
        echo '<td class="p-3 border">'.htmlspecialchars($row['id']).'</td>';
         echo '<td class="p-3 border">'.htmlspecialchars($row['category_name'] ?? 'N/A').'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($row['title']).'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($row['description']).'</td>';
        echo '<td class="p-3 border text-center">'.htmlspecialchars($row['timer']).'</td>';
        echo '<td class="p-3 border text-center">'.($row['status'] ? 'Active' : 'Inactive').'</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6" class="p-4 text-center text-gray-500">No quizzes found.</td></tr>';
}
echo '</tbody></table>';

// Pagination controls
echo '<div class="flex justify-between items-center mt-4">';
if ($page > 1) {
    echo '<button onclick="loadQuizzesTable('.($page-1).')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Previous</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Previous</span>';
}
echo '<span class="text-gray-700">Page '.$page.' of '.$total_pages.'</span>';
if ($page < $total_pages) {
    echo '<button onclick="loadQuizzesTable('.($page+1).')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Next</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Next</span>';
}
echo '</div>';

$conn->close();
?>