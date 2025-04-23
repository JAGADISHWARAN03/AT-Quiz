<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold text-center mb-6">Quiz Results</h1>

        <!-- Filter Section -->
        <?php include 'filter_section.php'; ?>

        <!-- Results Table -->
        <?php include 'results_table.php'; ?>
    </div>
</body>
</html>