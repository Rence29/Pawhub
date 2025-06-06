<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
}

// Check if the 'id' parameter is passed in the URL
if (isset($_GET['id'])) {
   $id = $_GET['id'];

   // SQL query to delete the dog record with the specified id
   $sql = "DELETE FROM users WHERE id = $id";

   // Execute the query
   if ($conn->query($sql) === TRUE) {
      // Redirect to the dog list page after successful deletion
      header("Location: user_list.php");
      exit();
   } else {
      echo "Error deleting record: " . $conn->error;
   }
} else {
   echo "Invalid ID.";
}

// Close the database connection
$conn->close();
?>
