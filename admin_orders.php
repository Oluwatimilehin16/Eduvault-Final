<?php
//innitiating connection to database
    include 'connection.php';
    session_start();
    $admin_id= $_SESSION['admin_name'];

    if(!isset($admin_id)){
        header('location:login.php');
    }
    if(isset($_POST['logout'])){
        session_destroy();
        header('location:login.php');
    }


    if (isset($_GET['delete'])) {
        $delete_id = $_GET['delete'];

        mysqli_query($conn, "DELETE FROM `order` WHERE id = '$delete_id'") or die('query failed');
        $message[]='order removed successfully';
        header('location:admin_orders.php');
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Total User</title>
</head>
<body>
    <?php 
    include 'admin_header.php';
    ?>
   <?php
    if (isset($message)) {
        foreach ($message as $message) { // Change variable name to avoid conflict
            echo '
            <div class="message">
                <span>' . $message . '</span>
            </div>';
        }
    }
    
?>

  <section class="order-container">
    <h1 class="title">Total Order Placed</h1>
    <div class="box-message">

        <?php 
        $select_order = mysqli_query($conn, "SELECT * FROM `order`") or die('Query failed');
        if (mysqli_num_rows($select_order) > 0) {
            while ($fetch_order = mysqli_fetch_assoc($select_order)) {
                ?>
                <div class="box-m1">
                    <p>user name: <span><?php echo $fetch_order['name']; ?></span></p>
                    <p>user id: <span><?php echo $fetch_order['id']; ?></span></p>
                    <p>placed on: <span><?php echo $fetch_order['placed_on']; ?></span></p>
                    <p>number:<span><?php echo $fetch_order['number']; ?></span></p>
                    <p>email:<span><?php echo $fetch_order['email']; ?></span></p>
                    <p>total price:<span><?php echo $fetch_order['total_price']; ?></span></p>
                    <p>method:<span><?php echo $fetch_order['method']; ?></span></p>
                    <p>total product:<span><?php echo $fetch_order['total_products']; ?></span></p>

                    <form method="post">
                        <input type="hidden" name="order_id" value="<?php echo $fetch_order['id']; ?>">
                        <select name="update-payment">
                            <option disabled selected><?php echo $fetch_order['payment_status']; ?></option>
                            <option value="pending">Pending</option>
                            <option value="complete">Completed</option>
                        </select>
                
                    </form>
                    <a href="admin_orders.php?delete=<?php echo $fetch_order['id']; ?>" 
                       class="delete" onclick="return confirm('Delete this message?')">Delete</a>

                </div>
                <?php
            }
        } else {
            echo '<div class="empty"><p>No Order Placed yet!</p></div>';
        }
        ?>  
    </div>
</section>

   <script src="script.js"></script>
</body>
</html>


