<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['student_id'])) {
    header('location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sidebar</title>
    <style>
        /* Sidebar Styling */
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.sidebar {
    position: fixed;
    width: 250px;
    height: 100vh;
    background: #1E3A8A;
    top: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.sidebar .logo {
    width: 100%;
    padding:0 0; /* Reduce space around logo */
    text-align: center;
    border-bottom: 2px solid white; /* Adds a border under the logo */
}

.sidebar img {
    width: 200px; /* Adjust size if needed */
    display: block;
    margin: 0 auto; /* Center the logo */
}

.sidebar .menu {
    width: 100%;
    list-style: none;
    text-align: center;
    padding: 0;
}

.sidebar .menu li {
    width: 100%;
    padding: 10px 0;
    border-bottom: 1px solid white; /* Adds a border under each menu item */
}

.sidebar .menu li a {
    color: white;
    text-decoration: none;
    font-size: 16px;
    display: block;
    padding: 10px 0;
}

.sidebar .menu li a:hover {
    background: #ffcc00;
    color: #1E3A8A;
    border-radius: 5px;
}

/* Adjust content to fit next to sidebar */
.content {
    margin-left: 250px;
    padding: 20px;
}
.logout-btn {
    bottom:0;
    position: absolute;
    width: 50%;
    text-align: center;
    padding: 10px 0;
    background: #ffcc00;
    color:#1E3A8A;
    text-decoration: none;
    border-radius: 5px;
    margin-bottom: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        width: 60px;
        overflow: hidden;
    }

    .sidebar .menu li a {
        font-size: 14px;
        text-align: center;
        padding: 10px;
    }

    .sidebar img {
        width: 50px;
    }

    .content {
        margin-left: 60px;
    }
}

     </style>
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <a href="library.php"><img src="./assets/logo.png" alt="EduVault"></a>
    </div>
    <ul class="menu">
        <li><a href="homepage.php">Home</a></li>
        <li><a href="library.php">Library</a></li>
        <li><a href="course.php">Courses</a></li>
        <li><a href="contact.php">Contact</a></li>
    </ul>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>
</body>
</html>

