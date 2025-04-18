
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questions Display</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<?php include 'includes/header.php'; // Updated path to the header file ?><?php
require 'includes/config.php'; // Include database connection

// Fetch quiz results grouped by category, user ID, and user email
$query = "
    SELECT 
        qr.user_id, 
        qr.user_email, 
        qc.name AS category_name, 
        SUM(qr.score) AS total_score, 
        SUM(qr.total_questions) AS total_questions,
        COUNT(qr.id) AS attempts
    FROM quiz_results qr
    JOIN quiz_categories qc ON qr.category_id = qc.id
    GROUP BY qr.user_id, qr.user_email, qr.category_id
    ORDER BY qc.name, qr.user_email
";
$result = $conn->query($query);
?>

<div class="container mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold text-center mb-6">Quiz Results</h1>

    <div class="bg-white shadow-lg p-6 rounded-lg">
        <table class="min-w-full border">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">User ID</th>
                    <th class="text-left p-2">User Email</th>
                    <th class="text-left p-2">Category</th>
                    <th class="text-left p-2">Total Score</th>
                    <th class="text-left p-2">Total Questions</th>
                    <th class="text-left p-2">Attempts</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-2"><?= $row['user_id'] ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['user_email']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['category_name']) ?></td>
                        <td class="p-2"><?= $row['total_score'] ?></td>
                        <td class="p-2"><?= $row['total_questions'] ?></td>
                        <td class="p-2"><?= $row['attempts'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; // Updated path to the header file ?>