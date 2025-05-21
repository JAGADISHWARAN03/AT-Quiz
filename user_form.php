<?php
require 'includes/config.php';

session_start();

// Check if quiz_title_id is provided in the URL
if (!isset($_GET['quiz_title_id'])) {
    echo '<p class="text-center text-red-500">Invalid Quiz ID.</p>';
    exit;
}

$quiz_title_id = (int)$_GET['quiz_title_id']; // Get the quiz_title_id from the URL
$_SESSION['quiz_title_id'] = $quiz_title_id; // Store it in the session for later use

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_form'])) {
    // Save user details in the database
    $name = $_POST['name'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $city = $_POST['city'];
    $mobile_no = $_POST['mobile_no'];
    $pin_code = $_POST['pin_code'];
    $skill = $_POST['skill'];

    $stmt = $conn->prepare("INSERT INTO users (name, full_name, email, city, mobile_no, pin_code, skill) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $full_name, $email, $city, $mobile_no, $pin_code, $skill);
    $stmt->execute();
    $stmt->close();

    // Store user details in the session
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_city'] = $city;
    $_SESSION['user_mobile'] = $mobile_no;

    // Redirect to Quiz_page.php
    header("Location: instruction.php?quiz_title_id=$quiz_title_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Information</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .user-form-bg {
            background: #0a3880;
        }
        .user-form-label, .user-form-label * {
            color: #fff !important;
        }
        .user-form-input {
            background: #fff !important;
            color: #222 !important;
        }
        .user-form-input:focus {
            border-color: #e11d48 !important;
            box-shadow: 0 0 0 2px #e11d4833;
        }
        .user-form-btn {
            background: #e11d48;
        }
        .user-form-btn:hover {
            background: #c81c3a;
        }
    </style>
</head>
<body class="bg-[#fafbfc] min-h-screen flex flex-col">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- User Form Section -->
    <main class="flex-1 flex flex-col items-center justify-center px-2 py-8">
        <h2 class="text-xl sm:text-2xl md:text-3xl font-semibold text-[#e11d48] text-center mt-6 mb-2">User Information</h2>
        <p class="text-center max-w-2xl mb-6 text-sm sm:text-base">
            Fill out the form below to create your account and access our aptitude tests. Provide accurate details to ensure a seamless experience and personalized recommendations. Your information is secure with us.
        </p>
        <div class="w-full max-w-4xl mx-auto user-form-bg rounded-xl shadow-lg p-4 sm:p-8">
            <h3 class="text-white text-lg sm:text-xl font-bold mb-6 text-center">User Information</h3>
            <form method="POST" class="space-y-4" id="userForm">
                <input type="hidden" name="user_form" value="1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- First Name -->
                    <div>
                        <label for="name" class="user-form-label block text-sm font-medium mb-1">First Name</label>
                        <input type="text" id="name" name="name" class="user-form-input w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#e11d48]" required>
                    </div>
                    <!-- Last Name -->
                    <div>
                        <label for="full_name" class="user-form-label block text-sm font-medium mb-1">Last Name</label>
                        <input type="text" id="full_name" name="full_name" class="user-form-input w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#e11d48]" required>
                    </div>
                    <!-- Email -->
                    <div>
                        <label for="email" class="user-form-label block text-sm font-medium mb-1">Email</label>
                        <input type="email" id="email" name="email" class="user-form-input w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#e11d48]" required>
                    </div>
                    <!-- City -->
                    <div>
                        <label for="city" class="user-form-label block text-sm font-medium mb-1">City</label>
                        <input type="text" id="city" name="city" class="user-form-input w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#e11d48]" required>
                    </div>
                    <!-- Mobile Number -->
                    <div>
                        <label for="mobile_no" class="user-form-label block text-sm font-medium mb-1">Mobile Number</label>
                        <input type="tel" id="mobile_no" name="mobile_no" class="user-form-input w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#e11d48]" required>
                    </div>
                    <!-- Pin Code -->
                    <div>
                        <label for="pin_code" class="user-form-label block text-sm font-medium mb-1">Pin Code</label>
                        <input type="text" id="pin_code" name="pin_code" class="user-form-input w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#e11d48]" required>
                    </div>
                    <!-- Skill -->
                    <div class="md:col-span-2">
                        <label for="skill" class="user-form-label block text-sm font-medium mb-1">Skill</label>
                        <input type="text" id="skill" name="skill" class="user-form-input w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#e11d48]" required>
                    </div>
                </div>
                <!-- Submit Button -->
                <div class="flex justify-center pt-2">
                    <button type="submit" class="user-form-btn text-white px-8 py-2 rounded-md font-semibold hover:bg-[#c81c3a] transition">Submit</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-[#0a3880] text-white text-center p-4 text-xs sm:text-base mt-8">
        &copy; 2025 Quiz Management System
    </footer>
</body>
</html>