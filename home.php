<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch counts dynamically
$registeredDogs = $conn->query("SELECT COUNT(*) AS total FROM dogs")->fetch_assoc()['total'];
$adoptionDogs = $conn->query("SELECT COUNT(*) AS total FROM adoption_list")->fetch_assoc()['total'];
$adoptionRequests = $conn->query("SELECT COUNT(*) AS total FROM adoption_requests")->fetch_assoc()['total'];
$users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

// Fetch recently registered dogs with all details for the table
// This query correctly orders by registration_date in descending order
// to show the most recent dogs first, and limits to 5.
$recentDogsQuery = "SELECT id, dog_control_number, owner_name, dog_name, dog_breed, dog_age, dog_sex, barangay, vaccination_status, registration_date FROM dogs ORDER BY id DESC LIMIT 2";
$recentDogsResult = $conn->query($recentDogsQuery);
$recentDogsData = [];
if ($recentDogsResult->num_rows > 0) {
    while($row = $recentDogsResult->fetch_assoc()) {
        $recentDogsData[] = $row;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="../img/Pawhubicon.png">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/dog_list.css">
    
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="img/indexlogo.png" alt="PawHub Logo">
                <h2>PawHub</h2>
            </div>

            <ul class="menu">
                <li class="menu-item">
                    <a href="home.php" class="active">
                        <i class='bx bx-home-alt'></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="dog/dog_register.php">
                        <i class='bx bx-folder-plus'></i>
                        <span>Register Dog</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="dog/dog_list.php">
                        <i class='bx bx-list-ul'></i>
                        <span>Dog List</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="adoption/adoption_list.php">
                        <i class='bx bxs-carousel'></i>
                        <span>Adoption List</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="adoption/adoption_record.php">
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
                    <a href="user/user_list.php">
                        <i class='bx bx-cog'></i>
                        <span>User Settings</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="logout.php">
                        <i class='bx bx-log-out'></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-nav">
                <div class="page-title">
                    <h1>Dashboard</h1>
                </div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=6C63FF&color=fff" alt="User">
                    <div class="user-info">
                        <h4><?= htmlspecialchars($_SESSION['username']) ?></h4>
                        <p>Administrator</p>
                    </div>
                </div>
            </div>

            <div class="stats-container">
                <a href="dog/dog_list.php" class="stat-card primary animate__animated animate__fadeInUp">
                    <div class="stat-header">
                        <h3 class="stat-title">Registered Dogs</h3>
                        <div class="stat-icon primary">
                            <box-icon name='folder' ></box-icon>
                        </div>
                    </div>
                    <h2 class="stat-value"><?= $registeredDogs ?></h2>
                </a>

                <a href="adoption/adoption_list.php" class="stat-card success animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="stat-header">
                        <h3 class="stat-title">Dogs for Adoption</h3>
                        <div class="stat-icon success">
                           <box-icon name='donate-heart'></box-icon>
                        </div>
                    </div>
                    <h2 class="stat-value"><?= $adoptionDogs ?></h2>
                </a>

                <a href="adoption/adoption_record.php" class="stat-card warning animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="stat-header">
                        <h3 class="stat-title">Adoption Requests</h3>
                        <div class="stat-icon warning">
                            <box-icon name='list-ul'></box-icon>
                        </div>
                    </div>
                    <h2 class="stat-value"><?= $adoptionRequests ?></h2>
                </a>

                <a href="user/user_list.php" class="stat-card danger animate__animated animate__fadeInUp animate__delay-3s">
                    <div class="stat-header">
                        <h3 class="stat-title">System Users</h3>
                        <div class="stat-icon danger">
                            <box-icon name="user"></box-icon>
                        </div>
                    </div>
                    <h2 class="stat-value"><?= $users ?></h2>
                </a>
            </div>

            
            <div class="activity-section animate__animated animate__fadeIn">
                <div class="section-header">
                    <h2 class="section-title">Recently Registered Dogs</h2>
                    <a href="dog/dog_list.php" class="view-all">View All Dogs</a>
                </div>

                <div class="table-container">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Control#</th>
                                <th>Owner</th>
                                <th>Dog Name</th>
                                <th>Breed</th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Barangay</th>
                                <th>Vaccinated</th>
                                <th>Actions</th> </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentDogsData)): ?>
                                <tr>
                                    <td colspan="9" class="no-records">No dogs registered recently.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentDogsData as $dog): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($dog['dog_control_number']) ?></td>
                                        <td><?= htmlspecialchars($dog['owner_name']) ?></td>
                                        <td><?= htmlspecialchars($dog['dog_name']) ?></td>
                                        <td><?= htmlspecialchars($dog['dog_breed']) ?></td>
                                        <td><?= htmlspecialchars($dog['dog_age']) ?></td>
                                        <td><?= htmlspecialchars($dog['dog_sex']) ?></td>
                                        <td><?= htmlspecialchars($dog['barangay']) ?></td>
                                        <td><?= htmlspecialchars($dog['vaccination_status']) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="#" class="btn-icon btn-info view-dog" data-id="<?= $dog['id']; ?>" title="View">
                                                    <i class='bx bx-show'></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="dogViewModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <div class="modal-header">
                <h2 id="modalDogName">Dog Details</h2>
            </div>
            <div class="modal-body">
                <ul id="dogDetailsList">
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar active link functionality
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.menu-item a');

            menuItems.forEach(item => {
                if (item.getAttribute('href') === currentPage ||
                    (currentPage === 'home.php' && item.getAttribute('href') === 'home.php')) {
                    item.classList.add('active');
                }
            });

            // Stat cards animation on scroll
            const statCards = document.querySelectorAll('.stat-card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                    }
                });
            }, { threshold: 0.5 });

            statCards.forEach(card => {
                observer.observe(card);
            });

            // Modal functionality for viewing dog details
            const modal = document.getElementById("dogViewModal");
            const closeButton = document.getElementsByClassName("close-button")[0];
            const viewButtons = document.querySelectorAll(".activity-section .view-dog"); // Target only recent dogs table
            const modalDogName = document.getElementById("modalDogName");
            const dogDetailsList = document.getElementById("dogDetailsList");

            viewButtons.forEach(button => {
                button.addEventListener("click", function(event) {
                    event.preventDefault(); // Prevent default link behavior
                    const dogId = this.getAttribute("data-id");
                    fetchDogDetails(dogId);
                });
            });

            closeButton.addEventListener("click", function() {
                modal.style.display = "none";
            });

            window.addEventListener("click", function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            });

            function fetchDogDetails(id) {
                // Adjust the path to fetch_dog_details.php if it's in a different directory
                fetch('dog/fetch_dog_details.php?id=' + id)
                    .then(response => response.json())
                    .then(dogData => {
                        if (dogData && !dogData.error) {
                            populateModal(dogData);
                        } else {
                            console.error('Error or no data fetched:', dogData.error);
                            alert("Could not load dog details. Please try again.");
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching dog details:', error);
                        alert("Error fetching dog details. Please check your network connection.");
                    });
            }

            function populateModal(dog) {
                modalDogName.textContent = dog.dog_name ? `Details for ${dog.dog_name}` : 'Dog Details';
                dogDetailsList.innerHTML = ''; // Clear previous content

                const details = [
                    { label: 'Control #', value: dog.dog_control_number || 'N/A' },
                    { label: 'Owner Name', value: dog.owner_name || 'N/A' },
                    { label: 'Dog Name', value: dog.dog_name || 'N/A' },
                    { label: 'Owner Occupation', value: dog.owner_occupation || 'N/A' },
                    { label: 'Owner Birthday', value: dog.owner_birthday || 'N/A' },
                    { label: 'Dog Origin', value: dog.dog_origin || 'N/A' },
                    { label: 'Dog Breed', value: dog.dog_breed || 'N/A' },
                    { label: 'Dog Color', value: dog.dog_color || 'N/A' },
                    { label: 'Dog Age', value: dog.dog_age || 'N/A' },
                    { label: 'Dog Sex', value: dog.dog_sex || 'N/A' },
                    { label: 'Barangay', value: dog.barangay || 'N/A' },
                    { label: 'Vaccination Status', value: dog.vaccination_status || 'N/A' },
                    { label: 'Deceased', value: dog.deceased || 'N/A' },
                    { label: 'Registration Date', value: dog.registration_date || 'N/A' }
                ];

                details.forEach(item => {
                    const li = document.createElement('li');
                    li.classList.add('details-list-item');
                    li.innerHTML = `<strong>${item.label}:</strong> <span>${item.value}</span>`;
                    dogDetailsList.appendChild(li);
                });

                modal.style.display = "flex"; // Use flex to center the modal content
            }
        });
    </script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
</body>
</html>