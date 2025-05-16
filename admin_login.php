<?php
session_start();
require 'includes/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && password_verify($_POST['password'], $result['password'])) {
        $_SESSION['admin_id'] = $result['id'];
        $_SESSION['admin_name'] = $result['name'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-100 to-blue-200 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-auto bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-center text-indigo-700 mb-6">Admin Login</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-center"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['registered'])): ?>
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4 text-center">Registration successful! Please login.</div>
        <?php endif; ?>
        <form method="post" class="space-y-5">
            <div>
                <label class="block text-gray-700 mb-1" for="username">Username</label>
                <input name="username" id="username" placeholder="Username" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 outline-none" />
            </div>
            <div>
                <label class="block text-gray-700 mb-1" for="password">Password</label>
                <input name="password" id="password" type="password" placeholder="Password" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 outline-none" />
            </div>
            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">Login</button>
        </form>
        <div class="mt-6 text-center">
            <span class="text-gray-600">Don't have an account?</span>
            <a href="admin_register.php" class="text-indigo-600 hover:underline font-medium">Register here</a>
        </div>
    </div>
</body>
</html>