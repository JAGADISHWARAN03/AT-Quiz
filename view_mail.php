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

// Generate quiz link
$quiz_link = generateQuizLink($skill); // Custom function to generate quiz link

imap_close($inbox);

function extractSkillFromBody($body) {
    if (strpos($body, 'Python') !== false) {
        return 'Python';
    } elseif (strpos($body, 'JavaScript') !== false) {
        return 'JavaScript';
    } elseif (strpos($body, 'PHP') !== false) {
        return 'PHP';
    }
    return 'General';
}

function generateQuizLink($skill) {
    return "Quiz_page.php?category=" . urlencode($skill);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Mail</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <div class="bg-white p-6 shadow-md rounded-lg">
            <h2 class="text-2xl font-extrabold text-purple-700 mb-4"><?= htmlspecialchars($overview[0]->subject) ?></h2>
            <p class="text-sm text-gray-500 mb-2">From: <?= htmlspecialchars($overview[0]->from) ?></p>
            <div class="text-gray-800">
                <p><?= nl2br(htmlspecialchars($message)) ?></p>
                <a href="<?= $quiz_link ?>" class="text-blue-500 underline mt-4 block">Take the <?= htmlspecialchars($skill) ?> Quiz</a>
                <button onclick="replyToEmail(<?= $email_id ?>)" class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded mt-4">Reply</button>
            </div>
        </div>
    </div>
</body>
</html>