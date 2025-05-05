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
                <th class="border border-gray-200 p-2 text-left">Status</th> <!-- New Status Column -->
                <th class="border border-gray-200 p-2 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-id="<?= $row['id'] ?>">
                        <td class="border border-gray-200 p-2 quiz-title"><?= htmlspecialchars($row['title']) ?></td>
                        <td class="border border-gray-200 p-2 quiz-description"><?= htmlspecialchars($row['description']) ?></td>
                        <td class="border border-gray-200 p-2 quiz-timer"><?= htmlspecialchars($row['timer']) ?> minutes</td>
                        <td class="border border-gray-200 p-2">
                            <!-- Status Buttons -->
                            <button
                                id="on-btn-<?= $row['id'] ?>"
                                class="px-4 py-2 rounded text-white bg-green-500 hover:bg-green-600 <?= $row['status'] ? '' : 'hidden' ?>"
                                onclick="toggleQuizStatus(<?= $row['id'] ?>, 0)">
                                On
                            </button>
                            <button
                                id="off-btn-<?= $row['id'] ?>"
                                class="px-4 py-2 rounded text-white bg-red-500 hover:bg-red-600 <?= $row['status'] ? 'hidden' : '' ?>"
                                onclick="toggleQuizStatus(<?= $row['id'] ?>, 1)">
                                Off
                            </button>
                        </td>
                        <td class="border border-gray-200 p-2">
                            <a href="javascript:void(0);"
                                class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600"
                                onclick="loadQuizzes1(<?= $row['id'] ?>)">
                                View Questions
                            </a>
                        </td>
                        <!-- <td class="quiz-title"><?= htmlspecialchars($row['title']) ?></td>
                        <td class="quiz-description"><?= htmlspecialchars($row['description']) ?></td>
                        <td class="quiz-timer"><?= htmlspecialchars($row['timer']) ?> minutes</td> -->
                        
                        <td class="border border-gray-200 p-2">
                            <a href="javascript:void(0);"
                                class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600"
                                onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['title']) ?>', '<?= htmlspecialchars($row['description']) ?>', <?= $row['timer'] ?>)">
                                Edit
                            </a>
                        </td>
                        <td class="border border-gray-200 p-2">
                            <!-- Delete Button -->
                            <a href="javascript:void(0);"
                                class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600"
                                onclick="deleteQuiz(<?= $row['id'] ?>)">
                                Delete
                            </a>
                        </td>
                        <td class="border border-gray-200 p-2">
                            <!-- Generate Quiz Link -->
                            <a href="javascript:void(0);"
                                class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600"
                                onclick="generateQuizLink(<?= $row['id'] ?>)">
                                Generate Link
                            </a>
                            <span id="quiz-link-<?= $row['id'] ?>" class="ml-2 text-blue-500 underline hidden"></span>
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
<div id="edit-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-xl font-bold mb-4">Edit Quiz</h2>
        <form id="edit-form" action="dashboard.php" onsubmit="edit_form_submit(event)">
            <input type="hidden" id="edit-quiz-id" name="quiz_id">
            <div class="mb-4">
                <label for="edit-title" class="block font-medium">Title:</label>
                <input type="text" id="edit-title" name="title" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label for="edit-description" class="block font-medium">Description:</label>
                <textarea id="edit-description" name="description" class="w-full p-2 border rounded" required></textarea>
            </div>
            <div class="mb-4">
                <label for="edit-timer" class="block font-medium">Timer (in minutes):</label>
                <input type="number" id="edit-timer" name="timer" class="w-full p-2 border rounded" required>
            </div>
            <div class="flex justify-end">
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update</button>
            </div>
        </form>
    </div>
</div>
<script>


</script>