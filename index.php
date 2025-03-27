<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'maravilla_act1_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function emailExists($conn, $email) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $fullname = trim(strip_tags($_POST['fullname']));
    $email = trim(strip_tags($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All input fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
    } elseif (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters!";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match! Try Again.";
    } elseif (emailExists($conn, $email)) {  
        $_SESSION['error'] = "Email already exists! ";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullname, $email, $hashedPassword);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful!";
        } else {
            $_SESSION['error'] = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

    <div class="container">
        
        <div class="left-panel">
            <h1>Welcome Back!</h1>
            <p>Sign Up Now!</p>
        </div>

        <div class="form-container">
            <form id="registrationForm" method="POST">
                <h1>Create Account</h1>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert success" id="alertMessage"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert error" id="alertMessage"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="infield">
                    <input type="text" id="fullname" name="fullname" placeholder="Full Name" required>
                </div>
                <div class="infield">
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>
                <div class="infield">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <div class="infield">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <button type="submit" name="register">Sign Up</button>
            </form>
        </div>
    </div>

    <script>
        setTimeout(() => {
            let alertMessage = document.getElementById("alertMessage");
            if (alertMessage) {
                alertMessage.style.transition = "opacity 0.5s ease";
                alertMessage.style.opacity = "0";
                setTimeout(() => alertMessage.style.display = "none", 500);
            }
        }, 5000);
    </script>

</body>
</html>
