<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $request_id = $_GET['id'];
    $new_status = $_GET['status']; // 'Accepted' or 'Declined'

    // Start a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // 1. Fetch the full request data directly from the denormalized adoption_requests table
        $stmt_fetch = $conn->prepare("
            SELECT ar.id AS request_id, ar.request_date,
                   ar.adopter_name, ar.adopter_email, ar.adopter_phone, ar.adopter_address,
                   ar.adopter_age, ar.house_space, ar.pet_experience, ar.family_composition,
                   ar.dog_breed, ar.dog_age, ar.dog_size, ar.dog_sex, ar.dog_color, ar.dog_behavior, ar.dog_image
            FROM adoption_requests ar
            WHERE ar.id = ?
        ");
        $stmt_fetch->bind_param("i", $request_id);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        $request_data = $result->fetch_assoc();
        $stmt_fetch->close();

        if ($request_data) {
            // 2. Insert the data into adoption_history
            $stmt_insert_history = $conn->prepare("
                INSERT INTO adoption_history (
                    adopter_name, adopter_email, adopter_phone, adopter_address,
                    adopter_age, house_space, pet_experience, family_composition,
                    dog_breed, dog_age, dog_size, dog_sex, dog_color, dog_behavior, dog_image,
                    status, request_date, processed_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            // Bind parameters: 's' for string. Adjust types if any column is not string (e.g., 'i' for int).
            $stmt_insert_history->bind_param(
                "sssssssssssssssss", // 17 's' parameters for the 17 columns being inserted
                $request_data['adopter_name'],
                $request_data['adopter_email'],
                $request_data['adopter_phone'],
                $request_data['adopter_address'],
                $request_data['adopter_age'],
                $request_data['house_space'],
                $request_data['pet_experience'],
                $request_data['family_composition'],
                $request_data['dog_breed'],
                $request_data['dog_age'],
                $request_data['dog_size'],
                $request_data['dog_sex'],
                $request_data['dog_color'],
                $request_data['dog_behavior'],
                $request_data['dog_image'],
                $new_status, // 'Accepted' or 'Declined'
                $request_data['request_date'] // Original request date
            );
            $stmt_insert_history->execute();
            $stmt_insert_history->close();

            // 3. Delete the record from adoption_requests
            $stmt_delete = $conn->prepare("DELETE FROM adoption_requests WHERE id = ?");
            $stmt_delete->bind_param("i", $request_id);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Commit the transaction if all operations were successful
            $conn->commit();

            // 4. Redirect to adoption_history.php
            header("Location: adoption_history.php");
            exit();

        } else {
            throw new Exception("Adoption request with ID " . $request_id . " not found for processing.");
        }

    } catch (Exception $e) {
        // Rollback the transaction if any operation failed
        $conn->rollback();
        die("Error processing request: " . $e->getMessage());
    }
} else {
    die("Invalid request parameters. Missing ID or status.");
}

$conn->close();
?>