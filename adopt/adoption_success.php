<?php
// Include your database connection file
// Make sure 'db.php' is in the same directory or adjust the path accordingly.
include 'db.php';

// --- Input Validation (Added for robustness) ---
// Function to sanitize and validate integer IDs
function validateId($id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        return false;
    }
    return $id;
}

// Check if the form to finalize adoption has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalize'])) {
    $adopter_id = validateId($_POST['adopter_id'] ?? null);
    $dog_id = validateId($_POST['dog_id'] ?? null);

    if ($adopter_id === false || $dog_id === false) {
        // Log this error in a real application instead of dying directly
        error_log("Invalid adopter_id or dog_id received during POST request.");
        die("Invalid adoption request data. Please go back and try again.");
    }

    // 1. Fetch ALL adopter information
    $stmt_adopter = $pdo->prepare("SELECT * FROM adopters_info WHERE id = ?");
    $stmt_adopter->execute([$adopter_id]);
    $adopter = $stmt_adopter->fetch(PDO::FETCH_ASSOC);

    // 2. Fetch ALL dog information
    $stmt_dog = $pdo->prepare("SELECT * FROM adoption_list WHERE id = ?");
    $stmt_dog->execute([$dog_id]);
    $dog = $stmt_dog->fetch(PDO::FETCH_ASSOC);

    // Proceed only if both adopter and dog data are found
    if ($adopter && $dog) {
        // --- CRITICAL FIXES HERE: Corrected column names and mapping ---
        // Prepare the INSERT statement to save all relevant data into adoption_requests
        // Ensure these column names EXACTLY match your 'adoption_requests' table schema.
        $stmt = $pdo->prepare("INSERT INTO adoption_requests (
            adopter_name,
            adopter_age,
            adopter_email,
            adopter_phone,
            house_space,        
            pet_experience,     
            family_composition, 
            adopter_address,
            dog_breed,
            dog_age,
            dog_size,
            dog_sex,
            dog_color,
            dog_behavior,
            dog_image
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        try {
            $stmt->execute([
                $adopter['name'],
                $adopter['age'],
                $adopter['email'],
                $adopter['contact_number'],
                $adopter['house_space'],          
                $adopter['pet_experience'],     
                $adopter['family_composition'], 
                $adopter['address'],
                $dog['dog_breed'],
                $dog['dog_age'],
                $dog['dog_size'],
                $dog['dog_sex'],
                $dog['dog_color'],
                $dog['dog_behavior'],
                $dog['dog_image']
            ]);

            // Redirect to the home page after successful insertion
            header("Location: ../index.php");
            exit(); // Always call exit() after a header redirect
        } catch (PDOException $e) {
            // Log the error for debugging (e.g., to a file, not to the user)
            error_log("PDO Error finalizing adoption: " . $e->getMessage());
            // Display a generic error to the user
            die("An error occurred during adoption finalization. Please try again later.");
        }

    } else {
        // Handle case where adopter or dog data couldn't be retrieved
        error_log("Adopter or dog data not found for finalization (adopter_id: $adopter_id, dog_id: $dog_id).");
        die("Error: Adopter or dog data not found for finalization. Please ensure the IDs are correct.");
    }
}

// --- This part of the code runs when the page is first loaded (GET request) ---
// It fetches data to display on the adoption success page.

$adopter_id = validateId($_GET['adopter_id'] ?? null);
$dog_id = validateId($_GET['dog_id'] ?? null);

if ($adopter_id === false || $dog_id === false) {
    die("Missing or invalid adoption request data for display.");
}

// Fetch adopter info for display
$stmt = $pdo->prepare("SELECT * FROM adopters_info WHERE id = ?");
$stmt->execute([$adopter_id]);
$adopter = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch dog info for display
$stmt = $pdo->prepare("SELECT * FROM adoption_list WHERE id = ?");
$stmt->execute([$dog_id]);
$dog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adopter || !$dog) {
    die("Adopter or dog not found for display.");
}

// Construct the image path for display
// Make sure the path is correct relative to where the image files are stored
$image_filename = basename($dog['dog_image']);
// This path assumes your images are in 'your_project_root/adoption/uploads/'
// and this PHP file is in 'your_project_root/adoption/some_folder/this_file.php'
$image_path = "../adoption/uploads/" . $image_filename;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Pawhub</title>
    <link rel="icon" type="image/png" href="../img/Pawhubicon.png">
    <link rel="stylesheet" href="../css/adoption_success.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav>
        <div class="nav-container">
            <a href="../index.php" class="nav-brand">
                <img src="../img/naic.png" alt="Dog Pound Logo" />
                <span class="nav-brand-text">DOG POUND NAIC</span>
            </a>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li><a href="../Anti-Rabies.php" class="nav-link">Anti-Rabies</a></li>
                <li><a href="../about.php" class="nav-link">About</a></li>
                <li><a href="../login.php" class="nav-link">Admin</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div class="success-header">
            <h2>Adoption Request Confirmed!</h2>
            <p class="subtitle">Thank you, <?= htmlspecialchars($adopter['name']) ?>, for choosing to adopt <?= htmlspecialchars($dog['dog_breed']) ?>. Click <strong>Finish</strong> to finalize your adoption.</p>
        </div>

        <div class="dog-display">
            <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($dog['dog_breed']) ?>" class="dog-image">

            <div class="dog-info">
                <h3><i class="fas fa-paw"></i> <?= htmlspecialchars($dog['dog_breed']) ?></h3>

                <div class="info-grid">
                    <div class="info-item"><i class="fas fa-birthday-cake"></i> <strong>Age:</strong> <?= htmlspecialchars($dog['dog_age']) ?></div>
                    <div class="info-item"><i class="fas fa-arrows-alt-v"></i> <strong>Size:</strong> <?= htmlspecialchars($dog['dog_size']) ?></div>
                    <div class="info-item"><i class="fas fa-venus-mars"></i> <strong>Sex:</strong> <?= htmlspecialchars($dog['dog_sex']) ?></div>
                    <div class="info-item"><i class="fas fa-palette"></i> <strong>Color:</strong> <?= htmlspecialchars($dog['dog_color']) ?></div>
                    <div class="info-item"><i class="fas fa-brain"></i> <strong>Temperament:</strong> <?= htmlspecialchars($dog['dog_behavior']) ?></div>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <a href="matches.php?id=<?= htmlspecialchars($adopter_id) ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Matches</a>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="adopter_id" value="<?= htmlspecialchars($adopter_id) ?>">
                <input type="hidden" name="dog_id" value="<?= htmlspecialchars($dog_id) ?>">
                <button type="submit" name="finalize" class="btn btn-primary"><i class="fas fa-home"></i> Finish</button>
            </form>
        </div>

    </main>

</body>
</html>