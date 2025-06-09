<?php
session_start(); // Start the session if it's not already started in your home.php or header file

// Database connection
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search filters
$search = "";
$breed_filter = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}
if (isset($_GET['breed_filter'])) {
    $breed_filter = trim($_GET['breed_filter']);
}

// Fetch data from the adoption_list table with optional search and breed filters
// Adjusted query to search dog_breed and dog_color by the main search input
$sql = "SELECT id, dog_image, dog_breed, dog_sex, dog_color, dog_age, dog_behavior, dog_size, health_condition
        FROM adoption_list
        WHERE (dog_breed LIKE ? OR dog_color LIKE ?)
        AND dog_breed LIKE ?"; // This line filters specifically by breed_filter
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$breed_param = $breed_filter ? "%$breed_filter%" : "%%";
$stmt->bind_param("sss", $search_param, $search_param, $breed_param);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query Failed: " . $conn->error);
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
    <link rel="stylesheet" href="../css/adoption_list.css">
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
                    <a href="../adoption/adoption_list.php" class="active">
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
                    <h1>Adoptable Dogs</h1>
                </div>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2>Search and Filter Adoptable Dogs</h2>
                    <p>Find dogs by breed, color, or other criteria</p>
                </div>

                <form method="get" action="adoption_list.php" class="search-form">
                    <div class="form-group">
                        <input type="search" class="form-control" placeholder="Search by breed or color" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="form-group">
                        <select class="form-control" name="breed_filter">
                            <option value="">All Breeds</option>
                            <?php
                            // Fetch distinct breeds from adoption_list for the filter dropdown
                            $breed_query = $conn->query("SELECT DISTINCT dog_breed FROM adoption_list ORDER BY dog_breed ASC");
                            while ($row_b = $breed_query->fetch_assoc()) {
                                $selected = ($row_b['dog_breed'] == $breed_filter) ? "selected" : "";
                                echo "<option value=\"" . htmlspecialchars($row_b['dog_breed']) . "\" $selected>" . htmlspecialchars($row_b['dog_breed']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="btn">
                        <i class='bx bx-search'></i> Search
                    </button>
                    <a href="add_dog.php" class="btn btn-success">
                        <i class='bx bx-plus'></i> Add Dog
                    </a>
                </form>
            </div>

            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Breed</th>
                            <th>Sex</th>
                            <th>Color</th>
                            <th>Age</th>
                            <th>Behavior</th>
                            <th>Size</th>
                            <th>Health Condition</th> <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['dog_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($row['dog_image']); ?>" alt="Dog Image" class="dog-image-thumbnail">
                                        <?php else: ?>
                                            <span class="no-image">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['dog_breed']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dog_sex']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dog_color']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dog_age']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dog_behavior']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dog_size']); ?></td>
                                    <td><?php echo htmlspecialchars($row['health_condition']); ?></td> <td>
                                        <div class="action-buttons">
                                        <a href="adoption_edit.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-primary" title="Edit">
                                            <i class='bx bx-edit'></i>
                                        </a>
                                        <a href="adoption_delete.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this dog?');">
                                            <i class='bx bx-trash'></i>
                                        </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="no-records">No dogs are available for adoption.</td>
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
                // Ensure the active class is set for the correct page
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