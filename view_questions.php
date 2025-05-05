<?php
include 'includes/config.php'; // Include database connection

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// Fetch questions for the selected quiz
$stmt = $conn->prepare("SELECT id, question_text, question_type, option_1, option_2, option_3, option_4, correct_option FROM questions WHERE quiz_title_id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="max-w-6xl mx-auto mt-10">
    <h2 class="text-3xl font-bold text-center mb-6">Questions for Selected Quiz</h2>

    <!-- Questions Table -->

    <table class="w-full border-collapse border border-gray-200">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-200 p-2 text-left">Question</th>
                <th class="border border-gray-200 p-2 text-left">Options</th>
                <th class="border border-gray-200 p-2 text-left">Correct Answer</th>
                <th class="border border-gray-200 p-2 text-left">Type</th>
                <th class="border border-gray-200 p-2 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-id="<?= $row['id'] ?>">
                        <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['question_text']) ?></td>
                        <td class="border border-gray-200 p-2">
                            <ul class="list-disc pl-4">
                                <li><?= htmlspecialchars($row['option_1']) ?></li>
                                <li><?= htmlspecialchars($row['option_2']) ?></li>
                                <li><?= htmlspecialchars($row['option_3']) ?></li>
                                <li><?= htmlspecialchars($row['option_4']) ?></li>
                            </ul>
                        </td>
                        <td class="border border-gray-200 p-2">
                            <?php
                            if ($row['question_type'] === 'radio' || $row['question_type'] === 'checkbox') {
                                $correct_option = json_decode($row['correct_option'], true);
                                if (is_array($correct_option)) {
                                    foreach ($correct_option as $option) {
                                        echo "Option $option<br>";
                                    }
                                } else {
                                    echo "Option " . htmlspecialchars($correct_option);
                                }
                            } elseif ($row['question_type'] === 'text') {
                                echo htmlspecialchars($row['correct_option']);
                            }
                            ?>
                        </td>
                        <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['question_type']) ?></td>
                        <td class="border border-gray-200 p-2">
                            <!-- Edit Button -->
                            <a href="javascript:void(0);"
                               class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600"
                               onclick="openEditModal1 (<?= $row['id'] ?>, '<?= htmlspecialchars($row['question_text']) ?>', '<?= htmlspecialchars($row['question_type']) ?>', '<?= htmlspecialchars($row['option_1']) ?>', '<?= htmlspecialchars($row['option_2']) ?>', '<?= htmlspecialchars($row['option_3']) ?>', '<?= htmlspecialchars($row['option_4']) ?>', '<?= htmlspecialchars($row['correct_option']) ?>')">
                               Edit
                            </a>
                            <!-- Delete Button -->
                            <a href="javascript:void(0);"
                               class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600"
                               onclick="deleteQuestion(<?= $row['id'] ?>)">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="border border-gray-200 p-2 text-center text-red-500">No questions found for this quiz.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div id="edit-question-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-xl font-bold mb-4">Edit Question</h2>
        <form id="edit-form" onsubmit="editQuestion(event)">
            <input type="hidden" id="edit-question-id" name="question_id">
            <div class="mb-4">
                <label for="edit-question-text" class="block font-medium">Question:</label>
                <textarea id="edit-question-text" name="question_text" class="w-full p-2 border rounded" required></textarea>
            </div>
            <div class="mb-4">
                <label for="edit-question-type" class="block font-medium">Type:</label>
                <select id="edit-question-type" name="question_type" class="w-full p-2 border rounded" required>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="text">Text</option>
                </select>
            </div>
            <div id="edit-options" class="mb-4">
                <label class="block font-medium">Options:</label>
                <input type="text" id="edit-option-1" name="option_1" class="w-full p-2 border rounded mb-2" placeholder="Option 1">
                <input type="text" id="edit-option-2" name="option_2" class="w-full p-2 border rounded mb-2" placeholder="Option 2">
                <input type="text" id="edit-option-3" name="option_3" class="w-full p-2 border rounded mb-2" placeholder="Option 3">
                <input type="text" id="edit-option-4" name="option_4" class="w-full p-2 border rounded mb-2" placeholder="Option 4">
            </div>
            <div class="mb-4">
                <label for="edit-correct-option" class="block font-medium">Correct Option:</label>
                <input type="text" id="edit-correct-option" name="correct_option" class="w-full p-2 border rounded">
            </div>
            <div class="flex justify-end">
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2" onclick="closeEditModal1()">Cancel</button>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update</button>
            </div>
        </form>
    </div>
</div>


