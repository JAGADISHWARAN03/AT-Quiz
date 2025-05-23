<?php
require 'includes/config.php'; // Include database connection

// Fetch filter values
$city = isset($_GET['city']) ? $_GET['city'] : '';
$skill = isset($_GET['skill']) ? $_GET['skill'] : '';
$exam_date = isset($_GET['exam_date']) ? $_GET['exam_date'] : '';

// Build query with filters
$query = "
    SELECT 
        qr.user_id, 
        qr.user_email, 
        qc.name AS category_name, 
        SUM(qr.score) AS total_score, 
        SUM(qr.total_questions) AS total_questions,
        COUNT(qr.id) AS attempts,
        qr.city, 
        qr.skill,
        DATE(qr.created_at) AS exam_date,
        (SUM(qr.score) / SUM(qr.total_questions)) * 100 AS percentage
    FROM quiz_results1 qr
    JOIN quiz_categories qc ON qr.category_id = qc.id
    WHERE 1=1
";

if ($city) {
    $query .= " AND qr.city = '$city'";
}
if ($skill) {
    $query .= " AND qr.skill = '$skill'";
}
if ($exam_date) {
    $query .= " AND DATE(qr.created_at) = '$exam_date'";
}

$query .= " GROUP BY qr.user_id, qr.user_email, qr.category_id, qr.city, qr.skill, DATE(qr.created_at)
            ORDER BY qc.name, qr.user_email";

$result = $conn->query($query);

// Fetch distinct cities and skills for dropdowns from quiz_results1
$cities = $conn->query("SELECT DISTINCT city FROM quiz_results1 WHERE city IS NOT NULL AND city != ''")->fetch_all(MYSQLI_ASSOC);
$skills = $conn->query("SELECT DISTINCT skill FROM quiz_results1 WHERE skill IS NOT NULL AND skill != ''")->fetch_all(MYSQLI_ASSOC);
?>

<form id="results-filter-form" method="GET" class="bg-white p-6 rounded-lg shadow mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- City Filter -->
        <div>
            <label for="city" class="block text-gray-700 font-medium mb-2">Filter by City</label>
            <select name="city" id="city" class="w-full border-gray-300 rounded-lg">
                <option value="">All Cities</option>
                <?php foreach ($cities as $city_option): ?>
                    <option value="<?= htmlspecialchars($city_option['city']) ?>" <?= $city === $city_option['city'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($city_option['city']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Skill Filter -->
        <div>
            <label for="skill" class="block text-gray-700 font-medium mb-2">Filter by Skill</label>
            <select name="skill" id="skill" class="w-full border-gray-300 rounded-lg">
                <option value="">All Skills</option>
                <?php foreach ($skills as $skill_option): ?>
                    <option value="<?= htmlspecialchars($skill_option['skill']) ?>" <?= $skill === $skill_option['skill'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($skill_option['skill']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Exam Date Filter -->
        <div>
            <label for="exam_date" class="block text-gray-700 font-medium mb-2">Filter by Exam Date</label>
            <input type="date" name="exam_date" id="exam_date" value="<?= htmlspecialchars($exam_date) ?>" class="w-full border-gray-300 rounded-lg">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Apply Filters</button>
        <button type="button" id="clear-filters" class="ml-4 text-blue-500 hover:underline bg-transparent">Clear Filters</button>
    </div>
</form>

<script>
document.getElementById('clear-filters').addEventListener('click', function() {
    const form = document.getElementById('results-filter-form');
    form.reset();
    // No AJAX, no redirect, just clear the fields
});
</script>