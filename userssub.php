<!-- filepath: c:\xampp\htdocs\AT-Quiz-main\userssub.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Submission Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white w-full max-w-2xl p-8 rounded-lg shadow-lg">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">User Submission Form</h2>
            <form method="POST" action="submit_user.php" class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input type="text" id="name" name="name" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your name" required>
                </div>
                <!-- Full Name -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your full name" required>
                </div>
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your email" required>
                </div>
                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <input type="text" id="city" name="city" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your city" required>
                </div>
                <!-- Exam Date -->
                <div>
                    <label for="exam_date" class="block text-sm font-medium text-gray-700 mb-2">Exam Date</label>
                    <input type="date" id="exam_date" name="exam_date" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <!-- Mobile Number -->
                <div>
                    <label for="mobile_no" class="block text-sm font-medium text-gray-700 mb-2">Mobile Number</label>
                    <input type="tel" id="mobile_no" name="mobile_no" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your mobile number" required>
                </div>
                <!-- Pin Code -->
                <div>
                    <label for="pin_code" class="block text-sm font-medium text-gray-700 mb-2">Pin Code</label>
                    <input type="text" id="pin_code" name="pin_code" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your pin code" required>
                </div>
                <!-- Skill -->
                <div>
                    <label for="skill" class="block text-sm font-medium text-gray-700 mb-2">Skill</label>
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
    </div>
</body>
</html>