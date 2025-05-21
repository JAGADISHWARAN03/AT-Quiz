<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Arrow Thought | Aptitude Quiz</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fafbfc] min-h-screen flex flex-col">
    <header class="w-full flex flex-col sm:flex-row items-center justify-between px-4 sm:px-10 py-4 bg-[#f5f7fa]">
        <img src="assets/Arrow Thought (1) 1 (1).png" alt="Logo" class="h-10 mb-2 sm:mb-0">
        <div class="flex items-center space-x-2">
            <div class="bg-[#0a3880] rounded-full p-2">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke="#fff" stroke-width="2" d="M22 16.92V19a2 2 0 0 1-2 2A19.72 19.72 0 0 1 3 5a2 2 0 0 1 2-2h2.09a2 2 0 0 1 2 1.72c.13 1.05.37 2.07.72 3.05a2 2 0 0 1-.45 2.11l-1.27 1.27a16 16 0 0 0 6.29 6.29l1.27-1.27a2 2 0 0 1 2.11-.45c.98.35 2 .59 3.05.72A2 2 0 0 1 22 16.92z"/>
                </svg>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-700 font-semibold">Call any time</div>
                <div class="text-xs text-gray-500">+1 916 284 9204</div>
            </div>
        </div>
    </header>
    <main class="flex-1 flex flex-col items-center justify-center px-2">
        <h2 class="text-lg sm:text-xl font-semibold text-[#e11d48] mt-8 mb-6 text-center">Introduction</h2>
        <div class="w-full max-w-lg sm:max-w-2xl mx-auto bg-white bg-opacity-80 rounded-xl shadow p-4 sm:p-8">
            <div class="mb-6">
                <p class="font-bold mb-2">About:</p>
                <p class="text-sm sm:text-base">This test consists of 20 questions, and the total time allotted is 25 minutes.</p>
            </div>
            <div class="mb-6">
                <p class="font-bold mb-2">Instruction</p>
                <p class="text-sm sm:text-base">
                    Read each question carefully and choose the correct answer from the given options. There is only one correct answer per question, and no negative marking for incorrect responses. Ensure to complete the test within the given time limit.
                </p>
            </div>
            <p class="mb-8 text-sm sm:text-base">When you are ready, click "Get Started" to start the test.</p>
            <div class="flex justify-center">
                <?php
                    $quiz_id = isset($_GET['quiz_title_id']) ? intval($_GET['quiz_title_id']) : 1;
                ?>
                <a href="Quizz_page.php?quiz_title_id=<?= $quiz_id ?>" class="bg-[#e11d48] text-white px-6 py-2 rounded-md font-semibold hover:bg-[#c81c3a] transition">Get Started</a>
            </div>
        </div>
    </main>
    <footer class="bg-[#0a3880] text-white text-center p-4 text-xs sm:text-base">
        &copy; 2025 Copyright: Arrow Thought
    </footer>
</body>
</html>