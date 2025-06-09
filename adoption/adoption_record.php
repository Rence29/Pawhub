<?php
// Database connection for display (using mysqli as in your original file for this part)
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search filter
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Fetch all adoption requests directly from the denormalized adoption_requests table
$sql = "
    SELECT ar.*
    FROM adoption_requests ar
    WHERE (ar.adopter_name LIKE ? OR ar.dog_breed LIKE ?)
    ORDER BY ar.request_date DESC
";

$stmt = $conn->prepare($sql);
$search_param = "%" . $search . "%";
$stmt->bind_param("ss", $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
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
    <link rel="stylesheet" href="../css/adoption_record.css">
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
                <li class="menu-item active">
                    <a href="../adoption/adoption_record.php" class="active">
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
                    <h1>Adoption Requests</h1>
                </div>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2>Search Requests</h2>
                    <p>Search by adopter name or dog breed</p>
                </div>

                <form method="get" action="adoption_record.php" class="search-form">
                    <div class="form-group">
                        <input type="search" class="form-control" placeholder="Search by adopter name or dog breed" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <button type="submit" class="btn">
                        <i class='bx bx-search'></i> Search
                    </button>
                </form>
            </div>

            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Adopter</th>
                            <th>Contacts</th>
                            <th>Address</th>
                            <th>Adopter Info</th>
                            <th>Dog Image</th>
                            <th>Dog Info</th>
                            <th>Requested At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><b><?php echo htmlspecialchars($row['adopter_name']); ?></b></td>
                                <td>
                                    <b><?php echo htmlspecialchars($row['adopter_email']); ?></b><br>
                                    <b><?php echo htmlspecialchars($row['adopter_phone']);?></b>
                                </td>
                                <td><?php echo htmlspecialchars($row['adopter_address']); ?></td>
                                <td>
                                    Age: <?php echo htmlspecialchars($row['adopter_age']); ?><br>
                                    House type: <?php echo htmlspecialchars($row['house_space']); ?><br>
                                    Experience: <?php echo htmlspecialchars($row['pet_experience']); ?><br>
                                    Family: <?php echo htmlspecialchars($row['family_composition']); ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['dog_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['dog_image']); ?>" alt="Dog" class="dog-image-thumbnail">
                                    <?php else: ?>
                                        <span class="no-image">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    Breed: <?php echo htmlspecialchars($row['dog_breed']); ?><br>
                                    Age: <?php echo htmlspecialchars($row['dog_age']); ?><br>
                                    Size: <?php echo htmlspecialchars($row['dog_size']); ?><br>
                                    Behavior: <?php echo htmlspecialchars($row['dog_behavior']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="update_status.php?id=<?php echo $row['id']; ?>&status=Accepted" class="btn-icon btn-success" title="Accept">
                                            <i class='bx bx-check'></i>
                                        </a>
                                        <a href="update_status.php?id=<?php echo $row['id']; ?>&status=Declined" class="btn-icon btn-danger" title="Decline">
                                            <i class='bx bx-x'></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="no-records">No pending adoption requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                    // Remove 'active' from other items, if any
                    document.querySelector('.menu-item.active')?.classList.remove('active');
                    item.parentElement.classList.add('active'); // Add active class to the parent li
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>