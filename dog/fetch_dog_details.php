<?php
// fetch_dog_details.php
session_start();

header('Content-Type: application/json'); // Indicate JSON response

// Database connection
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$dog_id = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $dog_id = (int)$_GET['id'];

    $sql = "SELECT * FROM dogs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $dog_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dog_data = $result->fetch_assoc();
        echo json_encode($dog_data);
    } else {
        echo json_encode(['error' => 'Dog not found.']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid dog ID.']);
}

$conn->close();
?>