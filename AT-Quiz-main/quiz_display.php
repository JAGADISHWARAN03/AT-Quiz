<?php
// filepath: c:\xampp\htdocs\htdocs\AT-Quiz-main\quiz_display.php

include 'includes/db.php';
include 'includes/header.php';

// Fetch quizzes from the database
$quizzes_result = $db->query("SELECT * FROM quizzes");

?>

<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Available Quizzes</h1>

    <?php if ($quizzes_result->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($quiz = $quizzes_result->fetch_assoc()): ?>
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($quiz['title']) ?></h2>
                    <p class="mb-4"><?= htmlspecialchars($quiz['description']) ?></p>
                    <a href="take_quiz.php?quiz_id=<?= $quiz['id'] ?>" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Take Quiz
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-center">No quizzes available at the moment.</p>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>