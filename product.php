<?php
require('dbcon.php');


/* --------------------------------------------------------------------------------code.php file start------------------------------------------------------------------------------------- */

// Insert data start
if (isset($_POST['save_supp_data'])) {
    $productname = $_POST['productname'];
    $productdes = $_POST['productdes'];
    $productcategory = $_POST['categoryid'];
    $sellprice = $_POST['productprice'];
    

    $insert_query = "INSERT INTO product(product_name,product_des,categories_id,sellPrice) VALUES ('" . mysqli_real_escape_string($con, $productname) . "', '" . mysqli_real_escape_string($con, $productdes) . "', '" . mysqli_real_escape_string($con, $productcategory) . "', '" . mysqli_real_escape_string($con, $sellprice) . "')";
    $set_initial_inventory_query = "INSERT INTO inventory(product_id,product_name, quantity_in_stock) VALUES (LAST_INSERT_ID(), ? , 0)";
    $stmt_for_inventory = $con->prepare($set_initial_inventory_query);
    $stmt_for_inventory->bind_param('i',mysqli_real_escape_string($con, $productname));


    $insert_query_run = mysqli_query($con, $insert_query);

    $_SESSION['status'] = "Insertion of data failed!";
    if ($insert_query_run) {
        if($stmt_for_inventory->execute()){
            $_SESSION['status'] = "Data inserted successfully !";
        }
    } else {
        $_SESSION['status'] = "Insertion of data failed!";
    }

    // Redirect to avoid form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
// Insert data end

// View data start
if (isset($_POST['click_view_btn'])) {
    $user_id = $_POST['user_id'];

    // Fetch user data based on $user_id from the database
    $query = "SELECT p.product_id, p.product_name, p.product_des, c.categories_name, p.sellPrice
FROM product p
JOIN categories c ON p.categories_id = c.categories_id
WHERE p.product_id = '$user_id'";
    $query_run = mysqli_query($con, $query);

    if (mysqli_num_rows($query_run) > 0) {
        $user_data = mysqli_fetch_assoc($query_run);
        // Assuming you want to return the data in some format (e.g., HTML or JSON)
        // Here is an example of returning the data as HTML
        echo "<p>Product Name: " . $user_data['product_name'] . "</p>";
        echo "<p>Product Description: " . $user_data['product_des'] . "</p>";
        echo "<p>Product Category: " . $user_data['categories_name'] . "</p>";
        echo "<p>Sell Price: " . $user_data['sellPrice'] . "</p>";
    } else {
        echo "No data found for this user.";
    }
    exit; // Stop further execution since this is an AJAX request
}
// view data end

// Edit data start

if (isset($_POST['click_edit_btn'])) {
    $id = $_POST['user_id'];
    $arrayresult = [];

    // Fetch user data based on $user_id from the database
    $query = "SELECT * FROM product WHERE product_id = '$id'";
    $query_run = mysqli_query($con, $query);

    if (mysqli_num_rows($query_run) > 0) {
        while ($row = mysqli_fetch_array($query_run)) {
            array_push($arrayresult, $row);
            header('Content-type: application/json');
            echo json_encode($arrayresult);

        }
    } else {
        echo "No data found for this user.";
    }
    exit; // Stop further execution since this is an AJAX request
}
// Edit data end



//update data start
if (isset($_POST['update_data'])) {
    // Get form data
    $product_id = $_POST['productid'];
    $product_name = $_POST['productname'];
    $product_des = $_POST['productdes'];
    $category_id = $_POST['categoryid']; // Category dropdown value
    $sell_price = $_POST['sellprice'];

    // Check if the selected category exists in the categories table
    $category_check_query = "SELECT * FROM categories WHERE categories_id = '$category_id'";
    $category_check_result = mysqli_query($con, $category_check_query);

    if (mysqli_num_rows($category_check_result) > 0) {
        // If category exists, proceed with the update
        $update_query = "UPDATE product 
                         SET product_name='$product_name', 
                             product_des='$product_des', 
                             categories_id='$category_id', 
                             sellPrice='$sell_price' 
                         WHERE product_id='$product_id'";

        $update_query_run = mysqli_query($con, $update_query);

        if ($update_query_run) {
            $_SESSION['status'] = "Product updated successfully!";
        } else {
            $_SESSION['status'] = "Product update failed!";
        }
    } else {
        // Category doesn't exist
        $_SESSION['status'] = "Invalid category. Please select a valid category.";
    }

    // Redirect back to the product page
    header("Location: product.php");
    exit;
}
//update data end


//Delete data start

if (isset($_POST['click_delete_btn'])) {
    $id = $_POST['user_id'];

    // Check for related rows in sales_items and order_items tables
    $check_sales_items_query = "SELECT * FROM sales_items WHERE product_id='$id'";
    $check_sales_items_result = mysqli_query($con, $check_sales_items_query);

    $check_order_items_query = "SELECT * FROM order_items WHERE product_id='$id'";
    $check_order_items_result = mysqli_query($con, $check_order_items_query);

    // If there are related rows, show confirmation popup
    if (mysqli_num_rows($check_sales_items_result) > 0 || mysqli_num_rows($check_order_items_result) > 0) {
        echo "<script>
            if (confirm('There are related records in sales_items or order_items. Do you still want to delete this product?')) {
                window.location.href = 'product.php?confirm_delete=1&product_id=$id';
            } else {
                window.location.href = 'product.php';
            }
        </script>";
    } else {
        // If no related rows, proceed with deletion
        $delete_sales_items_query = "DELETE FROM sales_items WHERE product_id='$id'";
        mysqli_query($con, $delete_sales_items_query);

        $delete_order_items_query = "DELETE FROM order_items WHERE product_id='$id'";
        mysqli_query($con, $delete_order_items_query);

        $delete_product_query = "DELETE FROM product WHERE product_id='$id'";
        $delete_product_query_run = mysqli_query($con, $delete_product_query);

        if ($delete_product_query_run) {
            $_SESSION['status'] = "Data deleted successfully!";
        } else {
            $_SESSION['status'] = "Data deletion failed!";
        }

        header("Location: product.php");
        exit;
    }
}

// Handle confirmation from the popup
if (isset($_GET['confirm_delete']) && $_GET['confirm_delete'] == 1) {
    $id = $_GET['product_id'];

    // Delete related rows in sales_items and order_items
    $delete_sales_items_query = "DELETE FROM sales_items WHERE product_id='$id'";
    mysqli_query($con, $delete_sales_items_query);

    $delete_order_items_query = "DELETE FROM order_items WHERE product_id='$id'";
    mysqli_query($con, $delete_order_items_query);

    // Now delete the product
    $delete_product_query = "DELETE FROM product WHERE product_id='$id'";
    $delete_product_query_run = mysqli_query($con, $delete_product_query);

    if ($delete_product_query_run) {
        $_SESSION['status'] = "Data deleted successfully!";
    } else {
        $_SESSION['status'] = "Data deletion failed!";
    }

    header("Location: product.php");
    exit;
}




//Delete Data end



/* code.php FILE End */

?>


<!-- ................................................................................product.php file start............................................................ -->

<?php
include('includes/header.php');
include('includes/navbar.php');
?>





<style>
    .modal {
        color: black;
        font-weight: 500;
    }
</style>

<!--Insert Modal Start-->
<div class="modal fade" id="insertdata" tabindex="-1" role="dialog" aria-labelledby="insertdataLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="insertdataLabel">Add Products</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="productname">Product Name</label>
                        <input type="text" class="form-control" name="productname" placeholder="Enter product name" required>
                    </div>

                    <div class="form-group">
                        <label for="productdes">Product Description</label>
                        <input type="text" class="form-control" name="productdes" placeholder="Enter description">
                    </div>
                    

                    <!-- <div class="form-group">
                        <label for="category">Category</label>
                        <select class="form-control" name="category">
                            <option value="Tshirt">T-shirt</option>
                            <option value="Shirt">Shirt</option>
                            <option value="trousers">Trousers</option>
                            <option value="skirts">Skirts</option>
                        </select>
                    </div> -->

                    <div class="form-group">
                        <label for="category">Product Category</label>
                        <select class="form-control" id="category_id" name="categoryid">
                            <option value="">Select a category</option>
                            <?php
                            // SQL query to get options
                            $category_query = "SELECT categories_id, categories_name FROM categories";
                            $category_query_run = mysqli_query($con, $category_query);

                            if ($category_query_run && mysqli_num_rows($category_query_run) > 0) {
                                // Output options
                                while ($row = mysqli_fetch_assoc($category_query_run)) {
                                    echo "<option value='" . $row['categories_id'] . "'>" . $row['categories_name'] . "</option>";
                                }
                            } else {
                                echo "<option>No categories available</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="productprice">Sell Price</label>
                        <input type="number" class="form-control" name="productprice" placeholder="Selling price" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" name="save_supp_data" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--Insert Modal End-->

<!-- View Modal Start -->
<div class="modal fade" id="viewuser" tabindex="-1" role="dialog" aria-labelledby="viewuserLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewuserLabel">View Product Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="view_user_data">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!--View Modal End-->

<!--Edit Modal Start-->
<div class="modal fade" id="editdata" tabindex="-1" role="dialog" aria-labelledby="editdataLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editdataLabel">Edit Product Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">

                        <input type="hidden" class="form-control" id="product_id" name="productid">
                    </div>

                    <div class="form-group mb-3">
                        <label for="name">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="productname" placeholder="enter name" required>
                    </div>
                    <!-- id use for jquery, name use for php -->

                    <div class="form-group">
                        <label for="email">Product Description</label>
                        <input type="text" class="form-control" id="product_des" name="productdes" placeholder="enter email" required>
                    </div>

                    <!-- <div class="form-group">
                        <label for="phone">Product Category</label>
                        <input type="text" class="form-control" id="category_id" name="categoryid" placeholder="enter number">
                    </div> -->

                    <div class="form-group">
                        <label for="category">Product Category</label>
                        <select class="form-control" id="category_id" name="categoryid">
                            <option value="">Select a category</option>
                            <?php
                            // SQL query to get options
                            $category_query = "SELECT categories_id, categories_name FROM categories";
                            $category_query_run = mysqli_query($con, $category_query);

                            if ($category_query_run && mysqli_num_rows($category_query_run) > 0) {
                                // Output options
                                while ($row = mysqli_fetch_assoc($category_query_run)) {
                                    echo "<option value='" . $row['categories_id'] . "'>" . $row['categories_name'] . "</option>";
                                }
                            } else {
                                echo "<option>No categories available</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="address">Sell Price</label>
                        <input type="text" class="form-control" id="sell_price" name="sellprice" placeholder="enter Sell price" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" name="update_data" class="btn btn-primary">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--Edit Modal End-->

<!--Delete Modal Start-->
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this product?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="product.php" method="POST" class="d-inline">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <button type="submit" name="click_delete_btn" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Delete Confirmation Modal End -->

<div class="container-fluid  mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Success messge show start -->
            <?php
            if (isset($_SESSION['status']) && $_SESSION['status'] != '') {

            ?>

                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Alert !</strong> <?php echo $_SESSION['status']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

            <?php
                unset($_SESSION['status']);
            }
            ?>
            <!-- Success messge show end -->

            <!-- Manage suppliers card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="text-dark fw-bold">MANAGE PRODUCTS</h4>
                    <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#insertdata">
                        Add Products
                    </button>
                    
                </div>

                <div class="card-body bg-light" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-bordered table-hover" width="100%" p-3>
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Category</th>
                                <th scope="col">Sell Price</th>
                                <th scope="col">View</th>
                                <th scope="col">Edit</th>
                                <th scope="col">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php
    $fetch_query = "SELECT p.product_id, p.product_name, p.product_des, c.categories_name, p.sellPrice FROM product p JOIN categories c ON p.categories_id = c.categories_id";
    $fetch_query_run = mysqli_query($con, $fetch_query);

    if (mysqli_num_rows($fetch_query_run) > 0) {
        while ($row = mysqli_fetch_array($fetch_query_run)) {
    ?>
            <tr>
                <td class="user_id"><?php echo $row['product_id']; ?></td>
                <td><?php echo $row['product_name']; ?></td>
                <td><?php echo $row['product_des']; ?></td>
                <td><?php echo $row['categories_name']; ?></td>
                <td><?php echo $row['sellPrice']; ?></td>
                <td>
                    <a href="#" class="btn btn-primary btn-sm view_data">View</a>
                </td>
                <td>
                    <a href="#" class="btn btn-success btn-sm edit_data">Edit</a>
                </td>
                <td>
                    <a href="#" class="btn btn-danger btn-sm delete_btn" data-id="<?php echo $row['product_id']; ?>">Delete</a>
                </td>
            </tr>
    <?php
        }
    } else {
    ?>
        <tr>
            <td colspan="8">No Record Found</td>
        </tr>
    <?php
    }
    ?>
</tbody>

                    </table>
                </div>
            </div>
        </div>


    </div>
</div>


<?php
include('includes/scripts.php');
include('includes/footer.php');
?>

<script>
    //view data start
    $(document).ready(function() {
        $('.view_data').click(function(e) {
            e.preventDefault();


            var user_id = $(this).closest('tr').find('.user_id').text();
            // console.log(user_id);

            $.ajax({
                method: "POST",
                url: "product.php",
                data: {
                    'click_view_btn': true,
                    'user_id': user_id,
                },
                success: function(response) {
                    // console.log(response);

                    $('.view_user_data').html(response);
                    $('#viewuser').modal('show');

                }
            });


        })
    });
    //view data end


    // Edit data start
    $(document).ready(function() {
        $('.edit_data').click(function(e) {
            e.preventDefault();


            var user_id = $(this).closest('tr').find('.user_id').text();
            console.log(user_id);

            $.ajax({
                method: "POST",
                url: "product.php",
                data: {
                    'click_edit_btn': true,
                    'user_id': user_id,
                },
                success: function(response) {
                    // console.log(response);

                    $.each(response, function(key, value) {
                        $('#product_id').val(value[0]);
                        $('#product_name').val(value[1]);
                        $('#product_des').val(value[2]);
                        $('#category_id').val(value[3]);
                        $('#sell_price').val(value[4]);
                    });
                    
                    $('#editdata').modal('show');

                }
            });


        })
    });
    // Edit data end

    //Delete data start
    $(document).ready(function() {
    $('.delete_btn').click(function(e) {
        e.preventDefault();

        var user_id = $(this).data('id'); // Get the product ID from the data-id attribute
        $('#delete_user_id').val(user_id); // Set the product ID in the hidden input field

        $('#deleteConfirmationModal').modal('show'); // Show the modal
    });
});

    //Delete data end
</script>