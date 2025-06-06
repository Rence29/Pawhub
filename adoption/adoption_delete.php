<?php
session_start(); // Start the session for messages

// Database connection
$conn = new mysqli('localhost', 'root', '', 'dog_found');

// Check connection
if ($conn->connect_error) {
    // Log the error instead of dying directly in a production environment
    error_log("Database Connection failed: " . $conn->connect_error);
    die("Connection failed: Please try again later."); // User-friendly message
}

// Check if an ID is provided for deletion
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $dog_id = $_GET['id'];

    // Only delete from adoption_list.
    // The ON DELETE SET NULL constraint will handle related records in adoption_history.
    $stmt = $conn->prepare("DELETE FROM adoption_list WHERE id = ?");

    // Check if the prepare statement failed
    if ($stmt === false) {
        // Log the error for debugging
        error_log("Prepare statement failed: " . $conn->error);
        $_SESSION['error'] = "An unexpected error occurred. Please try again.";
        header("Location: adoption_list.php");
        exit();
    }

    $stmt->bind_param("i", $dog_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Dog successfully removed from adoption list. Adoption history for this dog has been preserved (with dog_id set to NULL).";
    } else {
        // Log the error for debugging
        error_log("Execute statement failed: " . $stmt->error);
        $_SESSION['error'] = "Error deleting dog from adoption list: " . $stmt->error;
    }
    $stmt->close();
    header("Location: adoption_list.php");
    exit();

} else {
    $_SESSION['error'] = "Invalid dog ID provided for deletion.";
    header("Location: adoption_list.php");
    exit();
}

$conn->close();
?>