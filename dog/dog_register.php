<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli('localhost', 'root', '', 'dog_found');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $dog_control_number = $_POST['dog_control_number'];
    $owner_name = $_POST['owner_name'];
    $dog_name = $_POST['dog_name'];
    $owner_occupation = $_POST['owner_occupation'];
    $owner_birthday = $_POST['owner_birthday'];
    $dog_origin = $_POST['dog_origin'];
    $dog_breed = $_POST['dog_breed'];
    $dog_color = $_POST['dog_color'];
    $dog_age = $_POST['dog_age'];
    $dog_sex = $_POST['dog_sex'];
    $barangay = $_POST['barangay'];
    $vaccination_status = $_POST['vaccination_status'];
    $deceased = $_POST['deceased'];
    $registration_date = $_POST['registration_date'];
    // Removed: $owner_contact_number = $_POST['owner_contact_number'];

    $sql = "INSERT INTO dogs (
                dog_control_number, owner_name, dog_name, owner_occupation, owner_birthday, dog_origin,
                dog_breed, dog_color, dog_age, dog_sex, barangay, vaccination_status, deceased, registration_date
            ) VALUES (
                '$dog_control_number', '$owner_name', '$dog_name', '$owner_occupation', '$owner_birthday', '$dog_origin',
                '$dog_breed', '$dog_color', '$dog_age', '$dog_sex', '$barangay', '$vaccination_status', '$deceased', '$registration_date'
            )";

    if ($conn->query($sql) === TRUE) {
        header("Location: dog_list.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
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
    <link rel="stylesheet" href="../css/dog_register.css">

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
                    <a href="dog_register.php" class="active">
                        <i class='bx bx-folder-plus'></i>
                        <span>Register Dog</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="dog_list.php">
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
                    <h1>Dog Registration</h1>
                </div>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2>Register New Dog</h2>
                    <p>Please fill in all required information about the dog and its owner</p>
                </div>

                <form action="dog_register.php" method="post">
                    <div class="form-grid">
                        <div>
                            <div class="form-group">
                                <label for="dog_control_number">Dog Control Number *</label>
                                <input type="text" class="form-control" id="dog_control_number" name="dog_control_number" placeholder="00" required>
                            </div>

                            <div class="form-group">
                                <label for="owner_name">Owner Name</label>
                                <input type="text" class="form-control" id="owner_name" name="owner_name" placeholder="Full name" required>
                            </div>

                            <div class="form-group">
                                <label for="dog_name">Dog Name</label>
                                <input type="text" class="form-control" id="dog_name" name="dog_name" placeholder="Dog name" required>
                            </div>

                            <div class="form-group">
                                <label for="owner_occupation">Owner Occupation</label>
                                <input type="text" class="form-control" id="owner_occupation" name="owner_occupation" placeholder="Occupation" required>
                            </div>

                            <div class="form-group">
                                <label for="owner_birthday">Owner Birthday</label>
                                <input type="date" class="form-control" id="owner_birthday" name="owner_birthday" placeholder="birthdate" required>
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="dog_origin">Dog Origin *</label>
                                <select class="form-control" id="dog_origin" name="dog_origin" required>
                                    <option value="" disabled selected>Select Origin</option>
                                    <option value="Local">Local</option>
                                    <option value="International">International</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="dog_breed">Dog Breed</label>
                                <input type="text" class="form-control" id="dog_breed" name="dog_breed" placeholder="Breed" required>
                            </div>

                            <div class="form-group">
                                <label for="dog_color">Dog Color</label>
                                <input type="text" class="form-control" id="dog_color" name="dog_color" placeholder="Color" required>
                            </div>

                            <div class="form-group">
                                <label for="dog_age">Dog Age</label>
                                <input type="text" class="form-control" id="dog_age" name="dog_age" placeholder="Age" required>
                            </div>

                            <div class="form-group">
                                <label for="dog_sex">Dog Sex *</label>
                                <select class="form-control" id="dog_sex" name="dog_sex" required>
                                    <option value="" disabled selected>Select Sex</option>
                                    <option value="Male Castrated">Male Castrated</option>
                                    <option value="Male Intact">Male Intact</option>
                                    <option value="Female Spayed">Female Spayed</option>
                                    <option value="Female Intact">Female Intact</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="barangay">Barangay</label>
                                <select class="form-control" id="barangay" name="barangay" required>
                                    <option value="" disabled selected>Select Barangay</option>
                                    <option value="Bagong Kalsada">Bagong Kalsada</option>
                                    <option value="Balsahan">Balsahan</option>
                                    <option value="Bancaan">Bancaan</option>
                                    <option value="Bucana Malaki">Bucana Malaki</option>
                                    <option value="Bucana Sasahan">Bucana Sasahan</option>
                                    <option value="Calubcob">Calubcob</option>
                                    <option value="Capt. C. Nazareno (Poblacion)">Capt. C. Nazareno (Poblacion)</option>
                                    <option value="Gombalza (Poblacion)">Gombalza (Poblacion)</option>
                                    <option value="Halang">Halang</option>
                                    <option value="Humbac">Humbac</option>
                                    <option value="Ibayo Estacion">Ibayo Estacion</option>
                                    <option value="Ibayo Silangan">Ibayo Silangan</option>
                                    <option value="Kanluran Rizal">Kanluran Rizal</option>
                                    <option value="Latoria">Latoria</option>
                                    <option value="Labac">Labac</option>
                                    <option value="Mabolo">Mabolo</option>
                                    <option value="Malainen Bago">Malainen Bago</option>
                                    <option value="Malainen Luma">Malainen Luma</option>
                                    <option value="Makina">Makina</option>
                                    <option value="Molino">Molino</option>
                                    <option value="Munting Mapino">Munting Mapino</option>
                                    <option value="Muzon">Muzon</option>
                                    <option value="Palangue 2 & 3">Palangue 2 & 3</option>
                                    <option value="Palangue Central">Palangue Central</option>
                                    <option value="Sabang">Sabang</option>
                                    <option value="San Roque">San Roque</option>
                                    <option value="Santulan">Santulan</option>
                                    <option value="Sapa">Sapa</option>
                                    <option value="Timalan Balsahan">Timalan Balsahan</option>
                                    <option value="Timalan Concepcion">Timalan Concepcion</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="vaccination_status">Vaccination Status *</label>
                                <select class="form-control" id="vaccination_status" name="vaccination_status" required>
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="Fully Vaccinated">Vaccinated</option>
                                    <option value="Not Vaccinated">Not Vaccinated</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="deceased">Deceased *</label>
                                <select class="form-control" id="deceased" name="deceased" required>
                                    <option value="" disabled selected>Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="registration_date">Registration Date *</label>
                                <input type="date" class="form-control" id="registration_date" name="registration_date" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-block">
                        <i class='bx bx-save'></i> Register Dog
                    </button>
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
                if (item.getAttribute('href') === currentPage) {
                    item.classList.add('active');
                }
            });

            // Set today's date as default for registration date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('registration_date').value = today;
        });
    </script>
</body>
</html>