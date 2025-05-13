<?php
require 'includes/config.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Get total users
$total_users_result = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $total_users_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $per_page);

// Fetch users for this page
$result = $conn->query("SELECT id, name, email, city, exam_date FROM users ORDER BY name DESC LIMIT $per_page OFFSET $offset");

echo '<table class="min-w-full border border-gray-300 rounded-lg shadow text-sm">';
echo '<thead class="bg-indigo-100 text-indigo-800">';
echo '<tr>
        <th class="p-3 border text-left">ID</th>
        <th class="p-3 border text-left">Name</th>
        <th class="p-3 border text-left">Email</th>
       
      </tr></thead><tbody>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr class="bg-white hover:bg-gray-50 transition">';
        echo '<td class="p-3 border">'.htmlspecialchars($row['id']).'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($row['name']).'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($row['email']).'</td>';
      
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4" class="p-4 text-center text-gray-500">No users found.</td></tr>';
}
echo '</tbody></table>';

// Pagination controls
echo '<div class="flex justify-between items-center mt-4">';
if ($page > 1) {
    echo '<button onclick="loadUsersTable('.($page-1).')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Previous</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Previous</span>';
}
echo '<span class="text-gray-700">Page '.$page.' of '.$total_pages.'</span>';
if ($page < $total_pages) {
    echo '<button onclick="loadUsersTable('.($page+1).')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Next</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Next</span>';
}
echo '</div>';

$conn->close();
?>