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

<div class="max-w-7xl mx-auto mt-10">
    <h2 class="text-3xl font-bold text-center mb-6 text-indigo-700">Quizzes for Selected Category</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed border border-gray-300 shadow-md rounded-lg text-sm">
            <thead>
                <tr class="bg-indigo-100 text-indigo-800">
                    <th class="p-3 border text-left w-[12%]">Title</th>
                    <th class="p-3 border text-left w-[20%]">Description</th>
                    <th class="p-3 border text-center w-[8%]">Timer</th>
                    <th class="p-3 border text-center w-[8%]">Status</th>
                    <th class="p-3 border text-center w-[12%]">View Questions</th>
                    <th class="p-3 border text-center w-[8%]">Edit</th>
                    <th class="p-3 border text-center w-[8%]">Delete</th>
                   
                    <th class="p-3 border text-center w-[12%]">Generate Link</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="bg-white hover:bg-gray-50 transition" data-quiz-id="<?= $row['id'] ?>">
                            <td class="p-3 border text-gray-800 truncate"><?= htmlspecialchars($row['title']) ?></td>
                            <td class="p-3 border text-gray-800 break-words"><?= htmlspecialchars($row['description']) ?></td>
                            <td class="p-3 border text-center"><?= htmlspecialchars($row['timer']) ?> min</td>
                            <td class="p-3 border text-center">
                                <button id="on-btn-<?= $row['id'] ?>" class="px-3 py-1 rounded text-white bg-green-500 hover:bg-green-600 <?= $row['status'] ? '' : 'hidden' ?>" onclick="toggleQuizStatus(<?= $row['id'] ?>, 0)">On</button>
                                <button id="off-btn-<?= $row['id'] ?>" class="px-3 py-1 rounded text-white bg-red-500 hover:bg-red-600 <?= $row['status'] ? 'hidden' : '' ?>" onclick="toggleQuizStatus(<?= $row['id'] ?>, 1)">Off</button>
                            </td>
                            <td class="p-3 border text-center">
                                <a href="javascript:void(0);" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 whitespace-nowrap" onclick="loadQuizzes1(<?= $row['id'] ?>)">View Questions</a>
                            </td>
                            <td class="p-3 border text-center">
                                <a href="javascript:void(0);" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600" onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['title']) ?>', '<?= htmlspecialchars($row['description']) ?>', <?= $row['timer'] ?>)">Edit</a>
                            </td>
                            <td class="p-3 border text-center">
                                <a href="javascript:void(0);" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" onclick="deleteQuiz(<?= $row['id'] ?>)">Delete</a>
                            </td>
                           
                            <td class="p-3 border text-center">
                                <button onclick="generateQuizLink(<?= $row['id'] ?>)" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 whitespace-nowrap">Generate Link</button>
                                <div id="quiz-link-<?= $row['id'] ?>" class="text-blue-600 text-xs mt-1 hidden"></div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center p-4 text-gray-500">No quizzes found for this category.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
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
// Add your JavaScript functions here like generateQuizLink(), toggleQuizStatus(), etc.
</script>
