
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

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

        $mail->setFrom('jagadishbit0@gmail.com', 'Quiz Application'); // Replace with your email and name
        $mail->addAddress($email);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        echo "<script>alert('Mail sent successfully!'); window.location.href = 'mail.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error sending mail: {$mail->ErrorInfo}'); window.history.back();</script>";
    }
}?>