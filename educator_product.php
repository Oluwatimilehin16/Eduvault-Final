<?php
// Start output buffering to prevent header issues
ob_start();

include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for POST size limit exceeded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $displayMaxSize = ini_get('post_max_size');
    $message[] = "File upload failed! The file is too large. Maximum allowed size is $displayMaxSize.";
}

// Ensure only educators can access this page
if (!isset($_SESSION['educator_id'])) {
    header('location:login.php');
    exit();
}

if (isset($_SESSION['message'])) {
    echo "<script>alert('" . $_SESSION['message'] . "');</script>";
    unset($_SESSION['message']); // Remove message after displaying
}

// Get logged-in educator ID
$educator_id = $_SESSION['educator_id'];

// Check if the user is an educator
$user_check = mysqli_query($conn, "SELECT user_type FROM user WHERE id = '$educator_id'") or die('Query failed');
$user_data = mysqli_fetch_assoc($user_check);

if ($user_data['user_type'] !== 'educator') {
    die("Access Denied! Only educators can upload books.");
}

// Function to convert image to base64
function imageToBase64($file) {
    $imageData = file_get_contents($file['tmp_name']);
    $base64 = base64_encode($imageData);
    $mimeType = $file['type'];
    return 'data:' . $mimeType . ';base64,' . $base64;
}

// Function to convert file to base64
function fileToBase64($file) {
    $fileData = file_get_contents($file['tmp_name']);
    $base64 = base64_encode($fileData);
    $mimeType = $file['type'];
    return 'data:' . $mimeType . ';base64,' . $base64;
}

