<?php
include 'connection.php';

if(isset($_POST['submit'])){

    $firstname = htmlspecialchars($_POST["firstname"]);
    $lastname  = htmlspecialchars($_POST["lastname"]);
    $email     = htmlspecialchars($_POST["email"]);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = "student";

    $select_user=mysqli_query($conn, "SELECT * FROM `user` WHERE email='$email'") or die();

        if(mysqli_num_rows($select_user)>0){
            $message[] = 'user already exist';
        }else{
            if ($_POST['password'] !== $_POST['cpassword']) {
                $message[] = 'Passwords do not match!';
            }else{
                mysqli_query($conn, "INSERT INTO `user`(`firstname`,`lastname`, `email`, `password`) 
                VALUES ('$firsname','$lastname', '$email', '$password')") or die ('query failed');
           $message[]= 'registered successfully';
           header("refresh:2;url=homepage.php");
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


    <!--Header with Logo & Navigation -->
    <header>
        <div class="logo">
            <a href="homepage.html"><img src="./assets/logo.png" alt="EduVault"></a>
        </div>
        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Courses</a></li>
                <li><a href="#">Contact</a></li>
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

</body>
</html>
