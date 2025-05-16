<?php

session_start();
$username = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'User';
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logged Out</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .fade-in {
            opacity: 0;
            transform: translateY(30px) scale(0.98);
            animation: fadeInUp 0.8s cubic-bezier(.4,2,.6,1) forwards;
        }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            100% { transform: rotate(360deg);}
        }
        .redirect-link {
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-indigo-300 via-blue-200 to-purple-200">
    <div class="bg-white/90 p-10 rounded-2xl shadow-2xl text-center fade-in max-w-md w-full">
        <div class="flex justify-center mb-4">
            <svg class="w-16 h-16 text-indigo-500 spin" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 48 48">
                <circle class="opacity-30" cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4"/>
                <path d="M44 24c0-11.046-8.954-20-20-20" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
            </svg>
        </div>
        <h2 class="text-3xl font-extrabold text-indigo-700 mb-2 tracking-tight">Goodbye, <?= htmlspecialchars($username) ?>!</h2>
        <p class="text-lg text-gray-700 mb-4">You have been logged out successfully.</p>
        <div class="flex items-center justify-center gap-2 text-indigo-600 mb-2">
            <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7"></path>
            </svg>
            <span class="text-base font-medium redirect-link" id="redirect-login">Redirecting to login page...</span>
        </div>
        <!-- <div class="mt-6">
            <a href="admin_login.php" class="inline-block px-6 py-2 bg-indigo-500 text-white rounded-lg shadow hover:bg-indigo-600 transition">Go to Login Now</a>
        </div> -->
    </div>
    <script>
        document.getElementById('redirect-login').addEventListener('click', function() {
            document.querySelector('.fade-in').classList.add('opacity-0', 'scale-95', 'transition-all', 'duration-500');
            setTimeout(() => {
                window.location.href = 'admin_login.php';
            }, 400);
        });
    </script>
</body>
</html>