
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educator Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="educator.css"> <!-- Link to educator-specific CSS -->
</head>
<body id="bg">

<header id="header">
    <div class="logo">
        <a href="homepage.php"><img src="./assets/logo.png" alt="EduVault"></a>
    </div>

    <nav class="navbar" id="navbar">
        <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="educator_product.php">My Uploads</a></li>
            <li><a href="educator_sales.php">Sales</a></li>
            <li><a href="educator_dashboard.php">Dashboard</a></li>
        </ul>
    </nav>

    <div class="menu-icons">
        <i class="bi bi-person" id="user-btn"></i> <!-- Profile Icon -->
        <img src="./assets/ham.png" alt="Menu" id="menu-btn"> <!-- Hamburger Icon -->
    </div>

    <div class="user-box">
        <p>Username: <span><?php echo isset($_SESSION['educator_name']) ? $_SESSION['educator_name'] : 'Guest'; ?></span></p>
        <p>Email: <span><?php echo isset($_SESSION['educator_email']) ? $_SESSION['educator_email'] : 'Not available'; ?></span></p>

        <form method="post" action="login.php">
            <button type="submit" class="logout-btn">Log Out</button>
        </form>
    </div>
</header>

<div class="banner">
    <div class="detail">
        <h1>Educator Dashboard</h1>
        <p>Welcome to your educator portal. Upload your materials, manage your sales, and interact with students.</p>
    </div>
</div>



</body>
</html>
