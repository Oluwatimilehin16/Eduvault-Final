<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only educators can access this page
if (!isset($_SESSION['educator_id'])) {
    header('location:login.php');
    exit();
}

// Initialize message array
$message = array();

// Display session messages
if (isset($_SESSION['message'])) {
    echo "<script>alert('" . $_SESSION['message'] . "');</script>";
    unset($_SESSION['message']);
}

// Get logged-in educator ID
$educator_id = $_SESSION['educator_id'];

// Check if the user is an educator
$user_check = mysqli_query($conn, "SELECT user_type FROM user WHERE id = '$educator_id'") or die('Query failed');
$user_data = mysqli_fetch_assoc($user_check);

if ($user_data['user_type'] !== 'educator') {
    die("Access Denied! Only educators can upload books.");
}

// Function to convert bytes to human readable format
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Set higher upload limits programmatically (if allowed)
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', 600);
ini_set('max_input_time', 600);
ini_set('memory_limit', '512M');

// Check server upload limits
$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_limit = min($max_upload, $max_post, $memory_limit) * 1024 * 1024; // Convert to bytes

// If ini_set didn't work, set a reasonable default (100MB)
if ($upload_limit < (100 * 1024 * 1024)) {
    $upload_limit = 100 * 1024 * 1024; // 100MB fallback
}

// Initialize variables to preserve form data
$form_data = [
    'title' => '',
    'price' => '',
    'description' => '',
    'category' => '',
    'section' => ''
];

