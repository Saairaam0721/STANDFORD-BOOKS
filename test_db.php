<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'library_user');
define('DB_PASS', 'your_password_here');
define('DB_NAME', 'library_management');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully!<br>";

// Test query
$sql = "SELECT COUNT(*) as total_books FROM books";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo "Total books in database: " . $row['total_books'];

$conn->close();
?>