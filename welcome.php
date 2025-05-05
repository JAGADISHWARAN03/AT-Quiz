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

    // Redirect to the same page to display questions
    header("Location: welcome.php?quiz_title_id=$quiz_title_id");
    exit;
}

// Check if user details are already submitted
$user_form_submitted = isset($_SESSION['user_name']);

// Fetch questions for the quiz based on quiz_title_id
$questions = [];
if ($user_form_submitted) {
    $stmt = $conn->prepare("SELECT id, question_text, option_1, option_2, option_3, option_4, correct_option FROM questions WHERE quiz_title_id = ?");
    $stmt->bind_param("i", $quiz_title_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aptitude Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <main class="max-w-[80%] mx-auto flex-col flex justify-center h-full min-h-[81.3vh] mt-1 mb-1 relative">
        <?php if (!$user_form_submitted): ?>
            <!-- User Form -->
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 text-start mt-6">User Information</h1>
            <form method="POST" class="bg-white shadow-lg p-6 rounded-lg border mt-4">
                <input type="hidden" name="user_form" value="1">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" id="name" name="name" class="w-full p-3 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" id="full_name" name="full_name" class="w-full p-3 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="w-full p-3 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" id="city" name="city" class="w-full p-3 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="mobile_no" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                    <input type="tel" id="mobile_no" name="mobile_no" class="w-full p-3 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="pin_code" class="block text-sm font-medium text-gray-700">Pin Code</label>
                    <input type="text" id="pin_code" name="pin_code" class="w-full p-3 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="skill" class="block text-sm font-medium text-gray-700">Skill</label>
                    <input type="text" id="skill" name="skill" class="w-full p-3 border rounded-lg" required>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Submit</button>
            </form>
        <?php else: ?>
            <!-- Quiz Questions -->
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 text-start mt-6">Aptitude Quiz</h1>
            <form id="quiz-form" method="POST" action="submit_quiz1.php" class="bg-white shadow-lg p-6 rounded-lg border mt-4">
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
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="mt-10 bg-blue-900 text-white text-center p-4">
        &copy; 2025 Quiz Management System
    </footer>
</body>
</html>