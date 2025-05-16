<?php
require 'includes/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    if ($stmt->execute()) {
        header("Location: admin_login.php?registered=1");
        exit;
    } else {
        $error = "Registration failed!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Register / Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .slide-panel {
            transition: all 0.5s cubic-bezier(.4, 2, .6, 1);
            position: absolute;
            width: 100%;
            top: 0;
            left: 0;
        }

        .hidden-panel {
            opacity: 0;
            pointer-events: none;
            transform: translateX(40px) scale(0.98);
        }

        .active-panel {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(0) scale(1);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-indigo-100 to-blue-200 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-auto bg-white rounded-xl shadow-lg p-8 relative overflow-hidden" style="min-height: 420px;">
        <!-- Register Panel -->
        <div id="register-panel" class="slide-panel p-5 px-4 py-1 hidden-panel">
            <h2 class="text-2xl font-bold text-center text-indigo-700 mb-6">Admin Register</h2>
            <?php if (isset($error)): ?>
                <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-center"><?= $error ?></div>
            <?php endif; ?>
            <form method="post" class="space-y-5">
                <div>
                    <label class="block text-gray-700 mb-1" for="username">Username</label>
                    <input name="username" id="username" placeholder="Username" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 outline-none" />
                </div>
                <div>
                    <label class="block text-gray-700 mb-1" for="email">Email</label>
                    <input name="email" id="email" type="email" placeholder="Email" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 outline-none" />
                </div>
                <div>
                    <label class="block text-gray-700 mb-1" for="password">Password</label>
                    <input name="password" id="password" type="password" placeholder="Password" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 outline-none" />
                </div>
                <button type="submit"
                    class="w-full bg-indigo-600 mt-4 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">Register</button>
            </form>
            <div class="mt-4 text-center">
                <span class="text-gray-600">Already have an account?</span>
                <button type="button" onclick="showLogin()" class="text-indigo-600 hover:underline font-medium">Login here</button>
            </div>
        </div>
        <!-- Login Panel -->
        <div id="login-panel" class="slide-panel px-4 py-6 active-panel">
            <h2 class="text-2xl font-bold text-center text-indigo-700 mb-6">Admin Login</h2>
            <form action="admin_login.php" method="post" class="space-y-5">
                <div>
                    <label class="block text-gray-700 mb-1" for="login-username">Username</label>
                    <input name="username" id="login-username" placeholder="Username" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 outline-none" />
                </div>
                <div>
                    <label class="block text-gray-700 mb-1" for="login-password">Password</label>
                    <input name="password" id="login-password" type="password" placeholder="Password" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 outline-none" />
                </div>
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">Login</button>
            </form>
            <div class="mt-6 text-center">
                <span class="text-gray-600">Don't have an account?</span>
                <button type="button" onclick="showRegister()" class="text-indigo-600 hover:underline font-medium">Register here</button>
            </div>
        </div>
    </div>
    <script>
        function showLogin() {
            document.getElementById('register-panel').classList.remove('active-panel');
            document.getElementById('register-panel').classList.add('hidden-panel');
            document.getElementById('login-panel').classList.remove('hidden-panel');
            document.getElementById('login-panel').classList.add('active-panel');
        }
        function showRegister() {
            document.getElementById('login-panel').classList.remove('active-panel');
            document.getElementById('login-panel').classList.add('hidden-panel');
            document.getElementById('register-panel').classList.remove('hidden-panel');
            document.getElementById('register-panel').classList.add('active-panel');
        }
    </script>
</body>

</html>