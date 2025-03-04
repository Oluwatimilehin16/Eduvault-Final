<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="educator.css"> <!-- Link to educator-specific CSS -->
</head>
<body id="bg">

<header id="header">
    <div class="logo">
        <a href="homepage.php"><img src="./assets/logo.png" alt="EduVault"></a>
    </div>

    <nav class="navbar">
        <ul>
        <li><a href="homepage.html">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Courses</a></li>
                <li><a href="#">Contact</a></li>
        </ul>
    </nav>

    <div class="icons">
        <i class="bi bi-person" id="user-btn"></i>
        <i class="bi bi-list" id="menu-btn"></i>
    </div>

    <div class="user-box">
        <p>Username: <span><?php echo isset($_SESSION['student_firstname']) ? $_SESSION['student_firstname'] : 'Guest'; ?></span></p>
        <p>Email: <span><?php echo isset($_SESSION['student_email']) ? $_SESSION['student_email'] : 'Not available'; ?></span></p>

        <form method="post" action="logout.php">
            <button type="submit" class="logout-btn">Log Out</button>
        </form>
    </div>
</header>

</body>
</html>