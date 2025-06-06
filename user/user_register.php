<?php
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $position = trim($_POST['position']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username) || empty($full_name) || empty($position) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        $error = "Password must be at least 8 characters long and include both letters and numbers.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username already exists. Please choose another.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, full_name, position, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $full_name, $position, $hashed_password);

            if ($stmt->execute()) {
                $success = "User registered successfully.";
                // Optionally clear the form fields after successful registration
                $username = $full_name = $position = $password = $confirm_password = '';
            } else {
                $error = "Error: " . $stmt->error;
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
                <h1>Register New User</h1>
            </div>
        </div>

        <div class="form-container">
            <h2>Register New User</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

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

    <script>
        // Toggle both password fields and eye icons simultaneously
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', () => {
                const passwordInput = document.getElementById('password');
                const confirmInput = document.getElementById('confirm_password');
                const isPasswordShown = passwordInput.type === 'text';

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
                const linkHref = item.getAttribute('href');
                if (linkHref && linkHref.endsWith(currentPage)) {
                    item.classList.add('active');
                }
            });
        });
    </script>

</body>
</html>