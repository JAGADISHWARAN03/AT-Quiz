<?php
session_start();
include 'includes/config.php'; // Include database connection

if (!isset($_SESSION['user_id']) || !isset($_SESSION['quiz_title_id'])) {
    header("Location: welcome.php");
    exit;
}

$quiz_title_id = $_SESSION['quiz_title_id'];

// Fetch questions for the selected quiz_title_id
$stmt = $conn->prepare("
    SELECT id, question_text, option_1, option_2, option_3, option_4 
    FROM questions 
    WHERE quiz_title_id = ?
");
$stmt->bind_param("i", $quiz_title_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

$stmt->close();

if (empty($questions)) {
    echo '<p class="text-center text-red-500">No questions found for this quiz.</p>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Questions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4">Quiz Questions</h1>
        <form method="POST" action="submit_quiz.php">
            <?php foreach ($questions as $index => $question): ?>
                <div class="mb-6">
                    <p class="font-medium"><?= ($index + 1) . ". " . htmlspecialchars($question['question_text']) ?></p>
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <label class="block mt-2">
                            <input type="radio" name="answers[<?= $question['id'] ?>]" value="<?= $i ?>" class="form-radio text-blue-600">
                            <?= htmlspecialchars($question["option_$i"]) ?>
                        </label>
                    <?php endfor; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit Quiz</button>
        </form>
    </div>
</body>
</html>