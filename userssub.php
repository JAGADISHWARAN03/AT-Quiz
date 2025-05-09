<!-- filepath: c:\xampp\htdocs\AT-Quiz-main\userssub.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Submission Form</title>
    <link rel="stylesheet" href="assets/styles.css"> 
    <script src="https://cdn.tailwindcss.com"></script>
    
</head>\
<style>
        .hidden {
            display: none;
        }
    </style>
<?php include 'includes/header.php'; ?>
<?php
// Include database configuration
include 'includes/config.php'; // Ensure this file contains your database connection details

// Include PHPMailer for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $conn->real_escape_string($_POST['name']);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $city = $conn->real_escape_string($_POST['city']);
    $exam_date = $conn->real_escape_string($_POST['exam_date']);
    $mobile_no = $conn->real_escape_string($_POST['mobile_no']);
    $pin_code = $conn->real_escape_string($_POST['pin_code']);
    $skill = $conn->real_escape_string($_POST['skill']);

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO users (name, full_name, email, city, exam_date, mobile_no, pin_code, skill) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $full_name, $email, $city, $exam_date, $mobile_no, $pin_code, $skill);

    if ($stmt->execute()) {
        // Send emails
        $admin_email = "jagadishbit0@gmail.com"; // Replace with the admin's email address

        // Send greeting email to the user
        $user_subject = "Welcome to Our Platform!";
        $user_body = "Dear $name,\n\nThank you for registering with us. We are excited to have you on board!\n\nBest regards,\nArrow Thooughts";

        // Send registration notification to the admin
        $admin_subject = "New User Registration";
        $admin_body = "A new user has registered with the following details:\n\n" .
                      "Name: $name\n" .
                      "Full Name: $full_name\n" .
                      "Email: $email\n" .
                      "City: $city\n" .
                      "Exam Date: $exam_date\n" .
                      "Mobile Number: $mobile_no\n" .
                      "Pin Code: $pin_code\n" .
                      "Skill: $skill\n";

        // Initialize PHPMailer
        $mail = new PHPMailer(true);

        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'jagadish027609@gmail.com'; // Replace with your actual email
            $mail->Password = 'xkfy uscx cnyk ncvu'; // Replace with your email's app password (not the regular password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Send email to the user
            $mail->setFrom('jagadishbit0@gmail.com', 'jagadish'); // Replace with your email and name
            $mail->addAddress($email, $name);
            $mail->Subject = $user_subject;
            $mail->Body = $user_body;
            $mail->send();

            // Send email to the admin
            $mail->clearAddresses();
            $mail->addAddress($admin_email, 'Admin');
            $mail->Subject = $admin_subject;
            $mail->Body = $admin_body;
            $mail->send();

            // Show success message
            echo "<script>alert('User submitted successfully! A greeting email has been sent to the user, and the admin has been notified.'); window.location.href = 'userssub.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('User submitted successfully, but email could not be sent. Error: {$mail->ErrorInfo}'); window.location.href = 'userssub.php';</script>";
        }
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- Main Content -->
   <!-- Main Content -->
   <main class="flex-grow flex items-center justify-center mt-4 mb-4">
        <div class="bg-white w-full max-w-md p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">User Submission Form</h2>
            <form method="POST" action="userssub.php" class="space-y-4">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1"> First Name</label>
                    <input type="text" id="name" name="name" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your name" required>
                </div>
                <!-- Full Name -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" id="full_name" name="full_name" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your full name" required>
                </div>
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your email" required>
                </div>
                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" id="city" name="city" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your city" required>
                </div>
                <!-- Exam Date -->
                <div>
                    <label for="exam_date" class="block text-sm font-medium text-gray-700 mb-1">Exam Date</label>
                    <input type="date" id="exam_date" name="exam_date" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <!-- Mobile Number -->
                <div>
                    <label for="mobile_no" class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                    <input type="tel" id="mobile_no" name="mobile_no" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your mobile number" required>
                </div>
                <!-- Pin Code -->
                <div>
                    <label for="pin_code" class="block text-sm font-medium text-gray-700 mb-1">Pin Code</label>
                    <input type="text" id="pin_code" name="pin_code" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your pin code" required>
                </div>
                <!-- Skill -->
                <div>
                    <label for="skill" class="block text-sm font-medium text-gray-700 mb-1">Skill</label>
                    <input type="text" id="skill" name="skill" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your skill" required>
                </div>
                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </main>
    <script>
        document.getElementById('userForm').addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent the default form submission

            // Show the loading spinner
            document.getElementById('loadingSpinner').classList.remove('hidden');

            // Collect form data
            const formData = new FormData(this);

            // Send the form data via AJAX
            fetch('userssub.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.text())
                .then(data => {
                    // Hide the loading spinner
                    document.getElementById('loadingSpinner').classList.add('hidden');

                    // Show success message
                    alert('Form submitted successfully!');
                })
                .catch(error => {
                    // Hide the loading spinner
                    document.getElementById('loadingSpinner').classList.add('hidden');

                    // Show error message
                    alert('An error occurred while submitting the form.');
                    console.error('Error:', error);
                });
        });
    </script>
    <!-- Footer -->
   
</body>
<footer class="bg-blue-900 text-white text-center p-4">
        &copy; 2025 Quiz Management System
    </footer>
</html>

