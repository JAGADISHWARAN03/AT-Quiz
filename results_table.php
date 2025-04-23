<div class="bg-white shadow-lg p-6 rounded-lg">
    <h2 class="text-2xl font-bold mb-4">Results</h2>
    <table class="min-w-full border">
        <thead>
            <tr class="border-b">
                <th class="text-left p-2">User Email</th>
                <th class="text-left p-2">City</th>
                <th class="text-left p-2">Skill</th>
                <th class="text-left p-2">Category</th>
                <th class="text-left p-2">Total Score</th>
                <th class="text-left p-2">Total Questions</th>
                <th class="text-left p-2">Percentage</th>
                <th class="text-left p-2">Exam Date</th>
                <th class="text-left p-2">Pass/Fail</th> <!-- New column -->
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-2"><?= htmlspecialchars($row['user_email']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['city']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['skill']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['category_name']) ?></td>
                        <td class="p-2"><?= $row['total_score'] ?></td>
                        <td class="p-2"><?= $row['total_questions'] ?></td>
                        <td class="p-2"><?= round($row['percentage'], 2) ?>%</td>
                        <td class="p-2"><?= htmlspecialchars($row['exam_date']) ?></td>
                        <td class="p-2">
                            <?php if ($row['percentage'] >= 50): ?>
                                <span class="text-green-500 font-bold">Pass</span>
                            <?php else: ?>
                                <span class="text-red-500 font-bold">Fail</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center text-gray-500 p-4">No results found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>