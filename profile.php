<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
.user-box {
    position: absolute;
    top: 50px;
    right: 15px;
    background: rgba(173, 216, 230, 0.9); /* Light blue with opacity */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
    width: 18rem;
    z-index: 1000;
    display: none;
}

/* When active class is applied */
.user-box.active {
    display: block; 
}

/* Style for menu icons container */
.menu-icons {
    position: fixed;
    top: 20px;
    right: 25px;
    display: flex;
    align-items: center;
    z-index: 1000;
}

/* Style for the user button icon */
.menu-icons i {
    font-size: 24px;
    cursor: pointer;
    color: #1E3A8A;
    padding: 5px;
}

/* Hover effect for icon */
.menu-icons i:hover {
    color: #3B82F6;
}

    </style>
</head>

<body>
     <div class="menu-icons">
    <i class="bi bi-person-circle" id="user-btn"></i> <!-- Changed to person-circle -->
</div>

    <div class="user-box">
        <p>Username: <span><?php echo isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'Guest'; ?></span></p>
        <p>Email: <span><?php echo isset($_SESSION['student_email']) ? $_SESSION['student_email'] : 'Not available'; ?></span></p>
    </div>
</body>
</html>