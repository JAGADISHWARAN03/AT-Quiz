<?php
include 'includes/config.php'; // Include database connection

if (isset($_GET['id'])) {
    $quiz_id = (int)$_GET['id'];

    // Fetch quiz details
    $stmt = $conn->prepare("SELECT title, description, timer FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $quiz = $result->fetch_assoc();
    } else {
        echo '<p class="text-center text-red-500">Quiz not found.</p>';
        exit;
    }
} else {
    echo '<p class="text-center text-red-500">Invalid quiz ID.</p>';
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $timer = (int)$_POST['timer'];

    $stmt = $conn->prepare("UPDATE quizzes SET title = ?, description = ?, timer = ? WHERE id = ?");
    $stmt->bind_param("ssii", $title, $description, $timer, $quiz_id);

    if ($stmt->execute()) {
        echo '<p class="text-center text-green-500">Quiz updated successfully!</p>';
    } else {
        echo '<p class="text-center text-red-500">Failed to update quiz: ' . $stmt->error . '</p>';
    }

    $stmt->close();
}
?>

<form method="POST" class="max-w-lg mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-4">Edit Quiz</h2>
    <div class="mb-4">
        <label class="block font-medium">Title:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($quiz['title']) ?>" class="w-full p-2 border rounded" required>
    </div>
    <div class="mb-4">
        <label class="block font-medium">Description:</label>
        <textarea name="description" class="w-full p-2 border rounded" required><?= htmlspecialchars($quiz['description']) ?></textarea>
    </div>
    <div class="mb-4">
        <label class="block font-medium">Timer (in minutes):</label>
        <input type="number" name="timer" value="<?= htmlspecialchars($quiz['timer']) ?>" class="w-full p-2 border rounded" required>
    </div>
    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update Quiz</button>
</form>