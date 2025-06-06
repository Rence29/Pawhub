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
    die("Connection failed: " . $conn->connect_error);
}

$dog = [
    'dog_image' => '',
    'dog_breed' => '',
    'dog_color' => '',
    'dog_age' => '',
    'dog_sex' => '',
    'dog_behavior' => '',
    'dog_size' => '',
    'health_condition' => '' // Added this line
];

$dog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($dog_id === 0) {
    die("Invalid Dog ID provided. Please go back to the adoption list and select a dog to edit.");
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $sql = "SELECT id, dog_image, dog_breed, dog_color, dog_age, dog_sex, dog_behavior, dog_size, health_condition FROM adoption_list WHERE id = ?"; 
    $stmt = $conn->prepare($sql);
    // Check if prepare was successful
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $dog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $dog = $result->fetch_assoc();
    } else {
        die("Dog not found in adoption list with ID: " . htmlspecialchars($dog_id));
    }
    $stmt->close(); // Close the select statement
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

    // Handle image upload if a new file is provided
    if (isset($_FILES["dog_image"]) && !empty($_FILES["dog_image"]["name"])) {
        $original_filename = basename($_FILES["dog_image"]["name"]);
        $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        // Create a unique filename to prevent overwriting
        $unique_filename = uniqid() . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $unique_filename;

        $uploadOk = 1;

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
                echo "<script>alert('Sorry, there was an error uploading your image file.');</script>";
            }
        }
    }

    // Update SQL statement
    // IMPORTANT: Ensure 'health_condition' column exists in your 'adoption_list' table in the database.
    $sql = "UPDATE adoption_list
            SET dog_image = ?, dog_breed = ?, dog_color = ?, dog_age = ?, dog_sex = ?, dog_behavior = ?, dog_size = ?, health_condition = ?
            WHERE id = ?"; // Modified UPDATE query
    $stmt = $conn->prepare($sql);
    // Check if prepare was successful
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    // "ssssssssi" indicates all parameters are strings except the last one which is integer. Adjust if dog_age is INT.
    $stmt->bind_param("ssssssssi", $dog_image, $dog_breed, $dog_color, $dog_age, $dog_sex, $dog_behavior, $dog_size, $health_condition, $dog_id);

    if ($stmt->execute()) {
        header("Location: adoption_list.php");
        exit();
    } else {
        echo "<script>alert('Error updating record: " . $stmt->error . "');</script>";
    }
    $stmt->close(); // Close the update statement
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
                 <li class="menu-item active"> 
                    <a href="adoption_history.php">
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
                                <label for="health_condition">Dog Age (Years):</label>
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

    <script>
        // Add active class to current menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.menu-item a');
            
            menuItems.forEach(item => {
                // Special handling for adoption_edit.php to highlight adoption_list.php in sidebar
                if (currentPage === 'adoption_edit.php' && item.getAttribute('href').includes('adoption_list.php')) {
                    item.classList.add('active');
                } else if (item.getAttribute('href').split('/').pop() === currentPage) {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>