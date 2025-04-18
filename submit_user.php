<?php
// Include database configuration
include 'includes/config.php'; // Ensure this file contains your database connection details

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $conn->real_escape_string($_POST['name']);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $city = $conn->real_escape_string($_POST['city']);
    $exam_date = $conn->real_escape_string($_POST['exam_date']);
    $mobile_no = $conn->real_escape_string($_POST['mobile_no']);
    $pin_code = $conn->real_escape_string($_POST['pin_code']);
    $skill = $conn->real_escape_string($_POST['skill']);

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO users (name, full_name, email, city, exam_date, mobile_no, pin_code, skill) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $full_name, $email, $city, $exam_date, $mobile_no, $pin_code, $skill);

    if ($stmt->execute()) {
        echo "<script>alert('User submitted successfully!'); window.location.href = 'userssub.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>