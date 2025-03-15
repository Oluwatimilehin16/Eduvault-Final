<?php
require_once('config.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(isset($_POST["submit"])) {

    $firstname = htmlspecialchars($_POST["firstname"]);
    $lastname  = htmlspecialchars($_POST["lastname"]);
    $email     = htmlspecialchars($_POST["email"]);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = "educator"; // Automatically set to 'educator'

    // Check if the user already exists
    $select_user = mysqli_query($conn, "SELECT * FROM `user` WHERE email='$email'") or die(mysqli_error($conn));

    if(mysqli_num_rows($select_user) > 0){
        $message[] = 'User already exists!';
    } else {
        if ($_POST['password'] !== $_POST['cpassword']) {
            $message[] = 'Passwords do not match!';
        } else {
            // $sql = "INSERT INTO user (firstname, lastname, email, password, user_type) VALUES (?,?,?,?,?)";
            // $stmtinsert = $conn->prepare($sql);
            // $result = $stmtinsert->execute([$firstname, $lastname, $email, $password, $user_type]);

                $sql = "INSERT INTO user (firstname, lastname, email, password, user_type) VALUES (?,?,?,?,?)";
                $stmtinsert = $conn->prepare($sql);
                $stmtinsert->bind_param("sssss", $firstname, $lastname, $email, $password, $user_type);
                $result = $stmtinsert->execute();


            if ($result) {
                $_SESSION['educator_id'] = $conn->insert_id; // Get the new user's ID
                $_SESSION['educator_email'] = $email;
                $_SESSION['educator_name'] = $firstname . ' ' . $lastname;
                
                $message[] = 'Registration successful! Redirecting...';
                header("refresh:2;url=educator_product.php"); // Redirect after 2 seconds
            } else {
                $message[] = 'Something went wrong. Try again!';
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
    <title>Author Registration | Educare</title>
    <link rel="stylesheet" href="AuthorRegistration.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="homepage.html"><img src="./assets/logo.png" alt="EduVault"></a>
    </div>
    <nav>
        <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="course.php">Courses</a></li>
                <li><a href="contact.php">Contact</a></li>
            <li><a href="Login.php" class="login-btn">Login</a></li>
        </ul>
    </nav>
</header>

<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '<div class="message"><span>' . $msg . '</span></div>';
    }
}
?>

<div class="register-container">
    <form action="AuthorRegistration.php" method="POST" class="login-form">
        <h2>Sign Up</h2>

        <div class="input-group">
            <input type="text" id="firstname" name="firstname" placeholder="First Name" required>
        </div>

        <div class="input-group">
            <input type="text" id="lastname" name="lastname" placeholder="Last Name" required>
        </div>

        <div class="input-group">
            <input type="email" id="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-group">
            <input type="password" id="password" name="password" placeholder="Password" required>
        </div>

        <div class="input-group">
            <input type="password" id="cpassword" name="cpassword" placeholder="Confirm Password" required>
        </div>

        <div class="checkbox">
            <input type="checkbox" name="terms" required> I agree to the <a href="#">Terms & Conditions</a>
        </div>

        <button type="submit" class="register-btn" name="submit" id="register">Register</button>
    </form>
</div>

<footer>
    <div class="footer-content">
        <p>&copy; 2025 EduVault. All rights reserved.</p>
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>

</body>
</html>
