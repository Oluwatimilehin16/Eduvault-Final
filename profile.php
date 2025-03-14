<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
.user-box {
    position: absolute;
    top: 50px; /* Adjusted to appear above the icon */
    right: 10px;
    background: rgba(173, 216, 230, 0.7);
    box-shadow: var(--box-shadow);
    border-radius: .5rem;
    padding: 1rem;
    text-align: center;
    width: 18rem;
    transform: scale(1.0);
    transform-origin: top right;
    line-height: 2;
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    z-index: 1000; /* Ensures it stays above other elements */
}

.user-box.active {
    opacity: 1;
    visibility: visible;
}
.menu-icons {
    position: fixed;
    top: 20px; /* Distance from the top */
    right: 25px; /* Distance from the right */
    display: flex;
    align-items: center;
    z-index: 1000; /* Ensure it's above other elements */
}

.menu-icons i {
    font-size: 24px;
    cursor: pointer;
    color:#1E3A8A;
}

    </style>
</head>

<body>
     <div class="menu-icons">
        <i class="bi bi-person" id="user-btn"></i> <!-- Profile Icon -->
    </div>

    <div class="user-box">
        <p>Username: <span><?php echo isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'Guest'; ?></span></p>
        <p>Email: <span><?php echo isset($_SESSION['student_email']) ? $_SESSION['student_email'] : 'Not available'; ?></span></p>
    </div>
</body>
</html>