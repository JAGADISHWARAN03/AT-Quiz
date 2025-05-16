<?php
require 'includes/config.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Get total categories
$total_categories_result = $conn->query("SELECT COUNT(*) as total FROM quiz_categories");
$total_categories = $total_categories_result->fetch_assoc()['total'];
$total_pages = ceil($total_categories / $per_page);

// Fetch categories for this page
$result = $conn->query("SELECT id, name, description, timer, status FROM quiz_categories ORDER BY id DESC LIMIT $per_page OFFSET $offset");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()): ?>
        <div class="relative bg-white border border-gray-200 rounded-xl p-5 shadow-sm card-hover" data-category-id="<?= $row['id'] ?>">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800 category-name truncate"><?= htmlspecialchars($row['name']) ?></h3>
                <label class="custom-toggle">
                    <input type="checkbox" class="sr-only peer"
                        onchange="toggleCategory(<?= $row['id'] ?>)"
                        <?= $row['status'] ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="mb-4 flex gap-2">
                <a href="javascript:void(0);"
                    class="block flex-1 text-center px-4 py-2 bg-indigo-500 text-white font-medium rounded-lg hover:bg-indigo-600 transition"
                    onclick="loadQuizzes(<?= $row['id'] ?>)">
                    View Quizzes
                </a>
            </div>
            <div class="flex justify-between items-end border-t border-gray-100 pt-3">
                <button
                    class="block px-4 py-2 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 transition"
                    style="margin-right:auto;"
                    onclick="openAddQuestionModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['name'])) ?>')">
                    Add Questions
                </button>
                <div class="flex space-x-3">
                    <button onclick="openUpdateCategoryModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>')"
                        class="icon-btn text-indigo-500 hover:text-indigo-600">
                        <i class="fas fa-edit text-lg"></i>
                        <span class="tooltip">Update</span>
                    </button>
                    <button onclick="deleteCategory(<?= $row['id'] ?>)"
                        class="icon-btn text-red-500 hover:text-red-600">
                        <i class="fas fa-trash text-lg"></i>
                        <span class="tooltip">Delete</span>
                    </button>
                </div>
            </div>
        </div>
    <?php endwhile;
} else {
    echo '<tr><td colspan="5" class="p-4 text-center text-gray-500">No categories found.</td></tr>';
}

// Pagination controls
echo '<div class="flex justify-between items-center mt-4">';
if ($page > 1) {
    echo '<button onclick="loadCategoriesTable('.($page-1).')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Previous</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Previous</span>';
}
echo '<span class="text-gray-700">Page '.$page.' of '.$total_pages.'</span>';
if ($page < $total_pages) {
    echo '<button onclick="loadCategoriesTable('.($page+1).')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Next</button>';
} else {
    echo '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Next</span>';
}
echo '</div>';

$conn->close();
?>