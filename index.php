<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Arrow Thought Aptitude Quiz Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Background with overlay */
        .landing-bg {
            background: linear-gradient(rgba(10,56,128,0.45), rgba(10,56,128,0.45)), url('assets/At_bg.webp') center center/cover no-repeat;
            min-height: 100vh; /* Ensure full height */
        }
    </style>
</head>
<body class="min-h-screen flex flex-col landing-bg">
    <!-- Header -->
  

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-start">
        <div class="landing-content max-w-full sm:max-w-2xl mx-4 sm:ml-10 md:ml-24 mt-10 sm:mt-16 md:mt-24 text-center sm:text-left">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-6 leading-tight drop-shadow-lg">Arrow Thought Aptitude Quiz Platform!</h1>
            <p class="mb-4 text-base sm:text-lg text-white drop-shadow">
                Welcome to Arrow Thought, where innovation meets excellence. Established with a vision to empower individuals and businesses to reach their full potential, Arrow Thought has grown into a beacon of transformative solutions across various sectors. Our name, Arrow Thought, symbolizes precision, speed, and intellectual advancement, reflecting our commitment to delivering targeted and thoughtful solutions to complex challenges.
            </p>
            <ul class="mb-4 font-semibold text-white drop-shadow text-base sm:text-lg">
                <li>Ace Your Aptitude Test with Confidence!</li>
                <li>Prepare Smart – Pass Your Test – Secure Your Future</li>
            </ul>
            <a id="start-now-btn" href="#" class="bg-[#e11d48] text-white px-4 sm:px-6 py-2 rounded-md font-semibold hover:bg-[#c81c3a] mt-4 inline-block shadow-lg">Start Now</a>
            <script>
                // Get quiz_title_id from current URL
                const params = new URLSearchParams(window.location.search);
                const quizId = params.get('quiz_title_id') || '1'; // default to 1 if not present
                document.getElementById('start-now-btn').href = `user_form.php?quiz_title_id=${quizId}`;
            </script>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-[#0a3880] text-white text-center p-4">
        © 2025 Copyright: Arrow Thought
    </footer>
</body>
</html>