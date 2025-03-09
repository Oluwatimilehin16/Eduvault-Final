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

    <!-- Navigation Menu -->
    <nav class="navbar" id="navbar">
        <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="course.php">Courses</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>

     <!-- Icons Wrapper (Profile + Hamburger) -->
     <div class="menu-icons">
        <i class="bi bi-person" id="user-btn"></i> <!-- Profile Icon -->
        <img src="./assets/ham.png" alt="Menu" id="menu-btn"> <!-- Hamburger Icon -->
    </div>

    <div class="user-box">
        <p>Username: <span><?php echo isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'Guest'; ?></span></p>
        <p>Email: <span><?php echo isset($_SESSION['student_email']) ? $_SESSION['student_email'] : 'Not available'; ?></span></p>

        <form method="post" action="logout.php">
            <button type="submit" class="logout-btn">Log Out</button>
        </form>
    </div>
</header>


</body>
</html>