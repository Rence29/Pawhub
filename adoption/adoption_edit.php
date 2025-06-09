<?php
// Start the session if not already started
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php"); // Adjust path to your login page
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    // Log the error instead of dying directly
    error_log("Database Connection failed: " . $conn->connect_error);
    $message = "Database connection failed. Please try again later.";
    $message_type = 'error';
}

$dog = [
    'dog_image' => '',
    'dog_breed' => '',
    'dog_color' => '',
    'dog_age' => '',
    'dog_sex' => '',
    'dog_behavior' => '',
    'dog_size' => '',
    'health_condition' => ''
];

$dog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($dog_id === 0) {
    // Display error using the modal
    $message = "Invalid Dog ID provided. Please go back to the adoption list and select a dog to edit.";
    $message_type = 'error';
    // Exit if ID is invalid, no further processing needed
    // You might want to redirect here if it's a critical error
} else { // Proceed only if a valid ID is present
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $sql = "SELECT id, dog_image, dog_breed, dog_color, dog_age, dog_sex, dog_behavior, dog_size, health_condition FROM adoption_list WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            $message = "Database prepare error. Please try again.";
            $message_type = 'error';
        } else {
            $stmt->bind_param("i", $dog_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $dog = $result->fetch_assoc();
            } else {
                $message = "Dog not found in adoption list with ID: " . htmlspecialchars($dog_id);
                $message_type = 'error';
            }
            $stmt->close();
        }
    } elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
        $dog_breed = $_POST['dog_breed'];
        $dog_color = $_POST['dog_color'];
        $dog_age = $_POST['dog_age'];
        $dog_sex = $_POST['dog_sex'];
        $dog_behavior = $_POST['dog_behavior'];
        $dog_size = $_POST['dog_size'];
        $health_condition = $_POST['health_condition'];

        $target_dir = "uploads/";
        $dog_image = $_POST['existing_image'] ?? ''; // Keep existing image if no new one is uploaded

        $uploadOk = 1; // Assume upload is OK unless proven otherwise

        // Handle image upload if a new file is provided
        if (isset($_FILES["dog_image"]) && !empty($_FILES["dog_image"]["name"])) {
            $original_filename = basename($_FILES["dog_image"]["name"]);
            $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
            $unique_filename = uniqid() . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $unique_filename;

            // Check if image file is an actual image
            $check = getimagesize($_FILES["dog_image"]["tmp_name"]);
            if ($check === false) {
                $message = 'File is not an image. Please upload a valid image file (JPG, JPEG, PNG, GIF).';
                $message_type = 'error';
                $uploadOk = 0;
            }

            // Check file size (max 500KB)
            if ($_FILES["dog_image"]["size"] > 500000) {
                $message = 'Sorry, your image file is too large. Max size is 500KB.';
                $message_type = 'error';
                $uploadOk = 0;
            }

            // Allow certain file formats
            if (!in_array($file_extension, ["jpg", "jpeg", "png", "gif"])) {
                $message = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed for the dog image.';
                $message_type = 'error';
                $uploadOk = 0;
            }

            // Attempt to upload file
            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["dog_image"]["tmp_name"], $target_file)) {
                    $dog_image = $target_file;
                } else {
                    $message = 'Sorry, there was an error uploading your image file. Error code: ' . $_FILES["dog_image"]["error"];
                    $message_type = 'error';
                    $uploadOk = 0;
                }
            }
        }

        // Only proceed with database update if image upload was successful (or no new image was provided)
        // and required fields are not empty
        if ($uploadOk == 1) {
            $sql = "UPDATE adoption_list
                    SET dog_image = ?, dog_breed = ?, dog_color = ?, dog_age = ?, dog_sex = ?, dog_behavior = ?, dog_size = ?, health_condition = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                error_log("Prepare failed: " . $conn->error);
                $message = "Database prepare error: Unable to prepare statement.";
                $message_type = 'error';
            } else {
                $stmt->bind_param("ssssssssi", $dog_image, $dog_breed, $dog_color, $dog_age, $dog_sex, $dog_behavior, $dog_size, $health_condition, $dog_id);

                if ($stmt->execute()) {
                    $message = "Dog adoption record updated successfully!";
                    $message_type = 'success';
                } else {
                    error_log("Error updating record: " . $stmt->error);
                    $message = "Error updating record: " . $stmt->error;
                    $message_type = 'error';
                }
                $stmt->close();
            }
        }
    }
}
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
    <link rel="stylesheet" href="../css/adoption_edit.css">
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
                    <a href="../dog/dog_register.php">
                        <i class='bx bx-folder-plus'></i>
                        <span>Register Dog</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../dog/dog_list.php">
                        <i class='bx bx-list-ul'></i>
                        <span>Dog List</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="adoption_list.php" class="active">
                        <i class='bx bxs-carousel'></i>
                        <span>Adoption List</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="adoption_record.php">
                        <i class='bx bx-history'></i>
                        <span>Adopt Requests</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="adoption_history.php">
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
                    <h1>Edit Dog for Adoption</h1>
                </div>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2>Update Dog for Adoption</h2>
                    <p>Please update the information for the dog's adoption profile.</p>
                </div>

                <form action="adoption_edit.php?id=<?= htmlspecialchars($dog_id) ?>" method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div>
                            <div class="form-group">
                                <label for="dog_image">Dog Image:</label>
                                <?php if (!empty($dog['dog_image'])): ?>
                                    <div class="current-image-preview">
                                        <img src="<?= htmlspecialchars($dog['dog_image']) ?>" alt="Current Dog Image">
                                        <p>Current Image</p>
                                    </div>
                                <?php endif; ?>
                                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($dog['dog_image']) ?>">
                                <input type="file" class="form-control" id="dog_image" name="dog_image" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label for="dog_breed">Dog Breed:</label>
                                <input type="text" class="form-control" id="dog_breed" name="dog_breed" value="<?= htmlspecialchars($dog['dog_breed']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="dog_color">Dog Color:</label>
                                <input type="text" class="form-control" id="dog_color" name="dog_color" value="<?= htmlspecialchars($dog['dog_color']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="dog_age">Dog Age (Years):</label>
                                <select class="form-control" id="dog_age" name="dog_age" required>
                                    <option value="" disabled>Select Age</option>
                                    <option value="1-3 Years old" <?= ($dog['dog_age'] == '1-3 Years old') ? 'selected' : '' ?>>1-3 Years old</option>
                                    <option value="4-6 Years old" <?= ($dog['dog_age'] == '4-6 Years old') ? 'selected' : '' ?>>4-6 Years old</option>
                                    <option value="7 Years old above" <?= ($dog['dog_age'] == '7 Years old above') ? 'selected' : '' ?>>7 Years old above</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="dog_sex">Dog Sex:</label>
                                <select class="form-control" id="dog_sex" name="dog_sex" required>
                                    <option value="" disabled>Select Sex</option>
                                    <option value="Male" <?= ($dog['dog_sex'] == 'Male') ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($dog['dog_sex'] == 'Female') ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dog_behavior">Dog Behavior:</label>
                                <select class="form-control" id="dog_behavior" name="dog_behavior" required>
                                    <option value="" disabled>Select Behavior</option>
                                    <option value="Energetic" <?= ($dog['dog_behavior'] == 'Energetic') ? 'selected' : '' ?>>Energetic</option>
                                    <option value="Calm" <?= ($dog['dog_behavior'] == 'Calm') ? 'selected' : '' ?>>Calm</option>
                                    <option value="Aggressive" <?= ($dog['dog_behavior'] == 'Aggressive') ? 'selected' : '' ?>>Aggressive</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dog_size">Dog Size:</label>
                                <select class="form-control" id="dog_size" name="dog_size" required>
                                    <option value="" disabled>Select Size</option>
                                    <option value="Small" <?= ($dog['dog_size'] == 'Small') ? 'selected' : '' ?>>Small</option>
                                    <option value="Medium" <?= ($dog['dog_size'] == 'Medium') ? 'selected' : '' ?>>Medium</option>
                                    <option value="Large" <?= ($dog['dog_size'] == 'Large') ? 'selected' : '' ?>>Large</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="health_condition">Health Condition:</label>
                                <input type="text" class="form-control" id="health_condition" name="health_condition" value="<?= htmlspecialchars($dog['health_condition']) ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="button-group">
                        <a href="adoption_list.php" class="btn btn-secondary">Back to List</a>
                        <button type="submit" class="btn" name="submit">Update Dog for Adoption</button>
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
            <button class="btn" onclick="handleModalClose()">OK</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add active class to current menu item
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.menu-item a');

            menuItems.forEach(item => {
                const itemHref = item.getAttribute('href').split('/').pop();
                // Special handling for adoption_edit.php to highlight adoption_list.php in sidebar
                if (currentPage === 'adoption_edit.php' && itemHref === 'adoption_list.php') {
                    item.classList.add('active');
                } else if (itemHref === currentPage) {
                    item.classList.add('active');
                }
            });

            // Modal Logic
            const statusModal = document.getElementById('statusModal');
            const closeButton = statusModal.querySelector('.close-button');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalIcon = document.getElementById('modalIcon');
            const modalContent = statusModal.querySelector('.modal-content');

            // PHP variables to JS
            const message = "<?php echo isset($message) ? $message : ''; ?>";
            const messageType = "<?php echo isset($message_type) ? $message_type : ''; ?>";

            let shouldRedirect = false;

            // Only show modal if there's a message
            if (message) {
                modalTitle.textContent = messageType === 'success' ? "Success!" : "Error!";
                modalMessage.textContent = message;
                modalIcon.innerHTML = messageType === 'success' ? "<i class='bx bx-check-circle'></i>" : "<i class='bx bx-x-circle'></i>";
                modalContent.classList.add(messageType);
                statusModal.classList.add('show');

                if (messageType === 'success') {
                    shouldRedirect = true;
                }
            }

            // Function to handle modal close and potential redirection
            window.handleModalClose = function() {
                statusModal.classList.remove('show');
                modalContent.classList.remove('success', 'error'); // Clean up classes
                if (shouldRedirect) {
                    window.location.href = 'adoption_list.php'; // Redirect to adoption_list.php
                }
            };

            // Close modal when clicking on the close button
            closeButton.onclick = handleModalClose;

            // Close modal when clicking anywhere outside of the modal content
            window.onclick = function(event) {
                if (event.target == statusModal) {
                    handleModalClose();
                }
            };
        });
    </script>
</body>
</html>