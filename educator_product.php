<?php
include 'connection.php';
session_start();

// Ensure only educators can access this page
if (!isset($_SESSION['educator_id'])) {
    header('location:login.php');
    exit();
}

// Get logged-in educator ID
$educator_id = $_SESSION['educator_id'];

// Check if the user is an educator
$user_check = mysqli_query($conn, "SELECT user_type FROM user WHERE id = '$educator_id'") or die('Query failed');
$user_data = mysqli_fetch_assoc($user_check);

if ($user_data['user_type'] !== 'educator') {
    die("Access Denied! Only educators can upload books.");
}

// Handle book upload
if (isset($_POST['add_book'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    // File upload handling
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_size = $_FILES['file']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed_types = ['pdf', 'epub', 'mp4', 'avi', 'docx']; // Allowed formats
    $upload_folder = "uploads/"; // Directory to save files
    $file_path = $upload_folder . uniqid() . "." . $file_ext;

    // Handle cover image upload
    $cover_image = $_FILES['cover_img']['name'];
    $cover_tmp_name = $_FILES['cover_img']['tmp_name'];
    $cover_image_new_name = uniqid() . "_" . $cover_image;
    $cover_folder = "uploads/covers/" . $cover_image_new_name;

    $select_title = mysqli_query($conn, "SELECT title FROM `products` WHERE title= '$title'") or die('query failed');
    
    if (mysqli_num_rows($select_title) > 0) {
        $message[] = 'Product name already exists';
    } elseif (!in_array($file_ext, $allowed_types)) {
        $message[] = "Invalid file type!";
    } elseif ($file_size > 50000000) { // Limit: 50MB
        $message[] = "File is too large!";
    } else {
        if (move_uploaded_file($file_tmp, $file_path)) {
            $insert_product = mysqli_query($conn, "INSERT INTO `products` (`educator_id`, `title`, `price`, `description`, `category`, `file_path`, `cover_img`)
            VALUES ('$educator_id', '$title', '$price', '$description', '$category', '$file_path', '$cover_image_new_name')") or die('query failed');

            if ($insert_product) {
                move_uploaded_file($cover_tmp_name, $cover_folder);
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
        $fetch_delete = mysqli_fetch_assoc($check_book);
        
        // Delete book files
        if (!empty($fetch_delete['cover_img'])) {
            unlink("uploads/covers/" . $fetch_delete['cover_img']);
        }
        if (!empty($fetch_delete['file_path'])) {
            unlink($fetch_delete['file_path']);
        }

        // Delete book record
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

    // Update cover image
    if (!empty($_FILES['cover_img']['name'])) {
        $cover_img = $_FILES['cover_img']['name'];
        $cover_tmp = $_FILES['cover_img']['tmp_name'];
        $cover_image_new_name = uniqid() . "_" . $cover_img;
        $cover_folder = "uploads/covers/" . $cover_image_new_name;

        move_uploaded_file($cover_tmp, $cover_folder);

        // Delete old cover image
        unlink("uploads/covers/" . $edit_data['cover_img']);

        mysqli_query($conn, "UPDATE `products` SET cover_img = '$cover_image_new_name' WHERE id = '$update_id'");
    }

    // Update file
    if (!empty($_FILES['file']['name'])) {
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_path = "uploads/" . uniqid() . "_" . $file_name;

        move_uploaded_file($file_tmp, $file_path);

        // Delete old file
        unlink($edit_data['file_path']);

        mysqli_query($conn, "UPDATE `products` SET file_path = '$file_path' WHERE id = '$update_id'");
    }

    // Update book details
    mysqli_query($conn, "UPDATE `products` SET title='$title', price='$price', description='$description', category='$category' WHERE id='$update_id'") or die('Query failed');

    $message[] = "Book updated successfully!";
    header("Location: educator_product.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educator Dashboard - Upload Books</title>
</head>
<body>
    <?php include 'educator_header.php'; ?>
    <?php if (isset($message)) {
                foreach ($message as $msg) {
                    echo '<div class="message"><span>' . $msg . '</span></div>';
                }
            } ?>
    <section class="add-products form-container">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="input-field">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
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
                <label for="category">Category:</label>
                <select id="category" name="category">
                    <option value="ebook">E-Book</option>
                    <option value="video">Instructional Video</option>
                </select>
            </div>

            <div class="input-field">
                <label for="img">Upload Cover Image:</label>
                <input type="file" id="img" name="cover_img" accept="image/*" required>
            </div>

            <div class="input-field">
                <label for="file">Upload File (PDF, EPUB, MP4, etc.):</label>
                <input type="file" id="file" name="file" accept=".pdf, .epub, .mp4, .avi" required>
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
                        <img src="<?php echo 'uploads/covers/' . $fetch_products['cover_img']; ?>" alt="Book Cover" class="cover-image">
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
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo $edit_data['title']; ?>" required>
            </div>

            <div class="input-field">
                <label for="price">Price:</label>
                <input type="text" id="price" name="price" value="<?php echo $edit_data['price']; ?>" required>
            </div>

            <div class="input-field">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required><?php echo $edit_data['description']; ?></textarea>
            </div>

            <div class="input-field">
                <label for="category">Category:</label>
                <select id="category" name="category">
                    <option value="ebook" <?php echo ($edit_data['category'] == 'ebook') ? 'selected' : ''; ?>>E-Book</option>
                    <option value="video" <?php echo ($edit_data['category'] == 'video') ? 'selected' : ''; ?>>Instructional Video</option>
                </select>
            </div>

            <div class="input-field">
                <label>Current Cover:</label>
                <img src="<?php echo 'uploads/covers/' . $edit_data['cover_img']; ?>" alt="Book Cover" class="cover-preview">
                <input type="file" name="cover_img" accept="image/*">
            </div>

            <div class="input-field">
                <label>Current File: <a href="<?php echo $edit_data['file_path']; ?>" target="_blank">View File</a></label>
                <input type="file" name="file" accept=".pdf, .epub, .mp4, .avi">
            </div>

            <input type="submit" name="update_book" value="Update Book" class="btn">
            <a href="#" class="cancel-btn" onclick="closeEditForm()">Cancel</a>
        </form>
    </section>
    <?php endif; ?>
    <script src="script.js"></script>
</body>
</html>
