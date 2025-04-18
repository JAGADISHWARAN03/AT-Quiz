<?php
require 'vendor/autoload.php';  // Ensure this is the correct path
require 'db.php'; // Ensure this file exists

use Smalot\PdfParser\Parser;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["pdf_file"])) {
    $file = $_FILES["pdf_file"];
    
    // Validate file type
    if ($file["type"] !== "application/pdf") {
        die("Error: Only PDF files are allowed.");
    }

    // Ensure uploads directory exists
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Define target path
    $pdf_path = $uploadDir . basename($file["name"]);

    // Move file
    if (!move_uploaded_file($file["tmp_name"], $pdf_path)) {
        die("Error: Failed to move uploaded file. Check folder permissions.");
    }

    // Check if file exists
    if (!file_exists($pdf_path)) {
        die("Error: Uploaded file not found.");
    }

    // Parse PDF
    try {
        $parser = new Parser();
        $pdf = $parser->parseFile($pdf_path);
        $text = $pdf->getText();

        if (empty($text)) {
            die("Error: The uploaded PDF appears to be empty.");
        }

        // Process the extracted text
        $lines = explode("\n", $text); // Split text into lines
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                // Example: Assuming the extracted line is the question and other fields are placeholders
                $question = $line;
                $option1 = "Option 1"; // Replace with actual logic to extract options
                $option2 = "Option 2"; // Replace with actual logic to extract options
                $option3 = "Option 3"; // Replace with actual logic to extract options
                $option4 = "Option 4"; // Replace with actual logic to extract options
                $correct_answer = "Option 1"; // Replace with actual logic to determine the correct answer
                $created_at = date("Y-m-d H:i:s"); // Current timestamp

                // Insert into the `questions1` table
                $stmt = $conn->prepare("INSERT INTO questions1 (question, option1, option2, option3, option4, correct_answer, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss", $question, $option1, $option2, $option3, $option4, $correct_answer, $created_at);
                $stmt->execute();
            }
        }

        echo "PDF uploaded, processed, and data stored successfully.";
    } catch (Exception $e) {
        die("PDF Processing Error: " . $e->getMessage());
    }
}
?>
