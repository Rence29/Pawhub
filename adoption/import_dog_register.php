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

    // Retrieve the original adoption_list ID from a hidden input
    // This is crucial to know which dog to delete from adoption_list
    $adoption_list_id = $_POST['adoption_list_id'] ?? null;

    // Prepare and execute the INSERT query for the 'dogs' table using prepared statements
    $stmt_insert = $conn->prepare("INSERT INTO dogs (
                                dog_control_number, owner_name, dog_name, owner_occupation, owner_birthday, dog_origin,
                                dog_breed, dog_color, dog_age, dog_sex, barangay, vaccination_status, deceased, registration_date
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Define the type string for bind_param (e.g., 'ssssssssssssss' for all strings)
    // Adjust 's' to 'i' for integers, 'd' for doubles, etc., based on your database schema
    $stmt_insert->bind_param("ssssssssssssss",
                                $dog_control_number, $owner_name, $dog_name, $owner_occupation, $owner_birthday, $dog_origin,
                                $dog_breed, $dog_color, $dog_age, $dog_sex, $barangay, $vaccination_status, $deceased, $registration_date);


    if ($stmt_insert->execute()) {
        // If the dog is successfully registered in the 'dogs' table, delete it from 'adoption_list'
        if ($adoption_list_id) { // Only attempt deletion if an ID was passed
            $stmt_delete = $conn->prepare("DELETE FROM adoption_list WHERE id = ?");
            $stmt_delete->bind_param("i", $adoption_list_id); // 'i' for integer ID

            if ($stmt_delete->execute()) {
                // Successfully deleted from adoption_list
                header("Location: ../dog/dog_list.php"); // Redirect to dog_list.php
                exit();
            } else {
                echo "Error deleting from adoption_list: " . $stmt_delete->error;
            }
            $stmt_delete->close();
        } else {
            // If no adoption_list_id was passed (e.g., accessed directly), just redirect
            header("Location: ../dog/dog_list.php");
            exit();
        }
    } else {
        echo "Error registering dog: " . $stmt_insert->error;
    }
    $stmt_insert->close();
    $conn->close();
}

// Prefill logic
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$prefillData = [];
$adoption_id_for_form = null; // Initialize a variable to hold the adoption_list ID

if (isset($_GET['dog_id'])) {
    $dog_id = (int) $_GET['dog_id'];
    $adoption_id_for_form = $dog_id; // Store the ID for the hidden input

    $stmt = $conn->prepare("SELECT dog_breed, dog_age, dog_color FROM adoption_list WHERE id = ?");
    $stmt->bind_param("i", $dog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $prefillData = $result->fetch_assoc();
    }
    $stmt->close();
}

// Set defaults if values are missing
$prefillData['dog_breed'] = $prefillData['dog_breed'] ?? '';
$prefillData['dog_age'] = $prefillData['dog_age'] ?? '';
$prefillData['dog_color'] = $prefillData['dog_color'] ?? '';
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
                <li class="menu-item"><a href="../home.php"><i class='bx bx-home-alt'></i><span>Dashboard</span></a></li>
                <li class="menu-item"><a href="dog_register.php"><i class='bx bx-folder-plus'></i><span>Register Dog</span></a></li>
                <li class="menu-item"><a href="dog_list.php"><i class='bx bx-list-ul'></i><span>Dog List</span></a></li>
                <li class="menu-item"><a href="../adoption/adoption_list.php" class="active"><i class='bx bxs-carousel'></i><span>Adoption List</span></a></li>
                <li class="menu-item"><a href="../adoption/adoption_record.php"><i class='bx bx-history'></i><span>Adopt Requests</span></a></li>
                 <li class="menu-item active"> <a href="../adoption/adoption_history.php"><i class='bx bx-archive'></i> <span>Adoption History</span></a>
                <div class="menu-divider"></div>
                <li class="menu-item"><a href="../user/user_list.php"><i class='bx bx-cog'></i><span>User Settings</span></a></li>
                <li class="menu-item"><a href="../logout.php"><i class='bx bx-log-out'></i><span>Logout</span></a></li>
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

                <form action="import_dog_register.php" method="post">
                    <?php if ($adoption_id_for_form): ?>
                        <input type="hidden" name="adoption_list_id" value="<?php echo htmlspecialchars($adoption_id_for_form); ?>">
                    <?php endif; ?>

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
                                <input type="date" class="form-control" id="owner_birthday" name="owner_birthday" required>
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
                                <input type="text" class="form-control" id="dog_breed" name="dog_breed" placeholder="Breed" required value="<?php echo htmlspecialchars($prefillData['dog_breed']); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="dog_color">Dog Color</label>
                                <input type="text" class="form-control" id="dog_color" name="dog_color" placeholder="Color" required value="<?php echo htmlspecialchars($prefillData['dog_color']); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="dog_age">Dog Age</label>
                                <input type="text" class="form-control" id="dog_age" name="dog_age" placeholder="Age" required value="<?php echo htmlspecialchars($prefillData['dog_age']); ?>" readonly>
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
                                <input type="text" class="form-control" id="barangay" name="barangay" placeholder="Barangay" required>
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
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('registration_date').value = today;
        });
    </script>
</body>
</html>