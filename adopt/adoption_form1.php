<?php
$conn = new mysqli('localhost', 'root', '', 'dog_found');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = ''; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $age = $conn->real_escape_string($_POST['age']);
    $contact_number = $conn->real_escape_string($_POST['contact_number']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $house_space = $conn->real_escape_string($_POST['house_space']);
    $lifestyle = $conn->real_escape_string($_POST['lifestyle']);
    $family_composition = $conn->real_escape_string($_POST['family_composition']);
    $pet_experience = $conn->real_escape_string($_POST['pet_experience']);
    $adoption_date = $conn->real_escape_string($_POST['adoption_date']);

    // --- Server-side validation for contact_number ---
    // Remove any non-numeric characters for validation
    $cleaned_contact_number = preg_replace('/[^0-9]/', '', $contact_number);

    if (!preg_match('/^09[0-9]{9}$/', $cleaned_contact_number)) {
        $error = "Contact number must be exactly 11 digits, start with '09', and contain only numbers.";
    }

    if (empty($error)) { // Only proceed if no validation errors
        $sql = "INSERT INTO adopters_info (name, age, contact_number, email, address, house_space, lifestyle, family_composition, pet_experience, adoption_date)
                VALUES ('$name', '$age', '$cleaned_contact_number', '$email', '$address', '$house_space', '$lifestyle', '$family_composition', '$pet_experience', '$adoption_date')";

        if ($conn->query($sql) === TRUE) {
            $last_id = $conn->insert_id;
            header("Location: matches.php?id=$last_id");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Pawhub</title>
    <link rel="icon" type="image/png" href="../img/Pawhubicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/adoption_form1.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="../index.php" class="nav-brand">
                <img src="../img/naic.png" alt="Dog Pound Logo" />
                <span class="nav-brand-text">DOG POUND NAIC</span>
            </a>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li><a href="../Anti-Rabies.php" class="nav-link">Anti-Rabies</a></li>
                <li><a href="../about.php" class="nav-link">About</a></li>
                <li><a href="../login.php" class="nav-link">Admin</a></li>
            </ul>
        </div>
    </nav>

    <section class="hero">
        <div class="form-container">
            <div class="form-header">
                <h2>Application Form for Adoption</h2>
                <p>Please fill in all required information</p>
            </div>

            <?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>

            <form action="" method="POST" class="form-grid">
                <div class="form-group">
                    <label class="required-field">Name</label>
                    <input type="text" name="name" placeholder="Full name" required>
                </div>

                <div class="form-group">
                    <label class="required-field">Age</label>
                    <input type="number" name="age" placeholder="Age" required>
                </div>

                <div class="form-group">
                    <label class="required-field">Contact Number</label>
                    <input
                        type="tel"
                        name="contact_number"
                        id="contact_number"
                        placeholder="e.g., 09123456789"
                        required
                        pattern="09[0-9]{9}"
                        maxlength="11"
                        oninput="this.value = this.value.replace(/[^0-9]/g, ''); validateContactNumber();"
                        onchange="validateContactNumber();"
                    >
                </div>

                <div class="form-group">
                    <label class="required-field">Email Address</label>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="form-group">
                    <label class="required-field">Address</label>
                    <input type="text" name="address" placeholder="#Unit/Street/Barangay/City" required>
                </div>

                <div class="form-group">
                    <label class="required-field">House Space</label>
                    <select name="house_space" required>
                        <option disabled selected>Select</option>
                        <option value="small">Small House</option>
                        <option value="medium">Medium House</option>
                        <option value="large">Large House</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="required-field">Lifestyle</label>
                    <select name="lifestyle" required>
                        <option disabled selected>Select</option>
                        <option value="active">Active</option>
                        <option value="sedentary">Sedentary</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="required-field">Family Composition</label>
                    <select name="family_composition" required>
                        <option disabled selected>Select</option>
                        <option value="with kids">With kids</option>
                        <option value="no Kids">No Kids</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="required-field">Pet Experience</label>
                    <select name="pet_experience" required>
                        <option disabled selected>Select</option>
                        <option value="With Experience">With Experience</option>
                        <option value="No Experience">No Experience</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="required-field">Application Date</label>
                    <input type="date" name="adoption_date" required>
                </div>

                <button type="submit" class="btn-submit">Submit</button>
            </form>
        </div>
    </section>

    <script>
        function validateContactNumber() {
            const input = document.getElementById('contact_number');
            const errorSpan = document.getElementById('contact_number_error');
            const value = input.value;

            // Clear previous error message
            errorSpan.textContent = '';
            input.setCustomValidity(''); // Clear any custom validity set by previous checks

            if (value.length === 0) {
                errorSpan.textContent = 'Contact number is required.';
                input.setCustomValidity('Contact number is required.');
                return false;
            }

            // Check if it contains only numbers (though oninput handles this, it's a safeguard)
            if (/[^0-9]/.test(value)) {
                errorSpan.textContent = 'Only numbers are allowed.';
                input.setCustomValidity('Only numbers are allowed.');
                return false;
            }

            // Check length
            if (value.length !== 11) {
                errorSpan.textContent = 'Contact number must be exactly 11 digits.';
                input.setCustomValidity('Contact number must be exactly 11 digits.');
                return false;
            }

            // Check prefix
            if (!value.startsWith('09')) {
                errorSpan.textContent = 'Contact number must start with "09".';
                input.setCustomValidity('Contact number must start with "09".');
                return false;
            }

            // If all checks pass, it's valid
            return true;
        }

        // Add event listeners for real-time validation feedback
        document.addEventListener('DOMContentLoaded', function() {
            const contactNumberInput = document.getElementById('contact_number');
            
            // Initial validation on load if there's pre-filled data (e.g., from form re-submission)
            validateContactNumber(); 

            // Add an event listener to the form's submit button to ensure final validation
            document.querySelector('.form-grid').addEventListener('submit', function(event) {
                if (!validateContactNumber()) {
                    event.preventDefault(); // Prevent form submission if validation fails
                    // Scroll to the error message or highlight the field
                    document.getElementById('contact_number').focus();
                }
            });
        });
    </script>
</body>
</html>