// Handle book upload
if (isset($_POST['add_book'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);

    // Check if files were uploaded properly
    if (!isset($_FILES['file']) || !isset($_FILES['cover_img'])) {
        $message[] = "File upload failed! Please try again with smaller files.";
    } else {
        // File upload handling
        $file_name = $_FILES['file']['name'];
        $file_size = $_FILES['file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_types = ['pdf', 'epub', 'mp4', 'avi', 'docx']; // Allowed formats

        // Handle cover image upload
        $cover_image_name = $_FILES['cover_img']['name'];
        $cover_image_size = $_FILES['cover_img']['size'];

        $select_title = mysqli_query($conn, "SELECT title FROM `products` WHERE title= '$title'") or die('query failed');
        
        if (mysqli_num_rows($select_title) > 0) {
            $message[] = 'Product name already exists';
        } elseif (!in_array($file_ext, $allowed_types)) {
            $message[] = "Invalid file type!";
        } elseif ($file_size > 8000000) { // Limit: 8MB (slightly less than server limit)
            $message[] = "File is too large! Maximum file size is 8MB.";
        } elseif ($cover_image_size > 2000000) { // Limit: 2MB for images
            $message[] = "Cover image is too large! Maximum image size is 2MB.";
        } else {
            // Convert files to base64
            $cover_base64 = imageToBase64($_FILES['cover_img']);
            $file_base64 = fileToBase64($_FILES['file']);
            
            $insert_product = mysqli_query($conn, "INSERT INTO `products` (`educator_id`, `title`, `price`, `description`, `category`, `section`, `file_data`, `cover_img_data`, `file_name`, `cover_img_name`)
            VALUES ('$educator_id', '$title', '$price', '$description', '$category', '$section','$file_base64', '$cover_base64', '$file_name', '$cover_image_name')") or die('query failed');

            if ($insert_product) {
                $message[] = "File uploaded successfully!";
            }
        }
    }
}

// Handle book deletion
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Verify that the book belongs to the logged-in educator
    $check_book = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$delete_id' AND educator_id = '$educator_id'") or die('query failed');

    if (mysqli_num_rows($check_book) > 0) {
        // Delete book record (no files to delete since they're in database)
        mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed');
        $message[] = "Book deleted successfully!";
    } else {
        $message[] = "Unauthorized action!";
    }
}

// Fetch book details for editing
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$edit_id' AND educator_id = '$educator_id'") or die('Query failed');

    if (mysqli_num_rows($edit_query) > 0) {
        $edit_data = mysqli_fetch_assoc($edit_query);
    } else {
        die("Unauthorized action!");
    }
}

// Handle book update
if (isset($_POST['update_book'])) {
    $update_id = $_POST['book_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);

    // Update cover image if new one is uploaded
    if (!empty($_FILES['cover_img']['name'])) {
        if ($_FILES['cover_img']['size'] > 2000000) {
            $message[] = "Cover image is too large! Maximum size is 2MB.";
        } else {
            $cover_base64 = imageToBase64($_FILES['cover_img']);
            $cover_name = $_FILES['cover_img']['name'];
            mysqli_query($conn, "UPDATE `products` SET cover_img_data = '$cover_base64', cover_img_name = '$cover_name' WHERE id = '$update_id'");
        }
    }

    // Update file if new one is uploaded
    if (!empty($_FILES['file']['name'])) {
        if ($_FILES['file']['size'] > 8000000) {
            $message[] = "File is too large! Maximum size is 8MB.";
        } else {
            $file_base64 = fileToBase64($_FILES['file']);
            $file_name = $_FILES['file']['name'];
            mysqli_query($conn, "UPDATE `products` SET file_data = '$file_base64', file_name = '$file_name' WHERE id = '$update_id'");
        }
    }

    // Update book details (only if no file size errors)
    if (!isset($message)) {
        mysqli_query($conn, "UPDATE `products` SET title='$title', price='$price', description='$description', category='$category', section='$section' WHERE id='$update_id'") or die('Query failed');
        $_SESSION['message'] = "Book updated successfully!";
        header("Location: educator_product.php");
        exit();
    }
}

// End output buffering and flush
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educator Dashboard - Upload Books</title>
    <style>
        .file-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .error-message {
            color: #d32f2f;
            font-weight: bold;
            margin: 10px 0;
        }
        .success-message {
            color: #388e3c;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <?php include 'educator_header.php'; ?>
    <?php if (isset($message)) {
                foreach ($message as $msg) {
                    $messageClass = (strpos($msg, 'success') !== false) ? 'success-message' : 'error-message';
                    echo '<div class="message ' . $messageClass . '"><span>' . $msg . '</span></div>';
                }
            } ?>
    <section class="add-products form-container">
        <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="input-field">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" maxlength="70" required>
            </div>

            <div class="input-field">
                <label for="price">Price:</label>
                <input type="text" id="price" name="price" required>
            </div>

            <div class="input-field">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>

            <div class="input-field">
                <label for="category">Category of File:</label>
                <select id="category" name="category">
                    <option value="ebook">E-Book</option>
                    <option value="video">Instructional Video</option>
                </select>
            </div>

            <div class="input-field">
                <label for="section">Section:</label>
                <select name="section" id="section" required>
                    <option value="" disabled selected>Select a category</option>
                    <optgroup label="Academic">
                        <option value="science">Science</option>
                        <option value="mathematics">Mathematics</option>
                        <option value="engineering">Engineering</option>
                        <option value="medicine">Medicine & Health</option>
                        <option value="law">Law</option>
                        <option value="arts">Arts & Humanities</option>
                        <option value="social_science">Social Science</option>
                    </optgroup>
                    <optgroup label="Technology">
                        <option value="programming">Programming</option>
                        <option value="ai_ml">Artificial Intelligence & Machine Learning</option>
                        <option value="cybersecurity">Cybersecurity</option>
                        <option value="data_science">Data Science</option>
                        <option value="web_dev">Web Development</option>
                    </optgroup>
                    <optgroup label="Fiction">
                        <option value="fantasy">Fantasy</option>
                        <option value="sci_fi">Science Fiction</option>
                        <option value="romance">Romance</option>
                        <option value="mystery">Mystery & Thriller</option>
                        <option value="horror">Horror</option>
                        <option value="historical_fiction">Historical Fiction</option>
                    </optgroup>
                    <optgroup label="Non-Fiction">
                        <option value="biography">Biography & Memoir</option>
                        <option value="self_help">Self-Help</option>
                        <option value="business">Business & Finance</option>
                        <option value="psychology">Psychology</option>
                        <option value="philosophy">Philosophy</option>
                        <option value="history">History</option>
                    </optgroup>
                    <optgroup label="Entertainment & Hobbies">
                        <option value="gaming">Gaming</option>
                        <option value="comics">Comics & Graphic Novels</option>
                        <option value="music">Music</option>
                        <option value="film">Film & TV</option>
                        <option value="sports">Sports</option>
                        <option value="cooking">Cooking & Food</option>
                    </optgroup>
                    <optgroup label="Lifestyle & Personal Development">
                        <option value="travel">Travel</option>
                        <option value="fashion">Fashion</option>
                        <option value="fitness">Fitness & Wellness</option>
                        <option value="parenting">Parenting & Family</option>
                        <option value="spirituality">Spirituality</option>
                    </optgroup>
                </select>
            </div>

            <div class="input-field">
                <label for="img">Upload Cover Image:</label>
                <input type="file" id="img" name="cover_img" accept="image/*" required onchange="checkImageSize(this)">
                <div class="file-info">Maximum size: 2MB. Supported formats: JPG, PNG, GIF</div>
                <div id="imageError" class="error-message" style="display: none;"></div>
            </div>

            <div class="input-field">
                <label for="file">Upload File (PDF, EPUB, MP4, etc.):</label>
                <input type="file" id="file" name="file" accept=".pdf, .epub, .mp4, .avi, .docx" required onchange="checkFileSize(this)">
                <div class="file-info">Maximum size: 8MB. Supported formats: PDF, EPUB, MP4, AVI, DOCX</div>
                <div id="fileError" class="error-message" style="display: none;"></div>
            </div>

            <input type="submit" name="add_book" value="Upload" class="btn" id="submitBtn">
        </form>
    </section>

    <h2 id="cont-text">Your Uploaded Books</h2>
    <section class="show-products">
        <div class="box-container1">
            <?php 
            $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE educator_id = '$educator_id'") or die('Query failed');

            if (mysqli_num_rows($select_products) > 0) {
                while ($fetch_products = mysqli_fetch_assoc($select_products)) {
                    ?>
                    <div class="box1">
                        <img src="<?php echo $fetch_products['cover_img_data']; ?>" alt="Book Cover" class="cover-image" style="max-width: 200px; height: auto;">
                        <p class="price">₦<?php echo number_format($fetch_products['price'], 2); ?></p>
                        <h3><?php echo $fetch_products['title']; ?></h3>
                        <p><?php echo $fetch_products['description']; ?></p>
                        
                        <?php if ($fetch_products['educator_id'] == $educator_id): ?>
                            <a href="educator_product.php?edit=<?php echo $fetch_products['id']; ?>" class="edit">Edit</a>
                            <a href="educator_product.php?delete=<?php echo $fetch_products['id']; ?>" class="delete" onclick="return confirm('Delete this product?')">Delete</a>
                        <?php endif; ?>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="empty"><p>No books uploaded yet.</p></div>';
            }
            ?>
        </div>
    </section>

    <?php if (isset($_GET['edit']) && isset($edit_data)): ?>
    <section class="edit-book-form">
        <h2>Edit Book</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="book_id" value="<?php echo $edit_data['id']; ?>">

            <div class="input-field">
                <label for="edit_title">Title:</label>
                <input type="text" id="edit_title" name="title" value="<?php echo $edit_data['title']; ?>" required>
            </div>

            <div class="input-field">
                <label for="edit_price">Price:</label>
                <input type="text" id="edit_price" name="price" value="<?php echo $edit_data['price']; ?>" required>
            </div>

            <div class="input-field">
                <label for="edit_description">Description:</label>
                <textarea id="edit_description" name="description" rows="4" required><?php echo $edit_data['description']; ?></textarea>
            </div>

            <div class="input-field">
                <label for="edit_category">Category:</label>
                <select id="edit_category" name="category">
                    <option value="ebook" <?php echo ($edit_data['category'] == 'ebook') ? 'selected' : ''; ?>>E-Book</option>
                    <option value="video" <?php echo ($edit_data['category'] == 'video') ? 'selected' : ''; ?>>Instructional Video</option>
                </select>
            </div>

            <div class="input-field">
                <label for="edit_section">Section:</label>
                <select name="section" id="edit_section" required>
                    <option value="" disabled>Select a section</option>
                    <optgroup label="Academic">
                        <option value="science" <?php echo ($edit_data['section'] == 'science') ? 'selected' : ''; ?>>Science</option>
                        <option value="mathematics" <?php echo ($edit_data['section'] == 'mathematics') ? 'selected' : ''; ?>>Mathematics</option>
                        <option value="engineering" <?php echo ($edit_data['section'] == 'engineering') ? 'selected' : ''; ?>>Engineering</option>
                        <option value="medicine" <?php echo ($edit_data['section'] == 'medicine') ? 'selected' : ''; ?>>Medicine & Health</option>
                        <option value="law" <?php echo ($edit_data['section'] == 'law') ? 'selected' : ''; ?>>Law</option>
                        <option value="arts" <?php echo ($edit_data['section'] == 'arts') ? 'selected' : ''; ?>>Arts & Humanities</option>
                        <option value="social_science" <?php echo ($edit_data['section'] == 'social_science') ? 'selected' : ''; ?>>Social Science</option>
                    </optgroup>
                    <optgroup label="Technology">
                        <option value="programming" <?php echo ($edit_data['section'] == 'programming') ? 'selected' : ''; ?>>Programming</option>
                        <option value="ai_ml" <?php echo ($edit_data['section'] == 'ai_ml') ? 'selected' : ''; ?>>Artificial Intelligence & Machine Learning</option>
                        <option value="cybersecurity" <?php echo ($edit_data['section'] == 'cybersecurity') ? 'selected' : ''; ?>>Cybersecurity</option>
                        <option value="data_science" <?php echo ($edit_data['section'] == 'data_science') ? 'selected' : ''; ?>>Data Science</option>
                        <option value="web_dev" <?php echo ($edit_data['section'] == 'web_dev') ? 'selected' : ''; ?>>Web Development</option>
                    </optgroup>
                    <optgroup label="Fiction">
                        <option value="fantasy" <?php echo ($edit_data['section'] == 'fantasy') ? 'selected' : ''; ?>>Fantasy</option>
                        <option value="sci_fi" <?php echo ($edit_data['section'] == 'sci_fi') ? 'selected' : ''; ?>>Science Fiction</option>
                        <option value="romance" <?php echo ($edit_data['section'] == 'romance') ? 'selected' : ''; ?>>Romance</option>
                        <option value="mystery" <?php echo ($edit_data['section'] == 'mystery') ? 'selected' : ''; ?>>Mystery & Thriller</option>
                        <option value="horror" <?php echo ($edit_data['section'] == 'horror') ? 'selected' : ''; ?>>Horror</option>
                        <option value="historical_fiction" <?php echo ($edit_data['section'] == 'historical_fiction') ? 'selected' : ''; ?>>Historical Fiction</option>
                    </optgroup>
                    <optgroup label="Non-Fiction">
                        <option value="biography" <?php echo ($edit_data['section'] == 'biography') ? 'selected' : ''; ?>>Biography & Memoir</option>
                        <option value="self_help" <?php echo ($edit_data['section'] == 'self_help') ? 'selected' : ''; ?>>Self-Help</option>
                        <option value="business" <?php echo ($edit_data['section'] == 'business') ? 'selected' : ''; ?>>Business & Finance</option>
                        <option value="psychology" <?php echo ($edit_data['section'] == 'psychology') ? 'selected' : ''; ?>>Psychology</option>
                        <option value="philosophy" <?php echo ($edit_data['section'] == 'philosophy') ? 'selected' : ''; ?>>Philosophy</option>
                        <option value="history" <?php echo ($edit_data['section'] == 'history') ? 'selected' : ''; ?>>History</option>
                    </optgroup>
                    <optgroup label="Entertainment & Hobbies">
                        <option value="gaming" <?php echo ($edit_data['section'] == 'gaming') ? 'selected' : ''; ?>>Gaming</option>
                        <option value="comics" <?php echo ($edit_data['section'] == 'comics') ? 'selected' : ''; ?>>Comics & Graphic Novels</option>
                        <option value="music" <?php echo ($edit_data['section'] == 'music') ? 'selected' : ''; ?>>Music</option>
                        <option value="film" <?php echo ($edit_data['section'] == 'film') ? 'selected' : ''; ?>>Film & TV</option>
                        <option value="sports" <?php echo ($edit_data['section'] == 'sports') ? 'selected' : ''; ?>>Sports</option>
                        <option value="cooking" <?php echo ($edit_data['section'] == 'cooking') ? 'selected' : ''; ?>>Cooking</option>
                    </optgroup>
                    <optgroup label="Lifestyle & Personal Development">
                        <option value="travel" <?php echo ($edit_data['section'] == 'travel') ? 'selected' : ''; ?>>Travel</option>
                        <option value="fashion" <?php echo ($edit_data['section'] == 'fashion') ? 'selected' : ''; ?>>Fashion</option>
                        <option value="fitness" <?php echo ($edit_data['section'] == 'fitness') ? 'selected' : ''; ?>>Fitness & Wellness</option>
                        <option value="parenting" <?php echo ($edit_data['section'] == 'parenting') ? 'selected' : ''; ?>>Parenting & Family</option>
                        <option value="spirituality" <?php echo ($edit_data['section'] == 'spirituality') ? 'selected' : ''; ?>>Spirituality</option>
                    </optgroup>
                </select>
            </div>

            <div class="input-field">
                <label>Current Cover:</label>
                <?php if (!empty($edit_data['cover_img_data'])): ?>
                    <img src="<?php echo $edit_data['cover_img_data']; ?>" alt="Book Cover" class="cover-preview" style="max-width: 150px; height: auto; display: block; margin: 10px 0;">
                <?php endif; ?>
                <input type="file" name="cover_img" accept="image/*" onchange="checkImageSize(this)">
                <div class="file-info">Leave empty to keep current cover. Maximum size: 2MB.</div>
            </div>

            <div class="input-field">
                <label>Current File: <?php echo $edit_data['file_name']; ?></label>
                <input type="file" name="file" accept=".pdf, .epub, .mp4, .avi, .docx" onchange="checkFileSize(this)">
                <div class="file-info">Leave empty to keep current file. Maximum size: 8MB.</div>
            </div>

            <input type="submit" name="update_book" value="Update Book" class="btn">
            <a href="educator_product.php" class="cancel-btn">Cancel</a>
        </form>
    </section>
    <?php endif; ?>

    <script>
        function checkFileSize(input) {
            const file = input.files[0];
            const maxSize = 8 * 1024 * 1024; // 8MB in bytes
            const errorDiv = document.getElementById('fileError');
            const submitBtn = document.getElementById('submitBtn');
            
            if (file && file.size > maxSize) {
                errorDiv.textContent = `File is too large! Size: ${(file.size / 1024 / 1024).toFixed(2)}MB. Maximum allowed: 8MB.`;
                errorDiv.style.display = 'block';
                input.value = ''; // Clear the input
                submitBtn.disabled = true;
            } else {
                errorDiv.style.display = 'none';
                submitBtn.disabled = false;
            }
        }
        
        function checkImageSize(input) {
            const file = input.files[0];
            const maxSize = 2 * 1024 * 1024; // 2MB in bytes
            const errorDiv = document.getElementById('imageError');
            const submitBtn = document.getElementById('submitBtn');
            
            if (file && file.size > maxSize) {
                errorDiv.textContent = `Image is too large! Size: ${(file.size / 1024 / 1024).toFixed(2)}MB. Maximum allowed: 2MB.`;
                errorDiv.style.display = 'block';
                input.value = ''; // Clear the input
                submitBtn.disabled = true;
            } else {
                errorDiv.style.display = 'none';
                submitBtn.disabled = false;
            }
        }
        
        function closeEditForm() {
            window.location.href = 'educator_product.php';
        }
        
        // Form submission validation
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('file');
            const imageInput = document.getElementById('img');
            
            if (fileInput.files[0] && fileInput.files[0].size > 8 * 1024 * 1024) {
                e.preventDefault();
                alert('Please select a file smaller than 8MB.');
                return false;
            }
            
            if (imageInput.files[0] && imageInput.files[0].size > 2 * 1024 * 1024) {
                e.preventDefault();
                alert('Please select an image smaller than 2MB.');
                return false;
            }
        });
    </script>
</body>
</html>