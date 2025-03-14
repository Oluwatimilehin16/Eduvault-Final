<?php
include 'connection.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if form is submitted
if(isset($_POST['submit'])) {
    $firstname = htmlspecialchars($_POST["firstname"]);
    $lastname  = htmlspecialchars($_POST["lastname"]);
    $email     = htmlspecialchars($_POST["email"]);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = "student";

    // Check if email already exists
    $select_user = mysqli_query($conn, "SELECT * FROM `user` WHERE email='$email'") or die(mysqli_error($conn));

    if(mysqli_num_rows($select_user) > 0) {
        $message[] = 'User already exists!';
    } else {
        if ($_POST['password'] !== $_POST['cpassword']) {
            $message[] = 'Passwords do not match!';
        } else {
            // Insert new user into the database
            $query = "INSERT INTO `user`(`firstname`, `lastname`, `email`, `password`, `user_type`) 
                      VALUES ('$firstname','$lastname', '$email', '$password', '$user_type')";
            if (mysqli_query($conn, $query)) {
                // Get the newly registered user's ID
                $user_id = mysqli_insert_id($conn);

                // Start session for the new user
                $_SESSION['student_id'] = $user_id;
                $_SESSION['firstname'] = $firstname;
                $_SESSION['email'] = $email;

                // Redirect to their personal library page
                header("Location: library.php?user_id=" . $user_id);
                exit();
            } else {
                $message[] = 'Registration failed, please try again!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration | Educare</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="UserRegistration.css">
</head>
<body>
<header>
    <div class="logo">
        <a href="homepage.php"><img src="./assets/logo.png" alt="EduVault"></a>
    </div>

    <!-- Hamburger Menu Icon -->
    <div class="hamburger" onclick="toggleMenu()">
        <img src="./assets/ham.png" alt="Menu">
    </div>

    <!-- Navigation Menu -->
    <nav id="mobileMenu">
        <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="course.php">Courses</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="login.php" class="login-btn">Login</a></li>
        </ul>
    </nav>
</header>

    <?php
    if (isset($message)) {
        foreach ($message as $message) { // Change variable name to avoid conflict
            echo '
            <div class="message">
                <span>' . $message . '</span>
            </div>';
        }
    }
    
?>

    <div class="register-container">
    <!--Registration Form -->
        <form action="UserRegistration.php" method="POST" class="login-form">
            <h2>Sign Up</h2>
            <div class="input-group">
                <input type="text" name="firstname" placeholder="First name"required>
            </div>

            <div class="input-group">
                <input type="text" name="lastname" placeholder="Last name"required>
            </div>

            <div class="input-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="input-group">
                <input type="password" name="password"  placeholder="Password" required>
            </div>

            <div class="input-group">
                <input type="password" name="cpassword"  placeholder=" Confirm Password" required>
            </div>

            <div class="checkbox">
                <input type="checkbox" name="terms" required> I agree to the <a href="#">Terms & Conditions</a>
            </div>

            <button type="submit" name="submit" class="register-btn">Register</button>
        </form>
    </div>

    <!--Footer -->
    <footer>
        <div class="footer-content">
            <p>&copy; 2025 EduVault. All rights reserved.</p>
        </div>
    </footer>
<script src="script.js"></script>
</body>
</html>
