<style>
tr:hover { background: none !important; }
</style>

<?php
require 'includes/config.php'; // Include database connection
date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$users_per_page = 6;
$offset = ($current_page - 1) * $users_per_page;

// Fetch today's users
$users_query = "SELECT id, name, email, city, exam_date FROM users WHERE DATE(exam_date) = '$today' ORDER BY exam_date DESC LIMIT $users_per_page OFFSET $offset";
$users_result = $conn->query($users_query);

// Total for pagination
$total_users_query = "SELECT COUNT(*) AS total FROM users WHERE DATE(exam_date) = '$today'";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_users / $users_per_page));

// Table HTML
echo '<table class="min-w-full border border-gray-200 rounded-lg shadow text-sm mb-2">';
echo '<thead style="background:#e8edfd;">';
echo '<tr>
    <th class="p-3 border text-left font-bold text-[#2d3a5b]">ID</th>
    <th class="p-3 border text-left font-bold text-[#2d3a5b]">Name</th>
    <th class="p-3 border text-left font-bold text-[#2d3a5b]">Email</th>
    <th class="p-3 border text-left font-bold text-[#2d3a5b]">City</th>
    <th class="p-3 border text-left font-bold text-[#2d3a5b]">Date</th>
    <th class="p-3 border text-left font-bold text-[#2d3a5b]">Time</th>
</tr></thead><tbody>';

if ($users_result->num_rows > 0) {
    while ($row = $users_result->fetch_assoc()) {
        $dateTime = new DateTime($row['exam_date']);
        $date = $dateTime->format('Y-m-d');
        $time = $dateTime->format('H:i:s');
        echo '<tr class="bg-white  transition">';
        echo '<td class="p-3 border">'.htmlspecialchars($row['id']).'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($row['name']).'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($row['email']).'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($row['city']).'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($date).'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($time).'</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6" class="p-4 text-center text-gray-500">No results found for today.</td></tr>';
}
echo '</tbody></table>';

// Pagination controls
echo '<div class="flex justify-between items-center mt-4">';
if ($current_page > 1) {
    echo '<button onclick="loadCategories('.($current_page-1).')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Previous</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Previous</span>';
}
echo '<span class="text-gray-700">Page '.$current_page.' of '.$total_pages.'</span>';
if ($current_page < $total_pages) {
    echo '<button onclick="loadCategories('.($current_page+1).')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Next</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Next</span>';
}
echo '</div>';
?>