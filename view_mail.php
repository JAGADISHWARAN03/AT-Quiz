<?php
$hostname = "{imap.gmail.com:993/imap/ssl}INBOX";
$username = 'jagadishbit0@gmail.com';
$password = 'ughe ebfb ewky gqep';

$email_id = isset($_GET['email_id']) ? (int)$_GET['email_id'] : 0;

$inbox = imap_open($hostname, $username, $password) or die('Cannot connect to IMAP: ' . imap_last_error());
$overview = imap_fetch_overview($inbox, $email_id, 0);
$message = imap_fetchbody($inbox, $email_id, 1);

// Extract skill from email body
$skill = extractSkillFromBody($message); // Custom function to extract skill

// Fetch quiz titles based on the category (skill)
$quiz_titles = [];
$conn = new mysqli('localhost', 'root', '', 'quiz_system'); // Update with your database credentials

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$stmt = $conn->prepare("
    SELECT quizzes.*
    FROM quizzes
    INNER JOIN quiz_categories ON quizzes.category_id = quiz_categories.id
    WHERE quiz_categories.name = ?
");

$stmt->bind_param("s", $skill);
$stmt->execute();
$result = $stmt->get_result();

$quiz_titles = []; // Initialize the array

while ($row = $result->fetch_assoc()) {
    $quiz_titles[] = $row;
}

// Output nicely
// echo '<pre>';
// print_r($quiz_titles);
// echo '</pre>';
// exit;

$stmt->close();
$conn->close();


imap_close($inbox);

function extractSkillFromBody($body) {
    if (strpos($body, 'Python') !== false) {
        return 'Python';
    } elseif (strpos($body, 'JavaScript') !== false) {
        return 'JavaScript';
    } elseif (strpos($body, 'PHP') !== false) {
        return 'PHP';
    }
    elseif (strpos($body, 'DotNet') !== false) {
        return 'Dotnet';
    } elseif (strpos($body, 'Java') !== false) {
        return 'Java';
    } elseif (strpos($body, 'ReactJS') !== false) {
        return 'ReactJS';
    }

    return 'General';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Mail</title>
    <link rel="stylesheet" href="assets/styles.css"> 
    <script src="https://cdn.tailwindcss.com"></script>
  
    <script>
        function showLoadingSpinner() {
            document.getElementById('loading-spinner').classList.remove('hidden');
        }

        function replyToEmail(emailId) {
            showLoadingSpinner();
            window.location.href = `reply_mail.php?email_id=${emailId}`;
        }

        function takeQuiz() {
            const quizId = document.getElementById('quiz-title').value;

            if (quizId) {
                window.location.href = `Quiz_page.php?quiz_id=${quizId}`;
            } else {
                alert('Please select a quiz title.');
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <!-- Loading Spinner -->
    <div id="loading-spinner" class="fixed inset-0 flex items-center justify-center bg-gray-100 bg-opacity-75 hidden">
        <div class="flex flex-col items-center">
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-blue-500 h-12 w-12 mb-4"></div>
            <p class="text-blue-700 font-semibold">Processing your request...</p>
        </div>
    </div>

    <!-- Response Message -->
    <div id="response-message" class="hidden text-center p-4 rounded-lg"></div>

    <!-- Main Content -->

    <div class="container mx-auto py-8">
        <div class="bg-white p-6 shadow-md rounded-lg">
            <h2 class="text-2xl font-extrabold text-purple-700 mb-4"><?= htmlspecialchars($overview[0]->subject) ?></h2>
            <p class="text-sm text-gray-500 mb-2">From: <?= htmlspecialchars($overview[0]->from) ?></p>
            <div class="text-gray-800">
                <p><?= nl2br(htmlspecialchars($message)) ?></p>

                <!-- Dropdown for Quiz Titles -->
                <label for="quiz-title" class="block text-gray-700 font-semibold mt-4">Select a Quiz Title:</label>
                <select id="quiz-title" class="w-full p-2 border rounded mt-2">
                    <?php if (!empty($quiz_titles)): ?>
                        <?php foreach ($quiz_titles as $quiz): ?>
                            <option value="<?= htmlspecialchars($quiz['id']) ?>"><?= htmlspecialchars($quiz['title']) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No quizzes available for this category</option>
                    <?php endif; ?>
                </select>

                <!-- Take Quiz Button -->
                <button onclick="takeQuiz()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mt-4">Take Quiz</button>
                <button onclick="replyToEmail(<?= $email_id ?>)" class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded mt-4">Reply</button>
            </div>
        </div>
    </div>
</body>
</html>