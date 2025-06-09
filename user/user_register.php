<?php
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    // Set message for modal instead of dying
    $message = "Database Connection failed: " . $conn->connect_error;
    $message_type = 'error';
    // Consider logging the error for debugging
    error_log($message);
}

$message = ""; // Initialize message for the modal
$message_type = ""; // Initialize message type ('success' or 'error')

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $position = trim($_POST['position']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username) || empty($full_name) || empty($position) || empty($password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $message_type = 'error';
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = 'error';
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        $message = "Password must be at least 8 characters long and include both letters and numbers.";
        $message_type = 'error';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Username already exists. Please choose another.";
            $message_type = 'error';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, full_name, position, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $full_name, $position, $hashed_password);

            if ($stmt->execute()) {
                $message = "User registered successfully.";
                $message_type = 'success';
                // Optionally clear the form fields after successful registration
                $username = $full_name = $position = $password = $confirm_password = '';
            } else {
                $message = "Error: " . $stmt->error;
                $message_type = 'error';
                error_log("User registration error: " . $stmt->error); // Log database error
            }
            $stmt->close();
        }
        $check->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Pawhub</title>
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../img/Pawhubicon.png">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="../css/user_register.css">
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
                <a href="user_list.php" class="active">
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
                <h1>Register New User</h1>
            </div>
        </div>

        <div class="form-container">
            <h2>Register New User</h2>

            <form method="post" action="user_register.php">
                <div class="form-group">
                    <label for="username">Username (must be unique)</label>
                    <input type="text" id="username" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required />
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Full name" value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required />
                </div>
                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position" placeholder="Position" value="<?php echo htmlspecialchars($position ?? ''); ?>" required />
                </div>
                <div class="form-group">
                    <label for="password">Password (min 8 characters, letters & numbers)</label>
                    <input type="password" id="password" name="password" placeholder="Password" required />
                    <ion-icon class="show-hide toggle-password" name="eye-outline"></ion-icon>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required />
                    <ion-icon class="show-hide toggle-password" name="eye-outline"></ion-icon>
                </div>

                <div class="form-actions">
                    <a href="user_list.php" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
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
        // Toggle both password fields and eye icons simultaneously
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', () => {
                const passwordInput = document.getElementById('password');
                const confirmInput = document.getElementById('confirm_password');
                const isPasswordShown = passwordInput.type === 'text';

                // Check which input is associated with the clicked icon
                // This approach is more robust if there are multiple password fields with individual toggles
                // However, since your current script toggles both, we'll keep that behavior.
                // If you want individual toggles, you'd modify this to find the *sibling* input.
                if (isPasswordShown) {
                    // Hide both passwords
                    passwordInput.type = 'password';
                    confirmInput.type = 'password';
                    document.querySelectorAll('.toggle-password').forEach(ic => ic.name = 'eye-outline');
                } else {
                    // Show both passwords
                    passwordInput.type = 'text';
                    confirmInput.type = 'text';
                    document.querySelectorAll('.toggle-password').forEach(ic => ic.name = 'eye-off-outline');
                }
            });
        });

        // Add active class to current menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.menu-item a');

            menuItems.forEach(item => {
                const linkHref = item.getAttribute('href').split('/').pop();
                if (linkHref === currentPage) {
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

            let shouldClearForm = false; // Flag to indicate if form should be cleared

            // Only show modal if there's a message
            if (message) {
                modalTitle.textContent = messageType === 'success' ? "Success!" : "Error!";
                modalMessage.textContent = message;
                modalIcon.innerHTML = messageType === 'success' ? "<i class='bx bx-check-circle'></i>" : "<i class='bx bx-x-circle'></i>";
                modalContent.classList.add(messageType);
                statusModal.classList.add('show');

                if (messageType === 'success') {
                    shouldClearForm = true; // Set flag to clear form on success
                }
            }

            // Function to handle modal close and potential form clearing
            window.handleModalClose = function() {
                statusModal.classList.remove('show');
                modalContent.classList.remove('success', 'error'); // Clean up classes
                if (shouldClearForm) {
                    // Clear the form fields after successful registration
                    document.getElementById('username').value = '';
                    document.getElementById('full_name').value = '';
                    document.getElementById('position').value = '';
                    document.getElementById('password').value = '';
                    document.getElementById('confirm_password').value = '';
                    // Reset password fields type to 'password' and icon to 'eye-outline'
                    document.getElementById('password').type = 'password';
                    document.getElementById('confirm_password').type = 'password';
                    document.querySelectorAll('.toggle-password').forEach(ic => ic.name = 'eye-outline');
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