<?php
require 'includes/config.php';

date_default_timezone_set('Asia/Kolkata'); // or your server's timezone

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Get today's date
$today = date('Y-m-d');

// Get total results for today
$total_users_result = $conn->query("SELECT COUNT(*) as total FROM quiz_results1 WHERE DATE(created_at) = '$today'");
$total_users = $total_users_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $per_page);

// Fetch results for this page and today (add total_questions and score)
$result = $conn->query("SELECT id, user_email, city, total_questions, score, created_at FROM quiz_results1 WHERE DATE(created_at) = '$today' ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");

echo '<table class="min-w-full border border-gray-300 rounded-lg shadow text-sm">';
echo '<thead class="bg-indigo-100 text-indigo-800">';
echo '<tr>
        <th class="p-3 border text-left">ID</th>
       
        <th class="p-3 border text-left">Email</th>
        <th class="p-3 border text-left">City</th>
        <th class="p-3 border text-left">Total Questions</th>
        <th class="p-3 border text-left">Score</th>
        <th class="p-3 border text-left">Date</th>
        <th class="p-3 border text-left">Time</th>
      </tr></thead><tbody>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dateTime = new DateTime($row['created_at']);
        $date = $dateTime->format('Y-m-d');
        $time = $dateTime->format('H:i:s');
       echo '<tr class="bg-white">';
        echo '<td class="p-3 border">' . htmlspecialchars($row['id']) . '</td>';

        echo '<td class="p-3 border">' . htmlspecialchars($row['user_email']) . '</td>';
        echo '<td class="p-3 border">' . htmlspecialchars($row['city']) . '</td>';
        echo '<td class="p-3 border">' . htmlspecialchars($row['total_questions']) . '</td>';
        echo '<td class="p-3 border">' . htmlspecialchars($row['score']) . '</td>';
        echo '<td class="p-3 border">' . htmlspecialchars($date) . '</td>';
        echo '<td class="p-3 border">' . htmlspecialchars($time) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="8" class="p-4 text-center text-gray-500">No results found for today.</td></tr>';
}
echo '</tbody></table>';

// Pagination controls
echo '<div class="flex justify-between items-center mt-4">';
if ($page > 1) {
    echo '<button onclick="loadUsersTable(' . ($page - 1) . ')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Previous</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Previous</span>';
}
echo '<span class="text-gray-700">Page ' . $page . ' of ' . $total_pages . '</span>';
if ($page < $total_pages) {
    echo '<button onclick="loadUsersTable(' . ($page + 1) . ')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Next</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Next</span>';
}
echo '</div>';

$conn->close();
