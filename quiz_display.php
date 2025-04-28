<?php
include 'includes/config.php'; // Include database connection

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($category_id > 0) {
    $stmt = $conn->prepare("SELECT id, title, description, timer, status FROM quizzes WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo '<p class="text-center text-red-500">Invalid category ID.</p>';
    exit;
}
?>

<div class="max-w-6xl mx-auto mt-10 data">
    <h2 class="text-3xl font-bold text-center mb-6">Quizzes for Selected Category</h2>
    <table class="w-full border-collapse border border-gray-200">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-200 p-2 text-left">Title</th>
                <th class="border border-gray-200 p-2 text-left">Description</th>
                <th class="border border-gray-200 p-2 text-left">Timer</th>
                <th class="border border-gray-200 p-2 text-left">Status</th>
                <th class="border border-gray-200 p-2 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['title']) ?></td>
                        <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['description']) ?></td>
                        <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['timer']) ?> minutes</td>
                        <td class="border border-gray-200 p-2">
                            <?= $row['status'] ? '<span class="text-green-500">Active</span>' : '<span class="text-red-500">Inactive</span>' ?>
                        </td>
                        <td class="border border-gray-200 p-2">
                        <a href="javascript:void(0);" 
                        class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600" 
                                       onclick="loadQuizzes1(<?= $row['id'] ?>)">
                                       View Questions</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center p-4">No quizzes found for this category.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- <script>
    function loadQuizzes1(quizId) {
        const questions_containern = document.getElementById('questions-containern');
        if(quizId){
            fetch(`view_questions.php?quiz_id=${quizId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('questions-containern').innerHTML = data;
                document.getElementById('data').classList.add('hidden'); // Hide categories section
                questions_containern.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error loading quizzes:', error);
            });
        } else {
        // quizDisplayContent.innerHTML = '<p class="text-center text-red-500">Invalid category selected.</p>';
    }
        
    }
</script> -->