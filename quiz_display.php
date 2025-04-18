<?php include 'includes/config.php'; // Database connection

// Fetch all quiz categories for the dropdown
$categoryOptions = "";
$categoriesResult = $conn->query("SELECT id, name FROM quiz_categories");
while ($row = $categoriesResult->fetch_assoc()) {
    $selected = (isset($_GET['category_id']) && $_GET['category_id'] == $row['id']) ? "selected" : "";
    $categoryOptions .= "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
}

// Get the selected category ID
$category_id = $_GET['category_id'] ?? "";

// Pagination variables
$page = $_GET['page'] ?? 1;
$limit = 5; // Number of rows per page
$offset = ($page - 1) * $limit;

// Fetch questions for the selected category with pagination
$query = "SELECT * FROM questions WHERE quiz_category = ? LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $category_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total questions for pagination
$total_query = "SELECT COUNT(*) AS total FROM questions WHERE quiz_category = ?";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param("s", $category_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_questions = $total_row['total'];
$total_pages = ceil($total_questions / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questions Display</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
</head>
<?php include 'includes/header.php'; // Updated path to the header file ?>
<body class="bg-gray-50">
    <div class="max-w-4xl mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-4">Questions in Category</h2>

        <!-- Filter Dropdown -->
        <form method="GET" class="mb-4">
            <label for="category_id" class="block font-medium mb-2">Filter by Category:</label>
            <select name="category_id" id="category_id" class="w-full p-2 border rounded" onchange="this.form.submit()">
                <option value="">Select a Category</option>
                <?= $categoryOptions; ?>
            </select>
        </form>
        <div class="flex space-x-4 mb-4">
            <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" 
                    onclick="editCategory()">Edit Category</button>
            <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600" 
                    onclick="deleteCategory()">Delete Category</button>
        </div>

        <!-- Questions Table -->
        <table class="w-full border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-200 p-2 text-left">Question</th>
                    <th class="border border-gray-200 p-2 text-left">Option 1</th>
                    <th class="border border-gray-200 p-2 text-left">Option 2</th>
                    <th class="border border-gray-200 p-2 text-left">Option 3</th>
                    <th class="border border-gray-200 p-2 text-left">Option 4</th>
                    <th class="border border-gray-200 p-2 text-left">Correct Option</th>
                    <th class="border border-gray-200 p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['question_text']) ?></td>
                            <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['option_1']) ?></td>
                            <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['option_2']) ?></td>
                            <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['option_3']) ?></td>
                            <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['option_4']) ?></td>
                            <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['correct_option']) ?></td>
                            <td class="border border-gray-200 p-2">
                                <button class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600" 
                                        onclick="editQuestion(<?= $row['id'] ?>)">Edit</button>
                                <button class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600" 
                                        onclick="deleteQuestion(<?= $row['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="border border-gray-200 p-2 text-center text-red-500">No questions found for the selected category.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4 flex justify-between">
            <a href="?category_id=<?= $category_id ?>&page=<?= max(1, $page - 1) ?>" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>">Previous</a>
            <a href="?category_id=<?= $category_id ?>&page=<?= min($total_pages, $page + 1) ?>" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300 <?= $page >= $total_pages ? 'pointer-events-none opacity-50' : '' ?>">Next</a>
        </div>
    </div>

    <!-- Edit Question Modal -->
    <div id="edit-question-modal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded shadow-lg">
            <h2 class="text-xl font-bold mb-4">Edit Question</h2>
            <form id="edit-question-form">
                <input type="hidden" id="edit-question-id" name="id">
                <textarea id="edit-question-text" name="question_text" class="w-full p-2 border rounded mb-4" required></textarea>
                <input type="text" id="edit-option-1" name="option_1" class="w-full p-2 border rounded mb-4" required>
                <input type="text" id="edit-option-2" name="option_2" class="w-full p-2 border rounded mb-4" required>
                <input type="text" id="edit-option-3" name="option_3" class="w-full p-2 border rounded mb-4" required>
                <input type="text" id="edit-option-4" name="option_4" class="w-full p-2 border rounded mb-4" required>
                <input type="text" id="edit-correct-option" name="correct_option" class="w-full p-2 border rounded mb-4" required>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save</button>
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" 
                        onclick="document.getElementById('edit-question-modal').classList.add('hidden')">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="edit-category-modal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded shadow-lg">
            <h2 class="text-xl font-bold mb-4">Edit Category</h2>
            <form id="edit-category-form">
                <input type="hidden" id="edit-category-id" name="id">
                <input type="text" id="edit-category-name" name="name" class="w-full p-2 border rounded mb-4" required>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save</button>
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" 
                        onclick="document.getElementById('edit-category-modal').classList.add('hidden')">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        // Edit Question
        function editQuestion(questionId) {
            fetch(`get_question.php?id=${questionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate the modal with question data
                        document.getElementById('edit-question-id').value = data.question.id;
                        document.getElementById('edit-question-text').value = data.question.question_text;
                        document.getElementById('edit-option-1').value = data.question.option_1;
                        document.getElementById('edit-option-2').value = data.question.option_2;
                        document.getElementById('edit-option-3').value = data.question.option_3;
                        document.getElementById('edit-option-4').value = data.question.option_4;
                        document.getElementById('edit-correct-option').value = data.question.correct_option;

                        // Show the modal
                        document.getElementById('edit-question-modal').classList.remove('hidden');
                    } else {
                        alert('Failed to fetch question details.');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Save Edited Question
        document.getElementById('edit-question-form').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('update_question.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Question updated successfully!');
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Failed to update question.');
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        // Delete Question
        function deleteQuestion(questionId) {
            console.log('Deleting question with ID:', questionId); // Debugging log
            if (confirm('Are you sure you want to delete this question?')) {
                fetch('delete_question.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${questionId}&_method=DELETE`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Question deleted successfully!');
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert('Failed to delete question: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

        // Edit Category
        function editCategory() {
            const categoryId = document.getElementById('category_id').value;
            if (!categoryId) {
                alert('Please select a category to edit.');
                return;
            }

            fetch(`get_category.php?id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate the modal with category data
                        document.getElementById('edit-category-id').value = data.category.id;
                        document.getElementById('edit-category-name').value = data.category.name;

                        // Show the modal
                        document.getElementById('edit-category-modal').classList.remove('hidden');
                    } else {
                        alert('Failed to fetch category details.');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Delete Category
        function deleteCategory() {
            const categoryId = document.getElementById('category_id').value;
            if (!categoryId) {
                alert('Please select a category to delete.');
                return;
            }

            if (confirm('Are you sure you want to delete this category?')) {
                fetch('delete_category.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${categoryId}&_method=DELETE`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Category deleted successfully!');
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert('Failed to delete category: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    </script>
</body>
</html>
<?php include 'includes/footer.php'; // Updated path to the footer file ?>
