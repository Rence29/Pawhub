<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the users table
$sql = "SELECT id, username, full_name, position, password FROM users";
$result = $conn->query($sql);
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
    <link rel="stylesheet" href="../css/user_list.css">
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

        <main class="main-content">
            <div class="top-nav">
                <div class="page-title">
                    <h1>User Management</h1>
                </div>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2>List of System Users</h2>
                    <p>Manage user accounts, roles, and permissions.</p>
                </div>

                <div class="add-user-section">
                    <a href="user_register.php" class="btn btn-success">
                        <i class='bx bx-plus'></i> Add User
                    </a>
                </div>

                <div class="table-container">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Position</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['position']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="user_edit.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-primary" title="Edit">
                                                    <i class='bx bx-edit'></i>
                                                </a>
                                                <a href="user_delete.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this user?');">
                                                    <i class='bx bx-trash'></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="no-records">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
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

<?php
$conn->close();
?>