// Handle book upload
if (isset($_POST['add_book'])) {
    // Preserve form data for redisplay
    $form_data['title'] = $_POST['title'] ?? '';
    $form_data['price'] = $_POST['price'] ?? '';
    $form_data['description'] = $_POST['description'] ?? '';
    $form_data['category'] = $_POST['category'] ?? '';
    $form_data['section'] = $_POST['section'] ?? '';
    
    // Check if POST size exceeds limit
    if ($_SERVER['CONTENT_LENGTH'] > $upload_limit) {
        $message[] = "File is too large! Maximum allowed size is " . formatBytes($upload_limit) . 
                    ". Your file is " . formatBytes($_SERVER['CONTENT_LENGTH']) . ".";
    } else {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $price = mysqli_real_escape_string($conn, $_POST['price']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $section = mysqli_real_escape_string($conn, $_POST['section']);

        // Validate required fields
        if (empty($title) || empty($price) || empty($description) || empty($category) || empty($section)) {
            $message[] = "All fields are required!";
        } else {
            // File upload handling
            $file_name = $_FILES['file']['name'];
            $file_tmp = $_FILES['file']['tmp_name'];
            $file_size = $_FILES['file']['size'];
            $file_error = $_FILES['file']['error'];
            
            // Cover image handling
            $cover_image = $_FILES['cover_img']['name'];
            $cover_tmp_name = $_FILES['cover_img']['tmp_name'];
            $cover_size = $_FILES['cover_img']['size'];
            $cover_error = $_FILES['cover_img']['error'];

            // Check for upload errors
            if ($file_error !== UPLOAD_ERR_OK || $cover_error !== UPLOAD_ERR_OK) {
                switch ($file_error) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $message[] = "File is too large! Maximum allowed size is " . formatBytes($upload_limit);
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $message[] = "File was only partially uploaded. Please try again.";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $message[] = "No file was uploaded.";
                        break;
                    default:
                        $message[] = "An error occurred during file upload.";
                }
            } else {
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $cover_ext = strtolower(pathinfo($cover_image, PATHINFO_EXTENSION));
                
                $allowed_file_types = ['pdf', 'epub', 'mp4', 'avi', 'docx'];
                $allowed_image_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                // Generate unique file names
                $file_path = "book_" . uniqid() . "." . $file_ext;
                $cover_image_new_name = "cover_" . uniqid() . "." . $cover_ext;

                // Check if title already exists
                $select_title = mysqli_query($conn, "SELECT title FROM `products` WHERE title = '$title'") or die('Query failed');
                
                if (mysqli_num_rows($select_title) > 0) {
                    $message[] = 'Product name already exists';
                } elseif (!in_array($file_ext, $allowed_file_types)) {
                    $message[] = "Invalid file type! Allowed types: " . implode(', ', $allowed_file_types);
                } elseif (!in_array($cover_ext, $allowed_image_types)) {
                    $message[] = "Invalid image type! Allowed types: " . implode(', ', $allowed_image_types);
                } elseif ($file_size > $upload_limit) {
                    $message[] = "File is too large! Maximum size: " . formatBytes($upload_limit) . 
                                ". Your file: " . formatBytes($file_size);
                } elseif ($cover_size > (20 * 1024 * 1024)) { // 20MB limit for images
                    $message[] = "Cover image is too large! Maximum size: 20MB. Your image: " . formatBytes($cover_size);
                } else {
                    // Try to upload files
                    if (move_uploaded_file($file_tmp, $file_path) && move_uploaded_file($cover_tmp_name, $cover_image_new_name)) {
                        $insert_product = mysqli_query($conn, "INSERT INTO `products` (`educator_id`, `title`, `price`, `description`, `category`, `section`, `file_path`, `cover_img`)
                        VALUES ('$educator_id', '$title', '$price', '$description', '$category', '$section', '$file_path', '$cover_image_new_name')") or die('Query failed');

                        if ($insert_product) {
                            $message[] = "File uploaded successfully!";
                            // Clear form data on success
                            $form_data = [
                                'title' => '',
                                'price' => '',
                                'description' => '',
                                'category' => '',
                                'section' => ''
                            ];
                        } else {
                            $message[] = "Database error occurred!";
                        }
                    } else {
                        $message[] = "Failed to upload files! Please check file permissions.";
                    }
                }
            }
        }
    }
}

// Handle book deletion
if (isset($_GET['delete'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);

    // Verify that the book belongs to the logged-in educator
    $check_book = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$delete_id' AND educator_id = '$educator_id'") or die('Query failed');

    if (mysqli_num_rows($check_book) > 0) {
        $fetch_delete = mysqli_fetch_assoc($check_book);
        
        // Delete book files from root directory
        if (!empty($fetch_delete['cover_img']) && file_exists($fetch_delete['cover_img'])) {
            unlink($fetch_delete['cover_img']);
        }
        if (!empty($fetch_delete['file_path']) && file_exists($fetch_delete['file_path'])) {
            unlink($fetch_delete['file_path']);
        }

        // Delete book record
        mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('Query failed');
        $message[] = "Book deleted successfully!";
    } else {
        $message[] = "Unauthorized action!";
    }
}

// Fetch book details for editing
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $edit_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$edit_id' AND educator_id = '$educator_id'") or die('Query failed');

    if (mysqli_num_rows($edit_query) > 0) {
        $edit_data = mysqli_fetch_assoc($edit_query);
    } else {
        die("Unauthorized action!");
    }
}

// Handle book update
if (isset($_POST['update_book'])) {
    // Check POST size for updates too
    if ($_SERVER['CONTENT_LENGTH'] > $upload_limit) {
        $_SESSION['message'] = "Files are too large! Maximum allowed size is " . formatBytes($upload_limit);
        header("Location: educator_product.php");
        exit();
    }
    
    $update_id = mysqli_real_escape_string($conn, $_POST['book_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);

    // Get current book data
    $current_book = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$update_id' AND educator_id = '$educator_id'");
    $current_data = mysqli_fetch_assoc($current_book);

    if (!$current_data) {
        $_SESSION['message'] = "Unauthorized action!";
        header("Location: educator_product.php");
        exit();
    }

    $update_success = true;

    // Update cover image if provided
    if (!empty($_FILES['cover_img']['name']) && $_FILES['cover_img']['error'] === UPLOAD_ERR_OK) {
        $cover_img = $_FILES['cover_img']['name'];
        $cover_tmp = $_FILES['cover_img']['tmp_name'];
        $cover_size = $_FILES['cover_img']['size'];
        $cover_ext = strtolower(pathinfo($cover_img, PATHINFO_EXTENSION));
        $cover_image_new_name = "cover_" . uniqid() . "." . $cover_ext;

        $allowed_image_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($cover_ext, $allowed_image_types)) {
            $_SESSION['message'] = "Invalid image type!";
            $update_success = false;
        } elseif ($cover_size > (20 * 1024 * 1024)) {
            $_SESSION['message'] = "Cover image is too large! Maximum: 20MB";
            $update_success = false;
        } elseif (move_uploaded_file($cover_tmp, $cover_image_new_name)) {
            // Delete old cover image
            if (!empty($current_data['cover_img']) && file_exists($current_data['cover_img'])) {
                unlink($current_data['cover_img']);
            }
            mysqli_query($conn, "UPDATE `products` SET cover_img = '$cover_image_new_name' WHERE id = '$update_id'");
        } else {
            $_SESSION['message'] = "Failed to upload cover image!";
            $update_success = false;
        }
    }

    // Update file if provided
    if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK && $update_success) {
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_size = $_FILES['file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_path = "book_" . uniqid() . "." . $file_ext;

        $allowed_file_types = ['pdf', 'epub', 'mp4', 'avi', 'docx'];

        if (!in_array($file_ext, $allowed_file_types)) {
            $_SESSION['message'] = "Invalid file type!";
            $update_success = false;
        } elseif ($file_size > $upload_limit) {
            $_SESSION['message'] = "File is too large! Maximum: " . formatBytes($upload_limit);
            $update_success = false;
        } elseif (move_uploaded_file($file_tmp, $file_path)) {
            // Delete old file
            if (!empty($current_data['file_path']) && file_exists($current_data['file_path'])) {
                unlink($current_data['file_path']);
            }
            mysqli_query($conn, "UPDATE `products` SET file_path = '$file_path' WHERE id = '$update_id'");
        } else {
            $_SESSION['message'] = "Failed to upload file!";
            $update_success = false;
        }
    }

    // Update book details if everything is successful
    if ($update_success) {
        mysqli_query($conn, "UPDATE `products` SET title='$title', price='$price', description='$description', category='$category', section='$section' WHERE id='$update_id'") or die('Query failed');
        $_SESSION['message'] = "Book updated successfully!";
    }
    
    header("Location: educator_product.php");
    exit();
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
        .message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .file-size-info {
            background: #e2e3e5;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <?php include 'educator_header.php'; ?>
    
    <?php if (!empty($message)): ?>
        <?php foreach ($message as $msg): ?>
            <div class="message<?php echo (strpos($msg, 'successfully') !== false) ? ' success' : ''; ?>">
                <span><?php echo htmlspecialchars($msg); ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="file-size-info">
        <strong>Upload Limits:</strong> Maximum file size: <?php echo formatBytes($upload_limit); ?> | Maximum image size: 20MB
    </div>
    
    <section class="add-products form-container">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="input-field">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" maxlength="70" value="<?php echo htmlspecialchars($form_data['title']); ?>" required>
            </div>

            <div class="input-field">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" min="0" step="0.01" value="<?php echo htmlspecialchars($form_data['price']); ?>" required>
            </div>

            <div class="input-field">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($form_data['description']); ?></textarea>
            </div>

            <div class="input-field">
                <label for="category">Category of File:</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="ebook" <?php echo ($form_data['category'] == 'ebook') ? 'selected' : ''; ?>>E-Book</option>
                    <option value="video" <?php echo ($form_data['category'] == 'video') ? 'selected' : ''; ?>>Instructional Video</option>
                </select>
            </div>

            <div class="input-field">
                <label for="section">Section:</label>
                <select name="section" id="section" required>
                    <option value="" disabled <?php echo empty($form_data['section']) ? 'selected' : ''; ?>>Select a category</option>
                    <optgroup label="Academic">
                        <option value="science" <?php echo ($form_data['section'] == 'science') ? 'selected' : ''; ?>>Science</option>
                        <option value="mathematics" <?php echo ($form_data['section'] == 'mathematics') ? 'selected' : ''; ?>>Mathematics</option>
                        <option value="engineering" <?php echo ($form_data['section'] == 'engineering') ? 'selected' : ''; ?>>Engineering</option>
                        <option value="medicine" <?php echo ($form_data['section'] == 'medicine') ? 'selected' : ''; ?>>Medicine & Health</option>
                        <option value="law" <?php echo ($form_data['section'] == 'law') ? 'selected' : ''; ?>>Law</option>
                        <option value="arts" <?php echo ($form_data['section'] == 'arts') ? 'selected' : ''; ?>>Arts & Humanities</option>
                        <option value="social_science" <?php echo ($form_data['section'] == 'social_science') ? 'selected' : ''; ?>>Social Science</option>
                    </optgroup>
                    <optgroup label="Technology">
                        <option value="programming" <?php echo ($form_data['section'] == 'programming') ? 'selected' : ''; ?>>Programming</option>
                        <option value="ai_ml" <?php echo ($form_data['section'] == 'ai_ml') ? 'selected' : ''; ?>>Artificial Intelligence & Machine Learning</option>
                        <option value="cybersecurity" <?php echo ($form_data['section'] == 'cybersecurity') ? 'selected' : ''; ?>>Cybersecurity</option>
                        <option value="data_science" <?php echo ($form_data['section'] == 'data_science') ? 'selected' : ''; ?>>Data Science</option>
                        <option value="web_dev" <?php echo ($form_data['section'] == 'web_dev') ? 'selected' : ''; ?>>Web Development</option>
                    </optgroup>
                    <optgroup label="Fiction">
                        <option value="fantasy" <?php echo ($form_data['section'] == 'fantasy') ? 'selected' : ''; ?>>Fantasy</option>
                        <option value="sci_fi" <?php echo ($form_data['section'] == 'sci_fi') ? 'selected' : ''; ?>>Science Fiction</option>
                        <option value="romance" <?php echo ($form_data['section'] == 'romance') ? 'selected' : ''; ?>>Romance</option>
                        <option value="mystery" <?php echo ($form_data['section'] == 'mystery') ? 'selected' : ''; ?>>Mystery & Thriller</option>
                        <option value="horror" <?php echo ($form_data['section'] == 'horror') ? 'selected' : ''; ?>>Horror</option>
                        <option value="historical_fiction" <?php echo ($form_data['section'] == 'historical_fiction') ? 'selected' : ''; ?>>Historical Fiction</option>
                    </optgroup>
                    <optgroup label="Non-Fiction">
                        <option value="biography" <?php echo ($form_data['section'] == 'biography') ? 'selected' : ''; ?>>Biography & Memoir</option>
                        <option value="self_help" <?php echo ($form_data['section'] == 'self_help') ? 'selected' : ''; ?>>Self-Help</option>
                        <option value="business" <?php echo ($form_data['section'] == 'business') ? 'selected' : ''; ?>>Business & Finance</option>
                        <option value="psychology" <?php echo ($form_data['section'] == 'psychology') ? 'selected' : ''; ?>>Psychology</option>
                        <option value="philosophy" <?php echo ($form_data['section'] == 'philosophy') ? 'selected' : ''; ?>>Philosophy</option>
                        <option value="history" <?php echo ($form_data['section'] == 'history') ? 'selected' : ''; ?>>History</option>
                    </optgroup>
                    <optgroup label="Entertainment & Hobbies">
                        <option value="gaming" <?php echo ($form_data['section'] == 'gaming') ? 'selected' : ''; ?>>Gaming</option>
                        <option value="comics" <?php echo ($form_data['section'] == 'comics') ? 'selected' : ''; ?>>Comics & Graphic Novels</option>
                        <option value="music" <?php echo ($form_data['section'] == 'music') ? 'selected' : ''; ?>>Music</option>
                        <option value="film" <?php echo ($form_data['section'] == 'film') ? 'selected' : ''; ?>>Film</option>
                        <option value="sports" <?php echo ($form_data['section'] == 'sports') ? 'selected' : ''; ?>>Sports</option>
                        <option value="cooking" <?php echo ($form_data['section'] == 'cooking') ? 'selected' : ''; ?>>Cooking & Food</option>
                    </optgroup>
                    <optgroup label="Lifestyle & Personal Development">
                        <option value="travel" <?php echo ($form_data['section'] == 'travel') ? 'selected' : ''; ?>>Travel</option>
                        <option value="fashion" <?php echo ($form_data['section'] == 'fashion') ? 'selected' : ''; ?>>Fashion</option>
                        <option value="fitness" <?php echo ($form_data['section'] == 'fitness') ? 'selected' : ''; ?>>Fitness & Wellness</option>
                        <option value="parenting" <?php echo ($form_data['section'] == 'parenting') ? 'selected' : ''; ?>>Parenting & Family</option>
                        <option value="spirituality" <?php echo ($form_data['section'] == 'spirituality') ? 'selected' : ''; ?>>Spirituality</option>
                    </optgroup>
                </select>
            </div>

            <div class="input-field">
                <label for="img">Upload Cover Image (Max: 20MB):</label>
                <input type="file" id="img" name="cover_img" accept="image/*" required>
            </div>

            <div class="input-field">
                <label for="file">Upload File (Max: <?php echo formatBytes($upload_limit); ?>):</label>
                <input type="file" id="file" name="file" accept=".pdf,.epub,.mp4,.avi,.docx" required>
            </div>

            <input type="submit" name="add_book" value="Upload" class="btn">
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
                        <img src="<?php echo htmlspecialchars($fetch_products['cover_img']); ?>" alt="Book Cover" class="cover-image">
                        <p class="price">₦<?php echo number_format($fetch_products['price'], 2); ?></p>
                        <h3><?php echo htmlspecialchars($fetch_products['title']); ?></h3>
                        <p><?php echo htmlspecialchars($fetch_products['description']); ?></p>
                        
                        <a href="educator_product.php?edit=<?php echo $fetch_products['id']; ?>" class="edit">Edit</a>
                        <a href="educator_product.php?delete=<?php echo $fetch_products['id']; ?>" class="delete" onclick="return confirm('Delete this product?')">Delete</a>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="empty"><p>No books uploaded yet.</p></div>';
            }
            ?>
        </div>
    </section>

    <?php if (isset($_GET['edit']) && $edit_data): ?>
    <section class="edit-book-form">
        <h2>Edit Book</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="book_id" value="<?php echo $edit_data['id']; ?>">

            <div class="input-field">
                <label for="edit_title">Title:</label>
                <input type="text" id="edit_title" name="title" value="<?php echo htmlspecialchars($edit_data['title']); ?>" required>
            </div>

            <div class="input-field">
                <label for="edit_price">Price:</label>
                <input type="number" id="edit_price" name="price" value="<?php echo $edit_data['price']; ?>" min="0" step="0.01" required>
            </div>

            <div class="input-field">
                <label for="edit_description">Description:</label>
                <textarea id="edit_description" name="description" rows="4" required><?php echo htmlspecialchars($edit_data['description']); ?></textarea>
            </div>

            <div class="input-field">
                <label for="edit_category">Category:</label>
                <select id="edit_category" name="category" required>
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
                        <option value="cooking" <?php echo ($edit_data['section'] == 'cooking') ? 'selected' : ''; ?>>Cooking & Food</option>
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
                <img src="<?php echo htmlspecialchars($edit_data['cover_img']); ?>" alt="Book Cover" class="cover-preview" style="max-width: 100px;">
                <input type="file" name="cover_img" accept="image/*">
                <small>Leave empty to keep current cover</small>
            </div>

            <div class="input-field">
                <label>Current File: <a href="<?php echo htmlspecialchars($edit_data['file_path']); ?>" target="_blank">View File</a></label>
                <input type="file" name="file" accept=".pdf,.epub,.mp4,.avi,.docx">
                <small>Leave empty to keep current file</small>
            </div>

            <input type="submit" name="update_book" value="Update Book" class="btn">
            <a href="educator_product.php" class="cancel-btn">Cancel</a>
        </form>
    </section>
    <?php endif; ?>

    <script>
        function closeEditForm() {
            window.location.href = 'educator_product.php';
        }
        
        // File size validation on client side
        document.getElementById('file').addEventListener('change', function() {
            const maxSize = <?php echo $upload_limit; ?>;
            if (this.files[0] && this.files[0].size > maxSize) {
                alert('File is too large! Maximum size allowed: <?php echo formatBytes($upload_limit); ?>');
                this.value = '';
            }
        });
        
        document.getElementById('img').addEventListener('change', function() {
            const maxSize = 20 * 1024 * 1024; // 20MB
            if (this.files[0] && this.files[0].size > maxSize) {
                alert('Cover image is too large! Maximum size allowed: 20MB');
                this.value = '';
            }
        });
    </script>
</body>
</html>