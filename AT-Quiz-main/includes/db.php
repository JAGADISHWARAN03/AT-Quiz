<?php
// Database connection settings
$host = 'localhost'; // Database host
$user = 'root'; // Database username
$password = ''; // Database password
$dbname = 'quiz_db'; // Database name

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to execute a query and return results
function executeQuery($query) {
    global $conn;
    $result = $conn->query($query);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    return $result;
}

// Function to escape user input for safe database queries
function escapeInput($input) {
    global $conn;
    return $conn->real_escape_string($input);
}

// Close the connection when done
function closeConnection() {
    global $conn;
    $conn->close();
}
?>