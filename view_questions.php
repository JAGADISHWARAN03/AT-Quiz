<?php
include 'includes/config.php'; // Include database connection

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// Fetch questions for the selected quiz
$stmt = $conn->prepare("SELECT question_text, question_type, option_1, option_2, option_3, option_4, correct_option FROM questions WHERE quiz_title = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
?>


    <div class="max-w-6xl mx-auto mt-10">
        <h2 class="text-3xl font-bold text-center mb-6">Questions for Selected Quiz</h2>

        <!-- Questions Table -->
        <table class="w-full border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-200 p-2 text-left">Question</th>
                    <th class="border border-gray-200 p-2 text-left">Options</th>
                    <th class="border border-gray-200 p-2 text-left">Correct Answer</th>
                    <th class="border border-gray-200 p-2 text-left">Type</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['question_text']) ?></td>
                            <td class="border border-gray-200 p-2">
                                <ul class="list-disc pl-4">
                                    <li><?= htmlspecialchars($row['option_1']) ?></li>
                                    <li><?= htmlspecialchars($row['option_2']) ?></li>
                                    <li><?= htmlspecialchars($row['option_3']) ?></li>
                                    <li><?= htmlspecialchars($row['option_4']) ?></li>
                                </ul>
                            </td>
                            <td class="border border-gray-200 p-2">
                                <?php
                                if ($row['question_type'] === 'radio' || $row['question_type'] === 'checkbox') {
                                    $correct_option = json_decode($row['correct_option'], true);
                                    if (is_array($correct_option)) {
                                        foreach ($correct_option as $option) {
                                            echo "Option $option<br>";
                                        }
                                    } else {
                                        echo "Option " . htmlspecialchars($correct_option);
                                    }
                                } elseif ($row['question_type'] === 'text') {
                                    echo htmlspecialchars($row['correct_option']);
                                }
                                ?>
                            </td>
                            <td class="border border-gray-200 p-2"><?= htmlspecialchars($row['question_type']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="border border-gray-200 p-2 text-center text-red-500">No questions found for this quiz.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
