<!-- filepath: c:\xampp\htdocs\AT-Quiz-main\send_reply.php -->
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include PHPMailer via Composer or manually

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_id = (int)$_POST['email_id'];
    $reply_message = $_POST['reply_message'];

    // Email configuration
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Use Gmail's SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'jagadish027609@gmail.com'; // Your Gmail address
        $mail->Password = 'xkfy uscx cnyk ncvu'; // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email details
        $mail->setFrom('jagadish027609@gmail.com', 'Arrow Thought'); 
        $mail->addAddress('recipient@example.com'); 
        $mail->Subject = 'Reply to your email';
        $mail->Body = $reply_message;

        // Send email
        $mail->send();
        echo "Reply sent successfully!";
    } catch (Exception $e) {
        echo "Failed to send reply. Error: {$mail->ErrorInfo}";
    }
}
?>