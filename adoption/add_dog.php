<?php
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php"); // Adjust path to your login page
    exit();
}

// Initialize message variables
$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli('localhost', 'root', '', 'dog_found');
    if ($conn->connect_error) {
        error_log("Database Connection failed: " . $conn->connect_error);
        $message = "Connection failed: Please try again later.";
        $message_type = 'error';
    } else {
        $dog_breed = isset($_POST['dog_breed']) ? $_POST['dog_breed'] : '';
        $dog_color = isset($_POST['dog_color']) ? $_POST['dog_color'] : '';
        $dog_age = isset($_POST['dog_age']) ? $_POST['dog_age'] : '';
        $dog_sex = isset($_POST['dog_sex']) ? $_POST['dog_sex'] : '';
        $dog_behavior = isset($_POST['dog_behavior']) ? $_POST['dog_behavior'] : '';
        $dog_size = isset($_POST['dog_size']) ? $_POST['dog_size'] : '';
        $dog_health_condition = isset($_POST['health_condition']) ? $_POST['health_condition'] : '';

        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $dog_image = null;
        $uploadOk = 1;

        // Handle file upload
        if (isset($_FILES["dog_image"]) && $_FILES["dog_image"]["error"] == UPLOAD_ERR_OK) {
            $original_filename = basename($_FILES["dog_image"]["name"]);
            $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
            $unique_filename = uniqid() . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $unique_filename;

            $check = getimagesize($_FILES["dog_image"]["tmp_name"]);
            if ($check === false) {
                $message = 'File is not an image. Please upload a valid image file (JPG, JPEG, PNG, GIF).';
                $message_type = 'error';
                $uploadOk = 0;
            }

            if ($_FILES["dog_image"]["size"] > 500000) {
                $message = 'Sorry, your image file is too large. Max size is 500KB.';
                $message_type = 'error';
                $uploadOk = 0;
            }

            if (!in_array($file_extension, ["jpg", "jpeg", "png", "gif"])) {
                $message = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed for the dog image.';
                $message_type = 'error';
                $uploadOk = 0;
            }

            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["dog_image"]["tmp_name"], $target_file)) {
                    $dog_image = $target_file;
                } else {
                    $message = 'Sorry, there was an error uploading your image file. Error code: ' . $_FILES["dog_image"]["error"];
                    $message_type = 'error';
                    $uploadOk = 0;
                }
            }
        } else {
            if (isset($_FILES["dog_image"]) && $_FILES["dog_image"]["error"] == UPLOAD_ERR_NO_FILE) {
                $message = 'Please select an image file for the dog.';
                $message_type = 'error';
            } else if (isset($_FILES["dog_image"])) { // Other upload errors
                $message = 'An error occurred during image upload: ' . $_FILES["dog_image"]["error"];
                $message_type = 'error';
            } else { // No file was even attempted to be uploaded (e.g., if input was missing)
                 $message = 'Image file input missing or invalid.';
                 $message_type = 'error';
            }
            $uploadOk = 0;
        }

        // Proceed with database insertion only if image upload was successful and other required fields are not empty
        if ($uploadOk == 1 && !empty($dog_breed) && !empty($dog_color) && !empty($dog_age) && !empty($dog_sex) && !empty($dog_behavior) && !empty($dog_size) && !empty($dog_health_condition)) {
            // Using prepared statements for security
            $sql = "INSERT INTO adoption_list (dog_image, dog_breed, dog_color, dog_age, dog_sex, dog_behavior, dog_size, health_condition)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                error_log("Prepare statement failed: " . $conn->error);
                $message = 'Database error: Unable to prepare statement.';
                $message_type = 'error';
            } else {
                // "ssssssss" indicates all parameters are strings. Adjust if dog_age is INT.
                // Ensure data types match your database schema. If dog_age is INT, use "i".
                $stmt->bind_param("ssssssss", $dog_image, $dog_breed, $dog_color, $dog_age, $dog_sex, $dog_behavior, $dog_size, $dog_health_condition);

                if ($stmt->execute()) {
                    $message = 'Dog added for adoption successfully!';
                    $message_type = 'success';
                } else {
                    error_log("Error inserting dog data: " . $stmt->error);
                    $message = 'Error inserting dog data: ' . $stmt->error;
                    $message_type = 'error';
                }
                $stmt->close();
            }
        } else if ($uploadOk == 1 && (empty($dog_breed) || empty($dog_color) || empty($dog_age) || empty($dog_sex) || empty($dog_behavior) || empty($dog_size) || empty($dog_health_condition))) {
            $message = 'Please fill in all required dog information fields.';
            $message_type = 'error';
        }

        $conn->close();
    }
}
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
    <link rel="stylesheet" href="../css/add_dog.css">
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
                    <a href="adoption_list.php" class="active"> <i class='bx bxs-carousel'></i>
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
                    <h1>Add Dog for Adoption</h1>
                </div>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2>Add New Dog for Adoption</h2>
                    <p>Please fill in all required information for the dog's adoption profile.</p>
                </div>

                <form action="add_dog.php" method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div>
                            <div class="form-group">
                                <label for="dog_image">Dog Image:</label>
                                <input type="file" class="form-control" id="dog_image" name="dog_image" accept="image/*" required>
                            </div>
                            <div class="form-group">
                                <label for="dog_breed">Dog Breed:</label>
                                <input type="text" class="form-control" id="dog_breed" name="dog_breed" placeholder="(Mongrel) Aspin" required>
                            </div>
                            <div class="form-group">
                                <label for="dog_color">Dog Color:</label>
                                <input type="text" class="form-control" id="dog_color" name="dog_color" placeholder="Black" required>
                            </div>
                            <div class="form-group">
                                <label for="dog_age">Dog Age (Years):</label>
                                <select class="form-control" id="dog_age" name="dog_age" required>
                                    <option value="" disabled selected>Select Age</option>
                                    <option value="1-3 years old">1-3 years old</option>
                                    <option value="4-6 years old">4-6 years old</option>
                                    <option value="7 years old and above">7 years old and above</option>
                                </select>
                            </div> 
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="dog_sex">Dog Sex:</label>
                                <select class="form-control" id="dog_sex" name="dog_sex" required>
                                    <option value="" disabled selected>Select Sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dog_behavior">Dog Behavior:</label>
                                <select class="form-control" id="dog_behavior" name="dog_behavior" required>
                                    <option value="" disabled selected>Select Behavior</option>
                                    <option value="Energetic">Energetic</option>
                                    <option value="Calm">Calm</option>
                                    <option value="Aggressive">Aggressive</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dog_size">Dog Size:</label>
                                <select class="form-control" id="dog_size" name="dog_size" required>
                                    <option value="" disabled selected>Select Size</option>
                                    <option value="Small">Small</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Large">Large</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="health_condition">Health Condition:</label>
                                <input type="text" class="form-control" id="health_condition" name="health_condition" value="Healthy" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="button-group">
                        <a href="adoption_list.php" class="btn btn-secondary">Back to List</a>
                        <button type="submit" class="btn" name="submit">Add Dog</button>
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
            <button class="btn" onclick="handleModalClose()">OK</button> </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add active class to current menu item
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.menu-item a');
            
            menuItems.forEach(item => {
                const itemHref = item.getAttribute('href').split('/').pop(); 
                // Special handling for 'add_dog.php' if 'adoption_list.php' is its parent menu item
                if (itemHref === currentPage || (currentPage === 'add_dog.php' && itemHref === 'adoption_list.php')) {
                    item.classList.add('active');
                }
            });

            // Modal Logic
            const statusModal = document.getElementById('statusModal');
            const closeButton = statusModal.querySelector('.close-button'); // Select from statusModal
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalIcon = document.getElementById('modalIcon');
            const modalContent = statusModal.querySelector('.modal-content'); // Select from statusModal

            // PHP variables to JS
            const message = "<?php echo $message; ?>";
            const messageType = "<?php echo $message_type; ?>";

            let shouldRedirect = false; // Flag to control redirection

            if (message) { // Only show modal if there's a message
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
            window.handleModalClose = function() { // Make it global so HTML onclick can access it
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