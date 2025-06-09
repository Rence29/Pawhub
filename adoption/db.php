<?php
$host = 'localhost';
$db   = 'dog_found'; // Make sure this is your actual database name
$user = 'root';      // Your database username
$pass = '';          // Your database password (empty for 'root' by default in XAMPP/WAMP)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Essential for error reporting
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                // For better security and performance
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Log the error message (don't show to the user in production)
    error_log("Database connection failed: " . $e->getMessage());
    // In a real application, you might redirect to a generic error page
    die("Connection to the database failed. Please try again later.");
}
?>