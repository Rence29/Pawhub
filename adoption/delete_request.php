<?php
$conn = new mysqli('localhost', 'root', '', 'dog_found');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM adoption_requests WHERE id = $id");
}

$conn->close();
header("Location: adoption_record.php");
exit();
