<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Fetch user based on email
    $select_user = mysqli_query($conn, "SELECT * FROM `user` WHERE email='$email'") or die(mysqli_error($conn));

    if (mysqli_num_rows($select_user) > 0) {
        $row = mysqli_fetch_assoc($select_user);

        // Verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id']; 
            $_SESSION['user_name'] = $row['firstname'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_type'] = $row['user_type']; 

            // Redirect based on user type
            if ($row['user_type'] == 'admin') {
                header('location:admin_pannel.php');
                exit();
            } elseif ($row['user_type'] == 'educator') {
                $_SESSION['educator_id'] = $row['id'];  // Ensure this comes from the database
                $_SESSION['educator_name'] = $row['firstname'];
                $_SESSION['educator_email'] = $row['email'];
                header("Location: educator_product.php");
                exit();
                
            } else {
                $_SESSION['student_id'] = $row['id'];  // Ensure this comes from the database
                $_SESSION['student_name'] = $row['firstname'];
                $_SESSION['student_email'] = $row['email'];
                header('location:library.php');
                exit();
            }
        } else {
            $message[] = 'Incorrect password!';
        }
    } else {
        $message[] = 'Incorrect email or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="loginStyleSheet.css">
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php"><img src="./assets/logo.png" alt="EduVault"></a>
    </div>

    <!-- Hamburger Menu Icon -->
    <div class="hamburger" onclick="toggleMenu()">
        <img src="./assets/ham.png" alt="Menu">
    </div>

    <!-- Navigation Menu -->
    <nav id="mobileMenu">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="course.php">Courses</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>
</header>
    
    <?php
    if (isset($message)) {
        foreach ($message as $message) { 
            echo '<div class="message"><span>' . $message . '</span></div>';
        }
    }
    ?>
    
    <div class="login-container">
        <form class="login-form" method="post">
            <h2>Login to Your Account</h2>
            <div class="input-group">
                <input type="text" placeholder="Email or Username" name="email" required>
            </div>
            <div class="input-group">
                <input type="password" placeholder="Password" name="password" required>
            </div>
            <div class="options">
                <a href="#">Forgot Password?</a>
            </div>
            <button type="submit" name="submit" class="login-btn">Login</button>
            <p class="signup-text">Don't have an account? <a href="UserRegistration.php">Sign Up</a></p>
        </form>
    </div>

    <footer>
        <div class="footer-content">
            <p>&copy; 2025 EduVault. All rights reserved.</p>
        </div>
    </footer>
    <script src="script.js"></script>
</body>
</html>
