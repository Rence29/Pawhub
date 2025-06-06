<?php
// CRITICAL: Ensure absolutely NO space or character before this <?php tag.
// It should be the very first thing in the file.

// IMPORTANT: Ensure these include paths are correct relative to matches.php
include 'db.php';         // For database connection (PDO)
include 'ml_service.php'; // For calling the Flask ML API

// Handle redirect to adoption_success.php FIRST, before any other output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dog_id'])) {
    $dog_id = $_POST['dog_id'];
    $adopter_id = $_GET['id']; // Get adopter_id from URL, assuming it's always present for matches.php

    // Path to adoption_success.php. If it's in a different folder, adjust this.
    $redirect_url = "adoption_success.php?adopter_id=$adopter_id&dog_id=$dog_id";

    header("Location: " . $redirect_url);
    exit(); // Ensure script stops immediately after redirect
}

// Rest of your PHP logic (fetching adopter, dogs, performing ML, rendering HTML)
// This part should only execute if it's NOT a POST request (i.e., when loading the page normally)

if (!isset($_GET['id'])) {
    die("Adopter ID is required.");
}

$adopter_id = $_GET['id'];

// --- 1. Fetch Adopter Data ---
$adopter = null;
$stmt_adopter = $pdo->prepare("SELECT name, house_space, lifestyle, family_composition, pet_experience FROM adopters_info WHERE id = ?");
if ($stmt_adopter) {
    $stmt_adopter->execute([$adopter_id]);
    $adopter = $stmt_adopter->fetch(PDO::FETCH_ASSOC);
}

if (!$adopter) {
    die("Adopter not found.");
}

// --- 2. Fetch All Available Dogs ---
$dogs = [];
$stmt_dogs = $pdo->prepare("SELECT id, dog_breed, dog_age, dog_behavior, dog_size, dog_image, dog_sex, health_condition FROM adoption_list WHERE status = 'available'");
if ($stmt_dogs) {
    $stmt_dogs->execute();
    $dogs = $stmt_dogs->fetchAll(PDO::FETCH_ASSOC);
}

// --- 3. Perform Matching using ML API for ALL dogs and store their match status ---
$all_dogs_with_match_status = [];

if (!empty($dogs)) {
    foreach ($dogs as $dog) {
        $pet_experience_for_ml = ($adopter['pet_experience'] === "With Experience") ? "Yes" : "No";

        $input_for_ml = [
            'dog_age' => (int)$dog['dog_age'],
            'house_type' => $adopter['house_space'],
            'family_composition' => $adopter['family_composition'],
            'lifestyle' => $adopter['lifestyle'],
            'pet_experience' => $pet_experience_for_ml,
            'dog_size' => $dog['dog_size'],
            'dog_behavior' => $dog['dog_behavior'],
            'health_condition' => $dog['health_condition'],
        ];

        $prediction_data = getMatchPrediction($input_for_ml);

        $dog['match_status'] = $prediction_data['match_result'] ?? 'Unknown';
        $all_dogs_with_match_status[] = $dog;
    }
}

// --- NEW: Filter to keep only "Good Match" dogs ---
$good_match_dogs = [];
foreach ($all_dogs_with_match_status as $dog) {
    if ($dog['match_status'] === 'Good Match') {
        $good_match_dogs[] = $dog;
    }
}

// You can optionally sort good_match_dogs by another criteria if you wish,
// for now, they are in the order they were processed (which is generally by their ID from DB)
// If you want to sort, e.g., by dog_age, you can add another usort here:
/*
usort($good_match_dogs, function($a, $b) {
    return $a['dog_age'] <=> $b['dog_age']; // Sorts by age ascending
});
*/

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pawhub - Good Match Dogs</title>
    <link rel="icon" type="image/png" href="../img/Pawhubicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/matches.css">
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

<div class="container">
    <?php if (count($good_match_dogs) == 0): // Check only good match dogs count ?>
        <div class="no-good-matches">
            <h3>No Good Matches Found</h3>
            <p>We couldn't find any dogs that are a "Good Match" based on your preferences at the moment. Please check back later, or contact us for more personalized assistance!</p>
            <a href="../index.php" class="home-btn">
                <i class="fas fa-home"></i> Return Home
            </a>
        </div>
    <?php else: // Display good match dogs ?>
        <div class="header">
            <h2>Good Match Dogs for <span class="adopter-name"><?= htmlspecialchars($adopter['name']) ?></span> <span class="match-count">(<?= count($good_match_dogs) ?> total)</span></h2>
            <p>Here are the dogs that are a "Good Match" based on your preferences!</p>
        </div>

        <div class="card-grid">
            <?php foreach ($good_match_dogs as $dog): // Loop through only good match dogs ?>
                <div class="card">
                    <?php $image_path = "../adoption/uploads/" . basename($dog['dog_image']); ?>
                    <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($dog['dog_breed']) ?>" class="card-img">
                    <div class="card-body">
                        <h3><?= htmlspecialchars($dog['dog_breed']) ?></h3>
                        <div class="info-item"><i class="fas fa-birthday-cake"></i><span><strong>Age:</strong> <?= htmlspecialchars($dog['dog_age']) ?> years</span></div>
                        <div class="info-item"><i class="fas fa-dog"></i><span><strong>Breed:</strong> <?= htmlspecialchars($dog['dog_breed']) ?></span></div>
                        <div class="info-item"><i class="fas fa-venus-mars"></i><span><strong>Sex:</strong> <?= htmlspecialchars($dog['dog_sex']) ?></span></div>
                        <div class="info-item"><i class="fas fa-arrows-alt-v"></i><span><strong>Size:</strong> <?= htmlspecialchars($dog['dog_size']) ?></span></div>
                        <div class="info-item"><i class="fas fa-brain"></i><span><strong>Behavior:</strong> <?= htmlspecialchars($dog['dog_behavior']) ?></span></div>
                        <div class="info-item"><i class="fas fa-heartbeat"></i><span><strong>Health:</strong> <?= htmlspecialchars($dog['health_condition']) ?></span></div>

                        <?php
                        // Display the match status (it will always be 'Good Match' here)
                        $status_class = 'good'; // Since we are only showing good matches
                        ?>

                        <form method="POST">
                            <input type="hidden" name="dog_id" value="<?= $dog['id'] ?>">
                            <button type="submit" class="request-btn"><i class="fas fa-heart"></i> Adopt</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
