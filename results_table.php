<?php
require 'includes/config.php'; // Include database connection

// Get filter values from GET
$city = isset($_GET['city']) ? $_GET['city'] : '';
$skill = isset($_GET['skill']) ? $_GET['skill'] : '';
$exam_date = isset($_GET['exam_date']) ? $_GET['exam_date'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 7;
$offset = ($page - 1) * $per_page;

// Build query with filters
$where = "WHERE 1=1";
if ($city) {
    $where .= " AND qr.city = '$city'";
}
if ($skill) {
    $where .= " AND qr.skill = '$skill'";
}
if ($exam_date) {
    $where .= " AND DATE(qr.created_at) = '$exam_date'";
}

// Get total count for pagination
$count_query = "
    SELECT COUNT(*) as total
    FROM quiz_results1 qr
    JOIN quiz_categories qc ON qr.category_id = qc.id
    $where
";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

// Main data query with LIMIT
$query = "
    SELECT 
        qr.user_email, 
        qr.city, 
        qr.skill, 
        qc.name AS category_name, 
        qr.score AS total_score, 
        qr.total_questions, 
        (qr.score / qr.total_questions) * 100 AS percentage, 
        qr.created_at AS exam_date
    FROM 
        quiz_results1 qr
    JOIN 
        quiz_categories qc ON qr.category_id = qc.id
    $where
    ORDER BY qr.created_at DESC
    LIMIT $per_page OFFSET $offset
";
$result = $conn->query($query);
?>

<div id="results-table-container" class="bg-white shadow-lg p-6 rounded-lg">
    <h2 class="text-2xl font-bold mb-4">Results</h2>
    <table class="min-w-full border">
        <thead>
            <tr class="border-b">
                <th class="text-left p-2">User Email</th>
                <th class="text-left p-2">City</th>
                <th class="text-left p-2">Skill</th>
                <th class="text-left p-2">Category</th>
                <th class="text-left p-2">Total Score</th>
                <th class="text-left p-2">Total Questions</th>
                <th class="text-left p-2">Percentage</th>
                <th class="text-left p-2">Exam Date</th>
                <th class="text-left p-2">Pass/Fail</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-2"><?= htmlspecialchars($row['user_email']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['city']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['skill']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['category_name']) ?></td>
                        <td class="p-2"><?= $row['total_score'] ?></td>
                        <td class="p-2"><?= $row['total_questions'] ?></td>
                        <td class="p-2"><?= round($row['percentage'], 2) ?>%</td>
                        <td class="p-2"><?= htmlspecialchars($row['exam_date']) ?></td>
                        <td class="p-2">
                            <?php if ($row['percentage'] >= 50): ?>
                                <span class="text-green-500 font-bold">Pass</span>
                            <?php else: ?>
                                <span class="text-red-500 font-bold">Fail</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center text-gray-500 p-4">No results found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <div class="flex justify-between items-center mt-4">
        <button 
            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 <?= $page <= 1 ? 'opacity-50 cursor-not-allowed' : '' ?>" 
            <?= $page <= 1 ? 'disabled' : '' ?>
            onclick="loadResultsPage(<?= $page - 1 ?>)">
            Previous
        </button>
        <span class="mx-2 text-gray-700">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <button 
                class="mx-1 px-2 py-1 rounded text-sm <?= $i == $page ? 'bg-blue-600 text-white font-bold' : 'bg-gray-100 text-gray-700' ?>"
                onclick="loadResultsPage(<?= $i ?>)">
                <?= $i ?>
            </button>
            <?php endfor; ?>
        </span>
        <button 
            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 <?= $page >= $total_pages ? 'opacity-50 cursor-not-allowed' : '' ?>" 
            <?= $page >= $total_pages ? 'disabled' : '' ?>
            onclick="loadResultsPage(<?= $page + 1 ?>)">
            Next
        </button>
    </div>
</div>

<script>
// This function should be available globally in your dashboard page
function loadResultsPage(page) {
    // Collect current filters
    const form = document.getElementById('results-filter-form');
    const formData = new FormData(form);
    formData.set('page', page);
    const params = new URLSearchParams(formData).toString();

    // Show loading
    const tableContainer = document.getElementById('results-table-container');
    tableContainer.innerHTML = '<p class="text-center text-gray-500">Loading...</p>';

    fetch('results_table.php?' + params)
        .then(res => res.text())
        .then(html => {
            tableContainer.innerHTML = html;
        })
        .catch(() => {
            tableContainer.innerHTML = '<p class="text-center text-red-500">Failed to load results.</p>';
        });
}
</script>