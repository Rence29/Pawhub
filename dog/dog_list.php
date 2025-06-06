<?php
session_start(); // Start the session

// Database connection
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search filters
$search = "";
$barangay_filter = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}
if (isset($_GET['barangay_filter'])) {
    $barangay_filter = trim($_GET['barangay_filter']);
}

// Fetch data from the dogs table with optional search and barangay filters
// Ensure owner_contact_number is part of the SELECT query if you modified it directly here
$sql = "SELECT * FROM dogs WHERE (owner_name LIKE ? OR dog_name LIKE ?) AND barangay LIKE ?";
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$barangay_param = $barangay_filter ? "%$barangay_filter%" : "%%";
$stmt->bind_param("sss", $search_param, $search_param, $barangay_param);
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
    <link rel="stylesheet" href="../css/dog_list.css">
    <style>
        /* Basic Modal CSS (you might want to put this in dog_list.css) */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            width: 80%;
            max-width: 600px;
            position: relative;
            animation-name: animatetop;
            animation-duration: 0.4s
        }

        /* Add Animation */
        @keyframes animatetop {
            from {top: -300px; opacity: 0}
            to {top: 0; opacity: 1}
        }

        .modal-content h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .modal-content p {
            margin-bottom: 10px;
            font-size: 16px;
            color: #555;
        }

        .modal-content p strong {
            color: #000;
        }

        .modal-content span {
            font-weight: normal;
        }

        .close-button { /* Changed from close-modal-btn for consistency with JS */
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 20px;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }


        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            transition: background-color 0.3s ease;
        }

        .btn-icon i {
            font-size: 18px;
        }

        .btn-info { background-color: #17a2b8; }
        .btn-info:hover { background-color: #138496; }
        .btn-primary { background-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-danger { background-color: #dc3545; }
        .btn-danger:hover { background-color: #bd2130; }

        .no-records {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        /* Styles for the ul in the modal */
        #dogDetailsList {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .details-list-item {
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }

        .details-list-item:last-child {
            border-bottom: none;
        }
    </style>
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
                    <a href="../dog/dog_list.php" class="active">
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
                    <h1>List of Registered Dogs</h1>
                </div>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2>Search and Filter</h2>
                    <p>Find dogs by name, owner, or barangay</p>
                </div>
                
                <form method="get" action="dog_list.php" class="search-form">
                    <div class="form-group">
                        <input type="search" class="form-control" placeholder="Search by owner or dog name" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group">
                        <select class="form-control" name="barangay_filter">
                            <option value="">All Barangays</option>
                            <?php
                            // Fetch distinct barangays for filter dropdown
                            $barangay_query = $conn->query("SELECT DISTINCT barangay FROM dogs ORDER BY barangay ASC");
                            while ($row_b = $barangay_query->fetch_assoc()) {
                                $selected = ($row_b['barangay'] == $barangay_filter) ? "selected" : "";
                                echo "<option value=\"" . htmlspecialchars($row_b['barangay']) . "\" $selected>" . htmlspecialchars($row_b['barangay']) . "</option>";
                            }
                            ?>
                        </select>
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
                            <th>Control#</th>
                            <th>Owner</th>
                            <th>Dog Name</th>
                            <th>Breed</th>
                            <th>Age</th>
                            <th>Sex</th>
                            <th>Barangay</th>
                            <th>Vaccinated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0) { ?>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['dog_control_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dog_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dog_breed']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dog_age']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dog_sex']); ?></td>
                                    <td><?php echo htmlspecialchars($row['barangay']); ?></td>
                                    <td><?php echo htmlspecialchars($row['vaccination_status']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="#" class="btn-icon btn-info view-dog" data-id="<?php echo $row['id']; ?>" title="View">
                                                <i class='bx bx-show'></i>
                                            </a>
                                            <a href="dog_edit.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-primary" title="Edit">
                                                <i class='bx bx-edit'></i>
                                            </a>
                                            <a href="dog_delete.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this record?');">
                                                <i class='bx bx-trash'></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="9" class="no-records">No records found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
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
        // Add active class to current menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.menu-item a');
            
            menuItems.forEach(item => {
                // Adjust for potential path variations (e.g., if home.php is default for '/')
                let itemHref = item.getAttribute('href').split('/').pop();
                if (itemHref === '' || itemHref === 'index.php') {
                    itemHref = 'home.php'; // Assuming home.php is the default/dashboard
                }

                if (itemHref === currentPage) {
                    item.classList.add('active');
                }
            });

            // Modal functionality
            const modal = document.getElementById("dogViewModal");
            const closeButton = document.getElementsByClassName("close-button")[0];
            const viewButtons = document.querySelectorAll(".view-dog");
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
                fetch('fetch_dog_details.php?id=' + id)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok ' + response.statusText);
                        }
                        return response.json();
                    })
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

                // Define the specific details you want to show
                const details = [
                    { label: 'Control #', value: dog.dog_control_number || 'N/A' },
                    { label: 'Owner Name', value: dog.owner_name || 'N/A' },
                    { label: 'Dog Name', value: dog.dog_name || 'N/A' },
                    { label: 'Owner Occupation', value: dog.owner_occupation || 'N/A' },
                    { label: 'Owner Birthday', value: dog.owner_birthday || 'N/A' },
                    // Removed { label: 'Owner Contact No.', value: dog.owner_contact_number || 'N/A' },
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
</body>
</html>