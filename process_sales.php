<?php
include_once('./dbcon.php');

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $sale_date = isset($_POST['sale_date']) ? mysqli_real_escape_string($con, $_POST['sale_date']) : '';
    $total_amount = isset($_POST['total_amount']) ? mysqli_real_escape_string($con, $_POST['total_amount']) : '';
    
    // Begin a transaction
    mysqli_begin_transaction($con);
    
    try {
        // Insert into sales table
        $sales_query = "INSERT INTO sales (sale_date, total_amount) VALUES ('$sale_date', '$total_amount')";
        if (!mysqli_query($con, $sales_query)) {
            throw new Exception("Sales insertion failed: " . mysqli_error($con));
        }
        
        // Get the last inserted sales_id
        $sales_id = mysqli_insert_id($con);
        
        // Prepare sales items data
        if (isset($_POST['product_id']) && isset($_POST['quantity']) && isset($_POST['price'])) {
            $product_ids = $_POST['product_id'];
            $quantities = $_POST['quantity'];
            $prices = $_POST['price'];

            // Check if arrays are of the same length
            if (count($product_ids) === count($quantities) && count($product_ids) === count($prices)) {
                for ($i = 0; $i < count($product_ids); $i++) {
                    $product_id = mysqli_real_escape_string($con, $product_ids[$i]);
                    $quantity = mysqli_real_escape_string($con, $quantities[$i]);
                    $price = mysqli_real_escape_string($con, $prices[$i]);
                    $total_price = $quantity * $price;

                    $items_query = "INSERT INTO sales_items (sales_id, product_id, quantity, price, total_price) VALUES ('$sales_id', '$product_id', '$quantity', '$price', '$total_price')";
                    $inventory_query = "UPDATE inventory SET quantity_in_stock = quantity_in_stock - '$quantity' WHERE product_id = '$product_id'";
                    if (!mysqli_query($con, $items_query)) {
                        throw new Exception("Sales items insertion failed: " . mysqli_error($con));
                    }
                    if (!mysqli_query($con, $inventory_query)) {
                        throw new Exception("Inventory update failed: " . mysqli_error($con));
                    }
                }
            } else {
                throw new Exception("Mismatch in number of products, quantities, or prices.");
            }
        } else {
            throw new Exception("No sales items provided.");
        }

        // Commit transaction
        mysqli_commit($con);

        // Redirect to a success page or display a success message
        header('Location: sales.php'); // Change to your success page
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);

        // Log error or display an error message
        echo "Error: " . $e->getMessage();
    }
} else {
    // Redirect or handle the case where the form was not submitted properly
    header('Location: index.php'); // Change to your form page
    exit;
}

// Close the database connection
mysqli_close($con);
?>
