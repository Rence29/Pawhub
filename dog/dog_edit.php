<?php
// Start the session if not already started (good practice, though not strictly needed for this page without session variables)
// session_start(); 

$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    // Log the error instead of dying directly in production for a better user experience
    error_log("Connection failed: " . $conn->connect_error);
    $error_message = "Database connection failed. Please try again later.";
}

$dog = null; // Initialize $dog to null
$success_message = ""; // Initialize success message for modal
$error_message = "";   // Initialize error message for modal

if (isset($_GET['id'])) {
    $dog_id = $_GET['id'];
    $sql = "SELECT * FROM dogs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    // Check if prepare was successful
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        $error_message = "Failed to prepare dog data retrieval.";
        // Exit if we can't even get the dog data
        // It's better to render the page with an error message than just exit for UX.
        // For now, let's keep it as exit if dog data cannot be fetched for editing.
        // If this were a production app, more graceful error handling would be needed.
        exit; 
    }
    $stmt->bind_param("i", $dog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dog = $result->fetch_assoc();
    if (!$dog) {
        $error_message = "Dog not found with the provided ID.";
        // No dog found, no need to proceed with form or updates
        exit;
    }
    $stmt->close(); // Close the select statement
} else {
    $error_message = "No dog ID provided for editing.";
    // No ID provided, no need to proceed
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and get POST variables
    $dog_control_number = htmlspecialchars($_POST['dog_control_number']);
    $owner_name = htmlspecialchars($_POST['owner_name']);
    $dog_name = htmlspecialchars($_POST['dog_name']);
    $owner_occupation = htmlspecialchars($_POST['owner_occupation']);
    $owner_birthday = htmlspecialchars($_POST['owner_birthday']);
    $dog_origin = htmlspecialchars($_POST['dog_origin']);
    $dog_breed = htmlspecialchars($_POST['dog_breed']);
    $dog_age = htmlspecialchars($_POST['dog_age']);
    $dog_color = htmlspecialchars($_POST['dog_color']);
    $dog_sex = htmlspecialchars($_POST['dog_sex']);
    $barangay = htmlspecialchars($_POST['barangay']);
    $vaccination_status = htmlspecialchars($_POST['vaccination_status']);
    $deceased = htmlspecialchars($_POST['deceased']);
    $registration_date = htmlspecialchars($_POST['registration_date']);

    // Update the SQL query
    $update_sql = "UPDATE dogs SET dog_control_number = ?, owner_name = ?, dog_name = ?, owner_occupation = ?, owner_birthday = ?, dog_origin = ?, dog_breed = ?, dog_age = ?, dog_color = ?, dog_sex = ?, barangay = ?, vaccination_status = ?, deceased = ?, registration_date = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    
    if ($update_stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        $error_message = "Failed to prepare update statement: " . $conn->error;
    } else {
        // CORRECTED LINE 71:
        // Changed "ssssssssssssi" to "ssssssssssssssi" to match the 15 parameters
        // And ensure $dog_id is the last variable.
        $update_stmt->bind_param(
            "ssssssssssssssi", // 14 's' for strings, 1 'i' for integer dog_id
            $dog_control_number, $owner_name, $dog_name, $owner_occupation, $owner_birthday, $dog_origin,
            $dog_breed, $dog_age, $dog_color, $dog_sex, $barangay, $vaccination_status, $deceased, $registration_date, $dog_id
        );
        
        if ($update_stmt->execute()) {
            $success_message = "Dog details updated successfully!";
            // Re-fetch dog data to display updated values in the form after success
            $sql_re_fetch = "SELECT * FROM dogs WHERE id = ?";
            $stmt_re_fetch = $conn->prepare($sql_re_fetch);
            $stmt_re_fetch->bind_param("i", $dog_id);
            $stmt_re_fetch->execute();
            $result_re_fetch = $stmt_re_fetch->get_result();
            $dog = $result_re_fetch->fetch_assoc();
            $stmt_re_fetch->close();
        } else {
            $error_message = "Error updating record: " . $update_stmt->error;
            error_log("Error updating record: " . $update_stmt->error);
        }
        $update_stmt->close(); // Close the update statement
    }
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
                <li class="menu-item"> 
                    <a href="../adoption/adoption_history.php">
                        <i class='bx bx-archive'></i> 
                        <span>Adoption History</span>
                    </a>
                </li>
                
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
                                <label for="dog_control_number">Dog Control Number</label>
                                <input type="text" class="form-control" id="dog_control_number" name="dog_control_number" value="<?= htmlspecialchars($dog['dog_control_number']) ?>" required>
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
                                <label for="dog_origin">Dog Origin </label>
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
                                <label for="dog_sex">Dog Sex </label>
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
                                <label for="barangay">Barangay </label>
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
                                <label for="vaccination_status">Vaccination Status </label>
                                <select class="form-control" id="vaccination_status" name="vaccination_status" required>
                                    <option value="" disabled>Select Status</option>
                                    <option value="Fully Vaccinated" <?= ($dog['vaccination_status'] == 'Fully Vaccinated') ? 'selected' : '' ?>>Vaccinated</option>
                                    <option value="Not Vaccinated" <?= ($dog['vaccination_status'] == 'Not Vaccinated') ? 'selected' : '' ?>>Not Vaccinated</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="deceased">Deceased </label>
                                <select class="form-control" id="deceased" name="deceased" required>
                                    <option value="" disabled>Select</option>
                                    <option value="Yes" <?= ($dog['deceased'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                                    <option value="No" <?= ($dog['deceased'] == 'No') ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="registration_date">Registration Date </label>
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

    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <div class="modal-icon" id="modalIcon"></div>
            <h3 id="modalTitle"></h3>
            <p id="modalMessage"></p>
        </div>
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
                    // Remove 'active' from other items, if any, before adding
                    document.querySelector('.menu-item.active')?.classList.remove('active');
                    item.parentElement.classList.add('active'); // Add active class to the parent li
                } else if (item.getAttribute('href').includes(currentPage)) { // General case for other pages
                    // Remove 'active' from other items, if any, before adding
                    document.querySelector('.menu-item.active')?.classList.remove('active');
                    item.parentElement.classList.add('active');
                }
            });

            // Modal Logic (copied from dog_register.php for consistency)
            const statusModal = document.getElementById('statusModal');
            const closeButton = document.querySelector('.modal .close-button');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalIcon = document.getElementById('modalIcon');
            const modalContent = document.querySelector('.modal-content');

            // PHP variables to JS
            const successMessage = "<?php echo $success_message; ?>";
            const errorMessage = "<?php echo $error_message; ?>";

            let shouldRedirect = false; // Flag to control redirection

            if (successMessage) {
                modalTitle.textContent = "Success!";
                modalMessage.textContent = successMessage;
                modalIcon.innerHTML = "<i class='bx bx-check-circle'></i>"; // Boxicons checkmark
                modalContent.classList.add('success');
                statusModal.classList.add('show');
                shouldRedirect = true; // Set flag to true on success
            } else if (errorMessage) {
                modalTitle.textContent = "Error!";
                modalMessage.textContent = errorMessage;
                modalIcon.innerHTML = "<i class='bx bx-x-circle'></i>"; // Boxicons X mark
                modalContent.classList.add('error');
                statusModal.classList.add('show');
                // No redirect on error, allow user to stay on page and correct input
            }

            // Function to handle modal close and potential redirection
            function handleModalClose() {
                statusModal.classList.remove('show');
                if (shouldRedirect) {
                    window.location.href = 'dog_list.php'; // Redirect to dog_list.php
                }
            }

            // Close modal when clicking on the close button
            closeButton.onclick = handleModalClose;

            // Close modal when clicking anywhere outside of the modal content
            window.onclick = function(event) {
                if (event.target == statusModal) {
                    handleModalClose();
                }
            }
        });
    </script>
</body>
</html>