<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    // Set message for modal instead of dying
    $message = "Database Connection failed: " . $conn->connect_error;
    $message_type = 'error';
    error_log($message); // Log the error for debugging
}

$user = null; // Initialize user variable
$message = ""; // Initialize message for the modal
$message_type = ""; // Initialize message type ('success' or 'error')
$should_redirect_on_success = false; // Flag to control redirection after modal close

// Check if the ID is provided in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure the ID is an integer

    // Fetch the user's current data using prepared statement
    $stmt = $conn->prepare("SELECT id, username, full_name, position FROM users WHERE id = ?");
    if ($stmt === false) {
        $message = "Database prepare error: " . $conn->error;
        $message_type = 'error';
        error_log($message);
    } else {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            $message = "User not found.";
            $message_type = 'error';
            // If user not found, we shouldn't proceed with the form, so exit
            // For now, we'll let the modal display the message.
            // In a real application, you might redirect to user_list.php here.
        }
        $stmt->close();
    }
} else {
    $message = "No user ID provided for editing.";
    $message_type = 'error';
    // If no ID, no need to proceed, let the modal show the error
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user !== null) { // Only process POST if a user was successfully fetched
    // Fetch form data
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $position = trim($_POST['position']);
    $password = trim($_POST['password']); // New password, if provided

    // Assume update is successful unless proven otherwise
    $update_successful = false;

    if (!empty($password)) {
        // Hash the new password if provided
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
            $message = 'Error: Password must be at least 8 characters long and include both letters and numbers.';
            $message_type = 'error';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Update with new password
            $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, position = ?, password = ? WHERE id = ?");
            if ($stmt === false) {
                $message = "Database prepare error: " . $conn->error;
                $message_type = 'error';
                error_log($message);
            } else {
                $stmt->bind_param("ssssi", $username, $full_name, $position, $hashed_password, $id);
                if ($stmt->execute()) {
                    $message = "User updated successfully!";
                    $message_type = 'success';
                    $update_successful = true;
                    $should_redirect_on_success = true;
                } else {
                    $message = "Error updating record: " . $stmt->error;
                    $message_type = 'error';
                    error_log("User update error: " . $stmt->error);
                }
                $stmt->close();
            }
        }
    } else {
        // Update without changing password
        $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, position = ? WHERE id = ?");
        if ($stmt === false) {
            $message = "Database prepare error: " . $conn->error;
            $message_type = 'error';
            error_log($message);
        } else {
            $stmt->bind_param("sssi", $username, $full_name, $position, $id);
            if ($stmt->execute()) {
                $message = "User updated successfully!";
                $message_type = 'success';
                $update_successful = true;
                $should_redirect_on_success = true;
            } else {
                $message = "Error updating record: " . $stmt->error;
                $message_type = 'error';
                error_log("User update error: " . $stmt->error);
            }
            $stmt->close();
        }
    }

    // After updating, re-fetch user data to display the latest info on the form
    // unless it was an error and the form data should persist
    if ($update_successful && $should_redirect_on_success) {
        // We will redirect via JS after modal, no need to re-fetch
    } elseif ($message_type === 'error' && $user !== null) {
        // If there was an error and the user was originally found,
        // update the $user array with the submitted (and potentially invalid) data
        // so it appears in the form, allowing the user to correct.
        $user['username'] = $username;
        $user['full_name'] = $full_name;
        $user['position'] = $position;
        // Don't update password in $user, it's never displayed.
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
            <li class="menu-item">
                <a href="../adoption/adoption_history.php">
                    <i class='bx bx-archive'></i>
                    <span>Adoption History</span>
                </a>
            </li>

            <div class="menu-divider"></div>

            <li class="menu-item">
                <a href="user_list.php" class="active"> <i class='bx bx-cog'></i>
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

            <?php if ($user): // Only display form if user data was fetched successfully ?>
            <form method="POST" action="user_edit.php?id=<?= htmlspecialchars($id) ?>">
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
            <?php else: ?>
                <p>Unable to load user data. Please go back to the user list.</p>
            <?php endif; ?>
        </div>
    </main>

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
                const linkHref = item.getAttribute('href').split('/').pop();
                // Special handling: user_edit.php should highlight user_list.php in sidebar
                if (currentPage === 'user_edit.php' && linkHref === 'user_list.php') {
                    item.classList.add('active');
                } else if (linkHref === currentPage) {
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
            const message = "<?php echo isset($message) ? htmlspecialchars($message) : ''; ?>";
            const messageType = "<?php echo isset($message_type) ? htmlspecialchars($message_type) : ''; ?>";
            const shouldRedirectOnSuccess = <?php echo json_encode($should_redirect_on_success); ?>;

            // Only show modal if there's a message
            if (message) {
                modalTitle.textContent = messageType === 'success' ? "Success!" : "Error!";
                modalMessage.textContent = message;
                modalIcon.innerHTML = messageType === 'success' ? "<i class='bx bx-check-circle'></i>" : "<i class='bx bx-x-circle'></i>";
                modalContent.classList.add(messageType);
                statusModal.classList.add('show');
            }

            // Function to handle modal close and potential redirection
            window.handleModalClose = function() {
                statusModal.classList.remove('show');
                modalContent.classList.remove('success', 'error'); // Clean up classes
                if (shouldRedirectOnSuccess) {
                    window.location.href = 'user_list.php'; // Redirect to user_list.php
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