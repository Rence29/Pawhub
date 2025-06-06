<?php
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php"); // Adjust path to your login page
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli('localhost', 'root', '', 'dog_found');
    if ($conn->connect_error) {
        // It's better to log the error and show a generic message to the user
        error_log("Database Connection failed: " . $conn->connect_error);
        die("Connection failed: Please try again later.");
    }

    $dog_breed = isset($_POST['dog_breed']) ? $_POST['dog_breed'] : '';
    $dog_color = isset($_POST['dog_color']) ? $_POST['dog_color'] : '';
    $dog_age = isset($_POST['dog_age']) ? $_POST['dog_age'] : '';
    $dog_sex = isset($_POST['dog_sex']) ? $_POST['dog_sex'] : '';
    $dog_behavior = isset($_POST['dog_behavior']) ? $_POST['dog_behavior'] : '';
    $dog_size = isset($_POST['dog_size']) ? $_POST['dog_size'] : '';
    
    // --- IMPORTANT FIX HERE ---
    // Change 'dog_health_condition' to 'health_condition' to match your HTML form's 'name' attribute
    $dog_health_condition = isset($_POST['health_condition']) ? $_POST['health_condition'] : ''; 
    // --- END FIX ---

    $target_dir = "uploads/";
    // Ensure the uploads directory exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $dog_image = null; // Initialize dog_image to null
    $uploadOk = 1; // Flag for upload status

    // Handle file upload
    if (isset($_FILES["dog_image"]) && $_FILES["dog_image"]["error"] == UPLOAD_ERR_OK) {
        $original_filename = basename($_FILES["dog_image"]["name"]);
        $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        // Create a unique filename to prevent overwriting
        $unique_filename = uniqid() . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $unique_filename;

        // Check if image file is an actual image
        $check = getimagesize($_FILES["dog_image"]["tmp_name"]);
        if ($check === false) {
            echo "<script>alert('File is not an image. Please upload a valid image file (JPG, JPEG, PNG, GIF).');</script>";
            $uploadOk = 0;
        }

        // Check file size (max 500KB)
        if ($_FILES["dog_image"]["size"] > 500000) {
            echo "<script>alert('Sorry, your image file is too large. Max size is 500KB.');</script>";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (!in_array($file_extension, ["jpg", "jpeg", "png", "gif"])) {
            echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed for the dog image.');</script>";
            $uploadOk = 0;
        }

        // Attempt to upload file
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["dog_image"]["tmp_name"], $target_file)) {
                $dog_image = $target_file;
            } else {
                echo "<script>alert('Sorry, there was an error uploading your image file. Error code: " . $_FILES["dog_image"]["error"] . "');</script>";
                $uploadOk = 0;
            }
        }
    } else {
        // Handle cases where no file was selected or an upload error occurred
        if ($_FILES["dog_image"]["error"] == UPLOAD_ERR_NO_FILE) {
             echo "<script>alert('Please select an image file for the dog.');</script>";
        } else {
            echo "<script>alert('An error occurred during image upload: " . $_FILES["dog_image"]["error"] . "');</script>";
        }
        $uploadOk = 0; // Set uploadOk to 0 if no valid file was uploaded
    }

    // Proceed with database insertion only if image upload was successful and other required fields are not empty
    if ($uploadOk == 1 && !empty($dog_breed) && !empty($dog_color) && !empty($dog_age) && !empty($dog_sex) && !empty($dog_behavior) && !empty($dog_size) && !empty($dog_health_condition)) {
        // Using prepared statements for security
        $sql = "INSERT INTO adoption_list (dog_image, dog_breed, dog_color, dog_age, dog_sex, dog_behavior, dog_size, health_condition)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            error_log("Prepare statement failed: " . $conn->error);
            echo "<script>alert('Database error: Unable to prepare statement.');</script>";
        } else {
            // "ssssssss" indicates all parameters are strings. Adjust if dog_age is INT.
            // Ensure data types match your database schema. If dog_age is INT, use "i".
            $stmt->bind_param("ssssssss", $dog_image, $dog_breed, $dog_color, $dog_age, $dog_sex, $dog_behavior, $dog_size, $dog_health_condition);

            if ($stmt->execute()) {
                header("Location: adoption_list.php");
                exit();
            } else {
                error_log("Error inserting dog data: " . $stmt->error);
                echo "<script>alert('Error inserting dog data: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        }
    } else if ($uploadOk == 1) { // If image was uploaded but other required fields are missing
         echo "<script>alert('Please fill in all required dog information fields.');</script>";
    }

    $conn->close();
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

    <script>
        // Add active class to current menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.menu-item a');
            
            menuItems.forEach(item => {
                // Adjust this logic if your hrefs are relative to the root or absolute
                const itemHref = item.getAttribute('href').split('/').pop(); 
                if (itemHref === currentPage) {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>