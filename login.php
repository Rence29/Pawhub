<?php
session_start(); // Start the session

$error = "";


if($_SESSION && $_SESSION ['username']){
  header("Location: home.php");
  exit();
}else{
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli('localhost', 'root', '', 'dog_found');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];

        if (password_verify($password, $stored_password)) {
            $_SESSION['username'] = $username;
            header("Location: home.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  <link rel="stylesheet" href="../css/login.css">
</head>
<body>

<!-- NAVIGATION -->
<nav>
  <div class="nav-container">
    <div class="nav-left">
      <img src="../img/naic.png" alt="Logo" />
      <a href="../index.php"><b>DOG POUND</b></a>
    </div>
    <div class="nav-center">
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="Anti-Rabies.php">Anti-Rabies</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="login.php">Admin</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- LOGIN FORM -->
<div class="main-content">
  <div class="login-container">
    <h2>Admin Login</h2>
    <form action="login.php" method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <div class="input-container">
          <ion-icon name="person-outline"></ion-icon>
          <input type="text" name="username" id="username" required />
        </div>
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-container password-container">
          <ion-icon name="lock-closed-outline"></ion-icon>
          <input type="password" name="password" id="password" required />
          <ion-icon class="show-hide" name="eye-outline" onclick="togglePassword()"></ion-icon>
        </div>
      </div>

      <button type="submit" class="login-btn">Login</button>

      <?php if ($error) { ?>
        <div class="alert"><?php echo htmlspecialchars($error); ?></div>
      <?php } ?>
    </form>
  </div>
</div>

<script>
  function togglePassword() {
    const password = document.getElementById('password');
    const icon = document.querySelector('.show-hide');
    if (password.type === 'password') {
      password.type = 'text';
      icon.name = 'eye-off-outline';
    } else {
      password.type = 'password';
      icon.name = 'eye-outline';
    }
  }
</script>

</body>
</html>
