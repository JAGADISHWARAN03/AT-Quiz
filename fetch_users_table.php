<?php
require 'includes/config.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$where = '';
if ($search !== '') {
    $where = "WHERE id LIKE '%$search%' OR email LIKE '%$search%'";
}

$total_query = "SELECT COUNT(*) as total FROM users $where";
$total_result = $conn->query($total_query);
$total = $total_result ? $total_result->fetch_assoc()['total'] : 0;
$total_pages = max(1, ceil($total / $per_page));

$query = "SELECT id, name, email, city FROM users $where ORDER BY id DESC LIMIT $per_page OFFSET $offset";
$result = $conn->query($query);

echo '<table class="min-w-full border border-gray-200 rounded-lg shadow text-sm mb-2">';
echo '<thead style="background:#e8edfd;">';
echo '<tr>
    <th class="p-3 border text-left font-bold text-[#2d3a5b]">ID</th>
    <th class="p-3 border text-left font-bold text-[#2d3a5b]">Name</th>
    <th class="p-3 border text-left font-bold text-[#2d3a5b]">Email</th>
    <th class="p-3 border text-left font-bold text-[#2d3a5b]">City</th>
    <th class="p-3 border text-left font-bold text-[#2d3a5b]">Actions</th>
</tr></thead><tbody>';

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr class="bg-white">';
        echo '<td class="p-3 border">'.htmlspecialchars($row['id']).'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($row['name']).'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($row['email']).'</td>';
        echo '<td class="p-3 border">'.htmlspecialchars($row['city']).'</td>';
        echo '<td class="p-3 border">
            <button onclick="openEditUserModal('.$row['id'].', \''.htmlspecialchars($row['email']).'\')" class="text-indigo-600 hover:underline">Edit</button>
            <button onclick="deleteUser('.$row['id'].')" class="text-red-600 hover:underline ml-2">Delete</button>
        </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5" class="p-4 text-center text-gray-500">No users found.</td></tr>';
}
echo '</tbody></table>';

// Pagination controls
echo '<div class="flex justify-between items-center mt-4">';
if ($page > 1) {
    echo '<button onclick="loadUsersTableWithActions('.($page-1).')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Previous</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Previous</span>';
}
echo '<span class="text-gray-700">Page '.$page.' of '.$total_pages.'</span>';
if ($page < $total_pages) {
    echo '<button onclick="loadUsersTableWithActions('.($page+1).')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Next</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Next</span>';
}
echo '</div>';

$conn->close();
?>