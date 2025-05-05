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
        <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col justify-between">
            <h3 class="text-xl font-semibold mb-2">' . htmlspecialchars($row['name']) . '</h3>
            <div class="flex justify-between items-center">
                <a href="javascript:void(0);" 
                   class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600" 
                   onclick="loadQuizzes(' . $row['id'] . ')">
                    View Quizzes
                </a>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" 
                           onchange="toggleCategory(' . $row['id'] . ')" 
                           ' . ($row['status'] ? 'checked' : '') . '>
                    <div class="w-11 h-6 bg-gray-300 peer-focus:ring-4 rounded-full 
                                peer-checked:bg-green-500 peer-checked:ring-green-300 
                                peer:bg-red-500 peer:ring-red-300"></div>
                </label>
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