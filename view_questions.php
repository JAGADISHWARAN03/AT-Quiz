<?php
require 'db.php';

$result = $conn->query("SELECT * FROM questions1");

echo "<h2>Extracted Questions</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Question</th><th>Option 1</th><th>Option 2</th><th>Option 3</th><th>Option 4</th><th>Correct Answer</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['question']}</td>";
    echo "<td>{$row['option1']}</td>";
    echo "<td>{$row['option2']}</td>";
    echo "<td>{$row['option3']}</td>";
    echo "<td>{$row['option4']}</td>";
    echo "<td>{$row['correct_answer']}</td>";
    echo "</tr>";
}

echo "</table>";
?>
