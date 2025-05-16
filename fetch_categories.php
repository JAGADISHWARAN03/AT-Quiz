<?php
require 'includes/config.php'; // Include database connection

// Get the current page from the AJAX request
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$categories_per_page = 6; // Number of categories per page
$offset = ($current_page - 1) * $categories_per_page;

// Fetch categories for the current page
$categories_query = "SELECT * FROM quiz_categories LIMIT $categories_per_page OFFSET $offset";
$categories_result = $conn->query($categories_query);

// Fetch total pages
$total_categories_query = "SELECT COUNT(*) AS total FROM quiz_categories";
$total_categories_result = $conn->query($total_categories_query);
$total_categories = $total_categories_result->fetch_assoc()['total'];
$total_pages = ceil($total_categories / $categories_per_page);

// Generate the HTML for categories
$output = '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">';
while ($row = $categories_result->fetch_assoc()) {
    $output .= '
        <div class="relative bg-white border border-gray-200 rounded-xl p-5 shadow-sm card-hover" data-category-id="' . $row['id'] . '">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800 category-name truncate">' . htmlspecialchars($row['name']) . '</h3>
                <label class="custom-toggle">
                    <input type="checkbox" class="sr-only peer" 
                           onchange="toggleCategory(' . $row['id'] . ')" 
                           ' . ($row['status'] ? 'checked' : '') . '>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="mb-4 flex gap-2">
                <a href="javascript:void(0);" 
                   class="block flex-1 text-center px-4 py-2 bg-indigo-500 text-white font-medium rounded-lg hover:bg-indigo-600 transition" 
                   onclick="loadQuizzes(' . $row['id'] . ')">
                    View Quizzes
                </a>
            </div>
            <div class="flex justify-between items-end border-t border-gray-100 pt-3">
                <button
                    class="block px-4 py-2 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 transition"
                    style="margin-right:auto;"
                    onclick="openAddQuestionModal(' . $row['id'] . ', \'' . htmlspecialchars(addslashes($row['name'])) . '\')">
                    Add Questions
                </button>
                <div class="flex space-x-3">
                    <button onclick="openUpdateCategoryModal(' . $row['id'] . ', \'' . htmlspecialchars($row['name']) . '\')"
                        class="icon-btn text-indigo-500 hover:text-indigo-600">
                        <i class="fas fa-edit text-lg"></i>
                        <span class="tooltip">Update</span>
                    </button>
                    <button onclick="deleteCategory(' . $row['id'] . ')"
                        class="icon-btn text-red-500 hover:text-red-600">
                        <i class="fas fa-trash text-lg"></i>
                        <span class="tooltip">Delete</span>
                    </button>
                </div>
            </div>
        </div>';
}
$output .= '</div>';

// Generate the HTML for pagination controls
$output .= '<div class="flex justify-between items-center mt-6">';
if ($current_page > 1) {
    $output .= '<a href="javascript:void(0);" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400" 
                   onclick="loadCategories(' . ($current_page - 1) . ')">
                    Previous
                </a>';
} else {
    $output .= '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Previous</span>';
}

$output .= '<span class="text-gray-700">Page ' . $current_page . ' of ' . $total_pages . '</span>';

if ($current_page < $total_pages) {
    $output .= '<a href="javascript:void(0);" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400" 
                   onclick="loadCategories(' . ($current_page + 1) . ')">
                    Next
                </a>';
} else {
    $output .= '<span class="px-4 py-2 bg-gray-200 text-gray-500 rounded">Next</span>';
}
$output .= '</div>';

// Return the generated HTML
echo $output;
?>