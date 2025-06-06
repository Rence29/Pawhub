<?php
// Start the session if not already started (good practice, though not strictly needed for this page without session variables)
// session_start(); 

$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$dog = null; // Initialize $dog to null

if (isset($_GET['id'])) {
    $dog_id = $_GET['id'];
    $sql = "SELECT * FROM dogs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    // Check if prepare was successful
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $dog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dog = $result->fetch_assoc();
    if (!$dog) {
        echo "Dog not found.";
        exit;
    }
    $stmt->close(); // Close the select statement
} else {
    echo "No dog id provided.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $owner_name = $_POST['owner_name'];
    $dog_name = $_POST['dog_name'];
    $owner_occupation = $_POST['owner_occupation'];
    $owner_birthday = $_POST['owner_birthday'];
    // Removed owner_contact_number from $_POST
    $dog_origin = $_POST['dog_origin'];
    $dog_breed = $_POST['dog_breed'];
    $dog_age = $_POST['dog_age'];
    $dog_color = $_POST['dog_color'];
    $dog_sex = $_POST['dog_sex'];
    $barangay = $_POST['barangay']; 
    $vaccination_status = $_POST['vaccination_status'];
    $deceased = $_POST['deceased'];
    $registration_date = $_POST['registration_date'];

    // Update the SQL query - owner_contact_number column removed
    $update_sql = "UPDATE dogs SET owner_name = ?, dog_name = ?, owner_occupation = ?, owner_birthday = ?, dog_origin = ?, dog_breed = ?, dog_age = ?, dog_color = ?, dog_sex = ?, barangay = ?, vaccination_status = ?, deceased = ?, registration_date = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    // Check if prepare was successful
    if ($update_stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    // Updated bind_param string - 's' for owner_contact_number removed
    $update_stmt->bind_param("sssssssssssssi", $owner_name, $dog_name, $owner_occupation, $owner_birthday, $dog_origin, $dog_breed, $dog_age, $dog_color, $dog_sex, $barangay, $vaccination_status, $deceased, $registration_date, $dog_id);
    
    if ($update_stmt->execute()) {
        header("Location: dog_list.php");
        exit;
    } else {
        echo "Error updating record: " . $update_stmt->error;
    }
    $update_stmt->close(); // Close the update statement
}

// Hardcoded list of barangays for the dropdown
$barangay_options = [
    "Bagong Kalsada", "Balsahan", "Bancaan", "Bucana Malaki", "Bucana Sasahan", 
    "Calubcob", "Capt. C. Nazareno (Poblacion)", "Gombalza (Poblacion)", "Halang", 
    "Humbac", "Ibayo Estacion", "Ibayo Silangan", "Kanluran Rizal", "Latoria", 
    "Labac", "Mabolo", "Malainen Bago", "Malainen Luma", "Maquina", "Molino", 
    "Munting Mapino", "Muzon", "Palangue 2 & 3", "Palangue Central", "Sabang", 
    "San Roque", "Santulan", "Sapa", "Timalan Balsahan", "Timalan Concepcion"
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pawhub</title>
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../img/Pawhubicon.png">
    <link rel="stylesheet" href="../css/dog_edit.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../img/indexlogo.png" alt="PawHub Logo">
                <h2>PawHub</h2>
            </div>
            
            <ul class="menu">
                <li class="menu-item">
                    <a href="../home.php">
                        <i class='bx bx-home-alt'></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="dog_register.php">
                        <i class='bx bx-folder-plus'></i>
                        <span>Register Dog</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="dog_list.php" class="active"> <i class='bx bx-list-ul'></i>
                        <span>Dog List</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../adoption/adoption_list.php">
                        <i class='bx bxs-carousel'></i>
                        <span>Adoption List</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../adoption/adoption_record.php">
                        <i class='bx bx-history'></i>
                        <span>Adopt Requests</span>
                    </a>
                </li>
                 <li class="menu-item active"> 
                    <a href="../adoption/adoption_history.php">
                        <i class='bx bx-archive'></i> 
                        <span>Adoption History</span>
                    </a>
                
                <div class="menu-divider"></div>
                
                <li class="menu-item">
                    <a href="../user/user_list.php">
                        <i class='bx bx-cog'></i>
                        <span>User Settings</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../logout.php">
                        <i class='bx bx-log-out'></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-nav">
                <div class="page-title">
                    <h1>Edit Dog Details</h1>
                </div>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2>Update Dog Information</h2>
                    <p>Modify the details for <?php echo htmlspecialchars($dog['dog_name']); ?></p>
                </div>

                <form action="dog_edit.php?id=<?= htmlspecialchars($dog_id) ?>" method="POST">
                    <div class="form-grid">
                        <div>
                            <div class="form-group">
                                <label for="dog_control_number">Dog Control Number *</label>
                                <input type="text" class="form-control" id="dog_control_number" name="dog_control_number" value="<?= htmlspecialchars($dog['dog_control_number']) ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="owner_name">Owner Name</label>
                                <input type="text" class="form-control" id="owner_name" name="owner_name" value="<?= htmlspecialchars($dog['owner_name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="dog_name">Dog Name</label>
                                <input type="text" class="form-control" id="dog_name" name="dog_name" value="<?= htmlspecialchars($dog['dog_name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="owner_occupation">Owner Occupation</label>
                                <input type="text" class="form-control" id="owner_occupation" name="owner_occupation" value="<?= htmlspecialchars($dog['owner_occupation']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="owner_birthday">Owner Birthday</label>
                                <input type="date" class="form-control" id="owner_birthday" name="owner_birthday" value="<?= htmlspecialchars($dog['owner_birthday']) ?>" required>
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="dog_origin">Dog Origin *</label>
                                <select class="form-control" id="dog_origin" name="dog_origin" required>
                                    <option value="" disabled>Select Origin</option>
                                    <option value="Local" <?= ($dog['dog_origin'] == 'Local') ? 'selected' : '' ?>>Local</option>
                                    <option value="International" <?= ($dog['dog_origin'] == 'International') ? 'selected' : '' ?>>International</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="dog_breed">Dog Breed</label>
                                <input type="text" class="form-control" id="dog_breed" name="dog_breed" value="<?= htmlspecialchars($dog['dog_breed']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="dog_color">Dog Color</label>
                                <input type="text" class="form-control" id="dog_color" name="dog_color" value="<?= htmlspecialchars($dog['dog_color']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="dog_age">Dog Age</label>
                                <input type="text" class="form-control" id="dog_age" name="dog_age" value="<?= htmlspecialchars($dog['dog_age']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="dog_sex">Dog Sex *</label>
                                <select class="form-control" id="dog_sex" name="dog_sex" required>
                                    <option value="" disabled>Select Sex</option>
                                    <option value="Male Castrated" <?= ($dog['dog_sex'] == 'Male Castrated') ? 'selected' : '' ?>>Male Castrated</option>
                                    <option value="Male Intact" <?= ($dog['dog_sex'] == 'Male Intact') ? 'selected' : '' ?>>Male Intact</option>
                                    <option value="Female Spayed" <?= ($dog['dog_sex'] == 'Female Spayed') ? 'selected' : '' ?>>Female Spayed</option> 
                                    <option value="Female Intact" <?= ($dog['dog_sex'] == 'Female Intact') ? 'selected' : '' ?>>Female Intact</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="barangay">Barangay *</label>
                                <select class="form-control" id="barangay" name="barangay" required>
                                    <option value="" disabled selected>Select Barangay</option>
                                    <?php foreach ($barangay_options as $b_option): ?>
                                        <option value="<?= htmlspecialchars($b_option) ?>" <?= ($dog['barangay'] == $b_option) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($b_option) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="vaccination_status">Vaccination Status *</label>
                                <select class="form-control" id="vaccination_status" name="vaccination_status" required>
                                    <option value="" disabled>Select Status</option>
                                    <option value="Fully Vaccinated" <?= ($dog['vaccination_status'] == 'Fully Vaccinated') ? 'selected' : '' ?>>Vaccinated</option>
                                    <option value="Not Vaccinated" <?= ($dog['vaccination_status'] == 'Not Vaccinated') ? 'selected' : '' ?>>Not Vaccinated</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="deceased">Deceased *</label>
                                <select class="form-control" id="deceased" name="deceased" required>
                                    <option value="" disabled>Select</option>
                                    <option value="Yes" <?= ($dog['deceased'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                                    <option value="No" <?= ($dog['deceased'] == 'No') ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="registration_date">Registration Date *</label>
                                <input type="date" class="form-control" id="registration_date" name="registration_date" value="<?= htmlspecialchars($dog['registration_date']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="dog_list.php" class="btn btn-secondary">
                            <i class='bx bx-arrow-back'></i> Back to List
                        </a>
                        <button type="submit" class="btn">
                            <i class='bx bx-save'></i> Update Dog
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Add active class to current menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.menu-item a');
            
            menuItems.forEach(item => {
                // If on dog_edit.php, we want dog_list.php to be active in the sidebar
                // This accounts for the common scenario where edit pages are linked from a list page.
                if (currentPage === 'dog_edit.php' && item.getAttribute('href').includes('dog_list.php')) {
                    item.classList.add('active');
                } else if (item.getAttribute('href').includes(currentPage)) { // General case for other pages
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>