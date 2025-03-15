<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $message = htmlspecialchars($_POST["message"]);

    $to = "support@eduvault.com"; // Your email
    $subject = "New Contact Message from EduVault";
    $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";

    if (mail($to, $subject, $body)) {
        echo "<script>alert('Message Sent Successfully!');</script>";
    } else {
        echo "<script>alert('Failed to send message. Try again!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | EduVault</title>
    <link rel="stylesheet" href="contact.css">
</head>
<body>
    
<header id="header">
    <div class="logo">
        <a href="index.php"><img src="./assets/logo.png" alt="EduVault"></a>
    </div>

    <nav class="navbar">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="course.php">Courses</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
    </nav>
</header>

    <section class="contact-section">
        <h2>Contact Us</h2>
        <p>Have any questions? Reach out to us, and we'll get back to you as soon as possible.</p>

        <div class="contact-container">
            <!-- Contact Form -->
            <div class="contact-form">
                <h3>Send Us a Message</h3>
                <form method="POST">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </div>

            <!-- Contact Details -->
            <div class="contact-details">
                <h3>Our Contact Info</h3>
                <p><strong>Email:   </strong> support@eduvault.com</p>
                <p><strong>Phone:   </strong> +234 803 447 4877</p>
                <p><strong>Location:</strong> Lagos, Nigeria</p>
            </div>
        </div>
        
        <!-- Google Maps (Optional) -->
        <div class="map">
            <h3>Find Us</h3>
            <iframe 
                src="https://www.google.com/maps/embed?pb=..." 
                width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy">
            </iframe>
        </div>
    </section>

</body>
</html>
