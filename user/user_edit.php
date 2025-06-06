<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = null; // Initialize user variable

// Check if the ID is provided in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure the ID is an integer

    // Fetch the user's current data using prepared statement
    $stmt = $conn->prepare("SELECT id, username, full_name, position FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit;
    }
    $stmt->close();
} else {
    echo "No ID provided.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch form data
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $position = trim($_POST['position']);
    $password = trim($_POST['password']); // New password, if provided

    $update_sql = "";
    if (!empty($password)) {
        // Hash the new password if provided
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
            echo "<script>alert('Error: Password must be at least 8 characters long and include both letters and numbers.'); window.location.href='user_edit.php?id=$id';</script>";
            exit;
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Update with new password
        $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, position = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $username, $full_name, $position, $hashed_password, $id);
    } else {
        // Update without changing password
        $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, position = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $full_name, $position, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('User updated successfully!'); window.location.href='user_list.php';</script>";
    } else {
        echo "Error updating record: " . $stmt->error;
    }
    $stmt->close();
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
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="../css/user_edit.css">
</head>
<body>

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
                <a href="user/user_list.php" class="active">
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

    <main class="main-wrapper">
        <div class="top-nav">
            <div class="page-title">
                <h1>Edit User</h1>
            </div>
        </div>

        <div class="form-container">
            <h2>Edit User Details</h2>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        value="<?php echo htmlspecialchars($user['username']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input
                        type="text"
                        class="form-control"
                        id="full_name"
                        name="full_name"
                        value="<?php echo htmlspecialchars($user['full_name']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="position">Position</label>
                    <input
                        type="text"
                        class="form-control"
                        id="position"
                        name="position"
                        value="<?php echo htmlspecialchars($user['position']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="password">New Password (leave blank to keep current)</label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Enter new password">
                    <ion-icon class="show-hide toggle-password" name="eye-outline"></ion-icon>
                </div>

                <div class="form-actions">
                    <a href="user_list.php" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', () => {
                const passwordInput = document.getElementById('password');
                const isPasswordShown = passwordInput.type === 'text';

                if (isPasswordShown) {
                    passwordInput.type = 'password';
                    icon.name = 'eye-outline';
                } else {
                    passwordInput.type = 'text';
                    icon.name = 'eye-off-outline';
                }
            });
        });

        // Add active class to current menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.menu-item a');

            menuItems.forEach(item => {
                const linkHref = item.getAttribute('href');
                if (linkHref && linkHref.endsWith(currentPage)) {
                    item.classList.add('active');
                }
            });
        });
    </script>

</body>
</html>