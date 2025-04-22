<!-- filepath: c:\xampp\htdocs\AT-Quiz-main\reply_mail.php -->
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include PHPMailer via Composer or manually

$email_id = isset($_GET['email_id']) ? (int)$_GET['email_id'] : 0;

// Database connection
$conn = new mysqli('localhost', 'root', '', 'quiz_system'); // Replace 'quiz_system' with your database name
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch email details
$hostname = "{imap.gmail.com:993/imap/ssl}INBOX";
$username = 'jagadishbit0@gmail.com';
$password = 'ughe ebfb ewky gqep';

$inbox = imap_open($hostname, $username, $password) or die('Cannot connect to IMAP: ' . imap_last_error());
$overview = imap_fetch_overview($inbox, $email_id, 0);
$message = imap_fetchbody($inbox, $email_id, 1);

// Extract user's email from the email body or use the sender's email
$user_email = extractEmailFromBody($message) ?: $overview[0]->from;

// Extract skill from email body
$skill = extractSkillFromBody($message);

// Match skill to quiz category
$category_id = matchSkillToCategory($skill, $conn);

// Generate quiz link
$quiz_link = generateQuizLink($category_id);

imap_close($inbox);

// Send reply email
sendReplyEmail($user_email, $quiz_link);

$conn->close();

function extractEmailFromBody($body) {
    // Use regex to extract the first email address found in the email body
    if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $body, $matches)) {
        return $matches[0];
    }
    return null; // Return null if no email is found
}

function extractSkillFromBody($body) {
    // Extract skill keywords from the email body
    $skills = ['Python', 'JavaScript', 'PHP', 'Dotnet', 'Java', 'ReactJS'];
    foreach ($skills as $skill) {
        if (stripos($body, $skill) !== false) {
            return $skill;
        }
    }
    return 'General'; // Default to General if no skill is found
}

function matchSkillToCategory($skill, $conn) {
    // Match the extracted skill to a quiz category in the database
    $stmt = $conn->prepare("SELECT id FROM quiz_categories WHERE LOWER(name) = LOWER(?)");
    $stmt->bind_param("s", $skill);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    $stmt->close();

    // If no matching category is found, return 0 (General)
    return $category ? $category['id'] : 0;
}

function generateQuizLink($category_id) {
    // Generate the quiz link with the category ID
    return "Quiz_page.php?category=" . urlencode($category_id);
}

function sendReplyEmail($recipient_email, $quiz_link) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jagadishbit0@gmail.com'; // Replace with your email
        $mail->Password = 'ughe ebfb ewky gqep'; // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email details
        $mail->setFrom('jagadishbit0@gmail.com', 'Quiz Application'); // Replace with your email and name
        $mail->addAddress($recipient_email);
        $mail->Subject = 'Your Quiz Link';
        $mail->Body = "Hello,\n\nBased on your skill, we have generated a quiz for you. Click the link below to start the quiz:\n\n$quiz_link\n\nGood luck!";

        $mail->send();
        echo "Reply sent successfully!";
    } catch (Exception $e) {
        echo "Failed to send reply. Error: {$mail->ErrorInfo}";
    }
}
?>