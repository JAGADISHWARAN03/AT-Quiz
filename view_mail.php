<?php
// IMAP Configuration
$hostname = "{imap.gmail.com:993/imap/ssl}INBOX"; // Replace with your IMAP server
$username = 'jagadishbit0@gmail.com'; // Replace with your email
$password = 'ughe ebfb ewky gqep'; // Replace with your email password

// Get the email ID from the query string
$email_id = isset($_GET['email_id']) ? intval($_GET['email_id']) : 0;

// Connect to IMAP
$inbox = imap_open($hostname, $username, $password) or die('Cannot connect to IMAP: ' . imap_last_error());

// Fetch the email content
$overview = imap_fetch_overview($inbox, $email_id, 0);
$message = imap_fetchbody($inbox, $email_id, 1);

// Close IMAP connection
imap_close($inbox);
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
            <h2 class="text-2xl font-semibold mb-2 text-gray-800"><?php echo htmlspecialchars($overview[0]->subject); ?></h2>
            <p class="text-sm text-gray-500 mb-4">From: <?php echo htmlspecialchars($overview[0]->from); ?></p>
            <div class="text-gray-800 space-y-4">
                <p><?php echo nl2br(htmlspecialchars($message)); ?></p>
            </div>
            <?php
            echo '<a href="view_mail.php?email_id=' . $email_number . '" class="text-blue-500 hover:underline">View</a>';
            echo '<p class="text-gray-600 p-4">No application-related emails found.</p>';
            ?>
        </div>
    </div>
</body>
</html>