<?php
// Include your database connection file that provides the $pdo object
include 'db.php'; // This should connect using PDO as defined above

// Function to sanitize and validate integer IDs
function validateId($id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        return false;
    }
    return $id;
}

// Check for required GET parameters
$request_id = validateId($_GET['id'] ?? null);
$status = $_GET['status'] ?? null; // Expected values: 'Accepted' or 'Declined'

if ($request_id === false || !in_array($status, ['Accepted', 'Declined'])) {
    // Log the error for debugging
    error_log("Invalid request ID or status received in update_status.php. ID: " . ($_GET['id'] ?? 'N/A') . ", Status: " . ($status ?? 'N/A'));
    die("Invalid request. Please go back and try again.");
}

try {
    // Start a transaction for atomicity. If anything fails, everything is rolled back.
    $pdo->beginTransaction();

    // 1. Fetch the adoption request details from adoption_requests
    $stmt_fetch_request = $pdo->prepare("SELECT * FROM adoption_requests WHERE id = ?");
    $stmt_fetch_request->execute([$request_id]);
    $request = $stmt_fetch_request->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        throw new Exception("Adoption request with ID {$request_id} not found.");
    }

    // 2. Insert the request details into the adoption_history table
    // IMPORTANT: Ensure adoption_history table has a 'final_status' column (ENUM('Accepted', 'Declined'))
    $stmt_insert_history = $pdo->prepare("
        INSERT INTO adoption_history (
            adopter_name, adopter_age, adopter_email, adopter_phone,
            house_space, pet_experience, family_composition, adopter_address,
            dog_breed, dog_age, dog_size, dog_sex, dog_color, dog_behavior, dog_image,
            request_date, final_status, processed_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt_insert_history->execute([
        $request['adopter_name'],
        $request['adopter_age'],
        $request['adopter_email'],
        $request['adopter_phone'],
        $request['house_space'],
        $request['pet_experience'],
        $request['family_composition'],
        $request['adopter_address'],
        $request['dog_breed'],
        $request['dog_age'],
        $request['dog_size'],
        $request['dog_sex'],
        $request['dog_color'],
        $request['dog_behavior'],
        $request['dog_image'],
        $request['request_date'], // Use the original request_date from adoption_requests
        $status // This will be 'Accepted' or 'Declined'
    ]);

    // 3. If the request is Declined, re-insert the dog back into adoption_list
    if ($status === 'Declined') {
        $stmt_return_dog = $pdo->prepare("
            INSERT INTO adoption_list (
                dog_image, dog_breed, dog_sex, dog_color, dog_age, dog_behavior, dog_size, health_condition, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available')
        ");
        $stmt_return_dog->execute([
            $request['dog_image'],
            $request['dog_breed'],
            $request['dog_sex'],
            $request['dog_color'],
            $request['dog_age'],
            $request['dog_behavior'],
            $request['dog_size'],
            'Healthy' // Default health_condition for re-listed dogs (adjust if needed)
        ]);
    }

    // 4. Delete the processed request from the adoption_requests table
    $stmt_delete_request = $pdo->prepare("DELETE FROM adoption_requests WHERE id = ?");
    $stmt_delete_request->execute([$request_id]);

    // Commit the transaction if all operations were successful
    $pdo->commit();

    // Redirect back to the adoption history page after successful processing
    header("Location: adoption_history.php"); // MODIFIED LINE
    exit();

} catch (PDOException $e) {
    // Rollback the transaction on a PDO error
    $pdo->rollBack();
    error_log("PDO Error in update_status.php: " . $e->getMessage());
    die("A database error occurred during the update. Please try again later.");
} catch (Exception $e) {
    // Rollback the transaction on any other general error
    $pdo->rollBack();
    error_log("General Error in update_status.php: " . $e->getMessage());
    die("An unexpected error occurred. Please try again later.");
}
?>