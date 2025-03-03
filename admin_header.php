<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css' rel='stylesheet'>
    <link rel="stylesheet" href="admin.css">
</head>
<body id="bg">
<header id="header">
        <div  class="logo">
            <a href="homepage.html"><img src="./assets/logo.png" alt="EduVault"></a>
        </div>
        <div>

        <nav class="navbar">
            <ul>
                <li><a href="admin_pannel.php">Home</a></li>
                <li><a href="admin_product.php">Product</a></li>
                <li><a href="admin_orders.php">Orders</a></li>
                <li><a href="admin_user.php">Users</a></li>
                <li><a href="admin_message.php">Message</a></li>
            </ul>
        </nav>
        </div>
        <div class="icons">
            <i class= "bi bi-person" id="user-btn"></i>
            <i class= "bi bi-list" id="menu-btn"></i>
        </div>

        <div class="user-box">
        <p>Username: <span><?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Guest'; ?></span></p>
        <p>Email: <span><?php echo isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'Not available'; ?></span></p>

            <form method="post">
                <button type="submit" class="logout-btn">log out</button>
            </form>
        </div>
    </header>
    <div class="banner">
        <div class="detail">
            <h1>admin dashboard</h1>
            <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Neque amet, ea ullam voluptate facilis ipsum, ut in reprehenderit necessitatibus quae aliquid. Accusantium de</p>
        </div>
    </div>
    <div class="line"></div>
   
</body>
</html>