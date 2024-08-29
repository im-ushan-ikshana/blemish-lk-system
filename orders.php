<?php
include_once('./dbcon.php');
include_once('./access.php');

// view order
if (isset($_POST['view_order'])) {
    $order_id = $_POST['id'];

    // Prepared statement to prevent SQL injection
    $query = $con->prepare("SELECT * FROM orders WHERE order_id = ?");
    $query->bind_param("i", $order_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows == 0) {
        echo "Order not found.";
        exit;
    } else {
        $order_data = $result->fetch_assoc();

        $query_for_find_products = $con->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $query_for_find_products->bind_param("i", $order_id);
        $query_for_find_products->execute();
        $order_items = $query_for_find_products->get_result()->fetch_all(MYSQLI_ASSOC);

        $supplier_query = $con->prepare("SELECT name FROM supplier WHERE id = ?");
        $supplier_query->bind_param("i", $order_data['supplier_id']);
        $supplier_query->execute();
        $supplier_name = $supplier_query->get_result()->fetch_assoc()['name'];

        $output = '<p><b>Order ID: </b>' . $order_data['order_id'] . '</p>'
            . '<p><b>Order Date: </b>' . $order_data['order_date'] . '</p>'
            . '<p><b>Total Amount: </b>' . $order_data['total_amount'] . '</p>'
            . '<p><b>Supplier: </b>' . $supplier_name . '</p>'
            . '<p><b>Order Items: </b></p>';

        $output .= '<div style="padding-left: 2%;">';

        // Query to get items for a specific order_id
        $query_for_find_item = "SELECT product_id, quantity, price FROM order_items WHERE order_id = ?";
        $stmt = $con->prepare($query_for_find_item);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $query_run = $stmt->get_result();

        if ($query_run->num_rows > 0) {
            while ($row = $query_run->fetch_assoc()) {
                // Query to get product name
                $product_query = "SELECT product_name FROM product WHERE product_id = ?";
                $product_stmt = $con->prepare($product_query);
                $product_stmt->bind_param("i", $row['product_id']);
                $product_stmt->execute();
                $product_result = $product_stmt->get_result();
                $product_name = $product_result->fetch_assoc()['product_name'];

                $output .= '<p">' .
                    htmlspecialchars($product_name) . ' - ' .
                    'Quantity: ' . htmlspecialchars($row['quantity']) . ', ' .
                    'Price: $' . number_format($row['price'], 2) .
                    '</p>';
            }
        } else {
            $output .= 'No Record Found';
        }

        $output .= '</div>';

        echo $output;
    }
    exit;
}

//view order-item details (function)
if (isset($_POST['view_order_item'])) {
    $order_item_id = $_POST['id'];

    // Prepared statement to prevent SQL injection
    $query = $con->prepare("SELECT * FROM order_items WHERE order_item_id = ?");
    $query->bind_param("i", $order_item_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows == 0) {
        echo "Order item not found.";
        exit;
    } else {
        $product_name_query = $con->prepare("SELECT product_name FROM product WHERE product_id = ?");
        $order_item_data = $result->fetch_assoc();
        $product_id = $order_item_data['product_id'];
        $product_name_query->bind_param("i", $product_id);
        $product_name_query->execute();
        $product_name = $product_name_query->get_result()->fetch_assoc()['product_name'];

        $output = '<p><b>Order Item ID: </b>' . $order_item_data['order_item_id'] . '</p>'
            . '<p><b>Order ID: </b>' . $order_item_data['order_id'] . '</p>'
            . '<p><b>Product ID: </b>' . $order_item_data['product_id'] . '</p>'
            . '<p><b>Product Name: </b>' . $product_name . '</p>'
            . '<p><b>Quantity: </b>' . $order_item_data['quantity'] . '</p>'
            . '<p><b>Price: </b>' . $order_item_data['price'] . '</p>'
            . '<p><b>Total Price: </b>' . $order_item_data['total_price'] . '</p>';

        echo $output;
    }
    exit;
}


//delete order item
// Check if the request is to delete an order item
if (isset($_POST['delete_order_item'])) {
    $order_item_id = $_POST['order_item_id'];
    $response = [];

    // Prepare the SQL query to delete the order item
    $delete_query = "DELETE FROM order_items WHERE order_item_id = ?";
    $stmt = mysqli_prepare($con, $delete_query);

    if ($stmt) {
        // Bind parameter and execute statement
        mysqli_stmt_bind_param($stmt, "i", $order_item_id);
        $success = mysqli_stmt_execute($stmt);
        error_log('Debug message: Reached this point in the script');

        if ($success) {
            $response = ['status' => 'success', 'message' => 'Order item deleted successfully'];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to delete order item'];
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        $response = ['status' => 'error', 'message' => 'Query preparation failed'];
    }

    // Set content type and output the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}


// delete order
// Check if the request is to delete an order
// delete order
if (isset($_POST['delete_order'])) {
    $delete_order_id = $_POST['delete_order_id'];
    $response_order = [];

    // Sanitize input
    $delete_order_id = intval($delete_order_id); // Ensure it's an integer

    // Prepare the SQL query to delete the order
    $delete_query = "DELETE FROM orders WHERE order_id = ?";
    $stmt = mysqli_prepare($con, $delete_query);

    if ($stmt) {
        // Bind parameter and execute statement
        mysqli_stmt_bind_param($stmt, "i", $delete_order_id);
        $success = mysqli_stmt_execute($stmt);

        if ($success) {
            $response_order = ['status' => 'success', 'message' => 'Order deleted successfully'];
        } else {
            // Output MySQL error message for debugging
            $response_order = ['status' => 'error', 'message' => 'Failed to delete order: ' . mysqli_error($con)];
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        // Output MySQL error message for debugging
        $response_order = ['status' => 'error', 'message' => 'Query preparation failed: ' . mysqli_error($con)];
    }

    // Set content type and output the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response_order);
    exit;
}

//edit order
if (isset($_POST['edit_order'])) {
    $order_id = $_POST['order_id'];
    $arrayResult = [];

    // Fetch order details from the database
    $fetch_query = "SELECT * FROM orders WHERE order_id = '$order_id'";
    $fetch_query_run = mysqli_query($con, $fetch_query);

    if (mysqli_num_rows($fetch_query_run) > 0) {
        while ($row = mysqli_fetch_assoc($fetch_query_run)) {
            // Push the fetched order details into the result array
            array_push($arrayResult, $row);
        }
        // Send the response as a JSON object
        header('Content-Type: application/json');
        echo json_encode($arrayResult);
        exit;
    } else {
        // No record found case
        echo json_encode(['status' => 'No record found']);
    }
}

//update order - data from edit order modal
if (isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $order_date = $_POST['order_date'];
    $total_amount = $_POST['total_amount'];
    $supplier_id = $_POST['supplier_id'];

    // Prepare the SQL update query
    $update_query = "UPDATE orders 
                     SET order_date = ?, total_amount = ?, supplier_id = ? 
                     WHERE order_id = ?";

    // Initialize prepared statement
    $stmt = mysqli_prepare($con, $update_query);

    if ($stmt) {
        // Bind parameters to the prepared statement
        // 's' for string, 'd' for decimal, 'i' for integer
        mysqli_stmt_bind_param($stmt, "sdii", $order_date, $total_amount, $supplier_id, $order_id);

        // Execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Order updated successfully";
        } else {
            $_SESSION['status'] = "Order update failed";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['status'] = "Query preparation failed";
    }

    header('Location: orders.php');
    exit();
}



// Check if the request is for editing order item
if (isset($_POST['edit_order_item'])) {
    $order_item_id = $_POST['order_item_id'];
    $response = [];

    // Prepare the SQL query to fetch order item details
    $fetch_query = "SELECT * FROM order_items WHERE order_item_id = ?";
    $stmt = mysqli_prepare($con, $fetch_query);

    if ($stmt) {
        // Bind parameter and execute statement
        mysqli_stmt_bind_param($stmt, "i", $order_item_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Check if the result has rows
        if (mysqli_num_rows($result) > 0) {
            // Fetch data and prepare the response
            $data = mysqli_fetch_assoc($result);
            $response = ['status' => 'success', 'data' => $data];
        } else {
            $response = ['status' => 'error', 'message' => 'No record found'];
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        $response = ['status' => 'error', 'message' => 'Query preparation failed'];
    }

    // Set content type and output the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}



// Update order item - data from edit order item modal
if (isset($_POST['update_order_item'])) {
    $order_item_id = $_POST['order_item_id'];
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $total_price = $_POST['total_price'];

    // Prepare the SQL update query
    $update_query = "UPDATE order_items 
                     SET order_id = ?, product_id = ?, quantity = ?, price = ?, total_price = ? 
                     WHERE order_item_id = ?";

    // Initialize prepared statement
    $stmt = mysqli_prepare($con, $update_query);

    if ($stmt) {
        // Bind parameters to the prepared statement
        // 'i' for integer, 'd' for decimal
        mysqli_stmt_bind_param($stmt, "iiiddi", $order_id, $product_id, $quantity, $price, $total_price, $order_item_id);

        // Execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Order item updated successfully";
        } else {
            $_SESSION['status'] = "Order item update failed";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['status'] = "Query preparation failed";
    }

    header('Location: orders.php');
    exit();
}




//update order item
if (isset($_POST['update_order_item'])) {
    $order_item_id = $_POST['order_item_id'];
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $total_price = $_POST['total_price'];
    $query = "UPDATE order_items SET order_id = '$order_id', product_id = '$product_id', quantity = '$quantity', price = '$price', total_price = '$total_price' WHERE order_item_id = $order_item_id";
    $query_run = mysqli_query($con, $query);
    if ($query_run) {
        $_SESSION['success'] = "Order item is updated successfully";
        header('Location: orders.php');
    } else {
        $_SESSION['status'] = "Order item is not updated";
        header('Location: orders.php');
    }
}

?>

<?php
include('includes/header.php');
include('includes/navbar.php');
?>
<!-- show orders table using php-->
<div class="container-fluid  mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <?php
            if (isset($_SESSION['status']) && $_SESSION['status'] != '') {

            ?>

                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Alert - </strong> <?php echo $_SESSION['status']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

            <?php
                unset($_SESSION['status']);
            }
            ?>
            <!-- show orders table -->
            <div class="card">
                <div class="card-header">
                    <h4 class="text-dark font-weight-bold">Orders</h4>
                </div>
                <div class="card-body  " style="max-height: 60vh;
                overflow-y: auto;">
                    <table class="table table-bordered table-hover" width="100%" p-3>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Order Date</th>
                                <th>Total Amount</th>
                                <th>Supplier</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM orders";
                            $query_run = mysqli_query($con, $query);
                            if (mysqli_num_rows($query_run) > 0) {
                                while ($row = mysqli_fetch_assoc($query_run)) {
                            ?>
                                    <tr>
                                        <td class="order_id_cls"><?php echo $row['order_id']; ?></td>
                                        <td><?php echo $row['order_date']; ?></td>
                                        <td><?php echo $row['total_amount']; ?></td>
                                        <td><?php
                                            $query_for_supplier = "SELECT name FROM supplier WHERE id = " . $row['supplier_id'];
                                            echo mysqli_fetch_assoc(mysqli_query($con, $query_for_supplier))['name'];
                                            ?></td>
                                        <td style="white-space: nowrap;">
                                            <button type="button" class="btn btn-success editbtn">Edit</button>
                                            <button type="button" class="btn btn-primary viewbtn" data-toggle="modal" data-target="#viewOrderModal">View</button>
                                            <button type="button" class="btn btn-danger deletebtn">Delete</button>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo "No Record Found";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- order items table -->
<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <?php
            if (isset($_SESSION['status_2']) && $_SESSION['status_2'] != '') {

            ?>

                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Alert :</strong> <?php echo $_SESSION['status_2']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

            <?php
                unset($_SESSION['status_2']);
            }
            ?>
            <div class="card">
                <div class="card-header">
                    <h4 class="text-dark font-weight-bold">Order Items</h4>
                </div>
                <div class="card-body" style="max-height: 60vh;
                overflow-y: auto;">
                    <table class="table table-bordered table-hover" width="100%" p-3>
                        <thead>
                            <tr>
                                <th>Order Item ID</th>
                                <th>Order ID</th>
                                <th>Product ID</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM order_items";
                            $query_run = mysqli_query($con, $query);
                            if (mysqli_num_rows($query_run) > 0) {
                                while ($row = mysqli_fetch_assoc($query_run)) {
                            ?>
                                    <tr>
                                        <td class="order_item_id_cls"> <?php echo $row['order_item_id']; ?> </td>
                                        <td><?php echo $row['order_id']; ?></td>
                                        <td><?php echo $row['product_id']; ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td><?php echo $row['price']; ?></td>
                                        <td><?php echo $row['total_price']; ?></td>
                                        <td style="white-space: nowrap;">
                                            <button type="button" class="btn btn-success editbtn_2">Edit</button>
                                            <button type="button" class="btn btn-primary viewbtn_2">View</button>
                                            <button type="button" class="btn btn-danger deletebtn_2">Delete</button>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo "No Record Found";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- view order modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" role="dialog" aria-labelledby="viewuserLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewuserLabel">View Order Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="view_order_data">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- view order-items model -->
<div class="modal fade" id="viewOrderItemModal" tabindex="-1" role="dialog" aria-labelledby="viewuserLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewuserLabel">View Order Item Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="view_order_item_data">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Order Modal Start -->
<div class="modal fade" id="editOrderModal" tabindex="-1" role="dialog" aria-labelledby="editOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOrderModalLabel">Edit Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <div class="modal-body">
                    <!-- Hidden field to store the Order ID -->
                    <input type="hidden" class="form-control" id="order_id" name="order_id">

                    <div class="form-group mb-3">
                        <label for="order_date">Order Date</label>
                        <input type="date" class="form-control" id="order_date" name="order_date" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="total_amount">Total Amount</label>
                        <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" placeholder="Enter Total Amount" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="supplier_id">Supplier Id</label>
                        <input type="number" class="form-control" id="supplier_id" name="supplier_id" placeholder="Enter Supplier ID" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="update_order" class="btn btn-primary">Update Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Edit Order Modal End -->

<!-- Edit Order Item Modal Start -->
<div class="modal fade" id="editOrderItemModal" tabindex="-1" role="dialog" aria-labelledby="editOrderItemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOrderItemModalLabel">Edit Order Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <div class="modal-body">
                    <!-- Hidden field to store the Order Item ID -->
                    <input type="hidden" class="form-control" id="order_item_id" name="order_item_id">

                    <div class="form-group mb-3">
                        <label for="order_id">Order ID</label>
                        <input type="number" class="form-control" id="order_id_2" name="order_id" placeholder="Enter Order ID" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="product_id">Product ID</label>
                        <input type="number" class="form-control" id="product_id" name="product_id" placeholder="Enter Product ID" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter Quantity" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="price">Price</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" placeholder="Enter Price" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="total_price">Total Price</label>
                        <input type="number" step="0.01" class="form-control" id="total_price" name="total_price" placeholder="Enter Total Price" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="update_order_item" class="btn btn-primary">Update Order Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Edit Order Item Modal End -->

<!-- Delete Confirmation Modal Start(for order table)-->
<div class="modal fade" id="deleteOrderModal" tabindex="-1" role="dialog" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteOrderModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this order?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
<!-- Delete Confirmation Modal End -->

<!-- Delete Confirmation Modal Start -->
<div class="modal fade" id="deleteOrderItemModal" tabindex="-1" role="dialog" aria-labelledby="deleteOrderItemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteOrderItemModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this order item?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn_2">Delete</button>
            </div>
        </div>
    </div>
</div>
<!-- Delete Confirmation Modal End -->





<?php
include('includes/scripts.php');
include('includes/footer.php');
?>


<script>
    $(document).ready(function() {

        function handleViewButtonClick(buttonClass, idClass, ajaxData, modalSelector, contentSelector) {
            $(buttonClass).click(function(e) {
                e.preventDefault();

                var id = $(this).closest('tr').find(idClass).text();
                console.log(id);

                if (!id) {
                    alert('ID not found');
                    return;
                }

                $.ajax({
                    method: "POST",
                    url: "orders.php",
                    data: ajaxData(id),
                    success: function(response) {
                        $(contentSelector).html(response);
                        $(modalSelector).modal('show');
                    }
                });
            });
        }

        handleViewButtonClick('.viewbtn', '.order_id_cls',
            function(id) {
                return {
                    'view_order': true,
                    'id': id
                };
            },
            '#viewOrderModal', '.view_order_data'
        );

        handleViewButtonClick('.viewbtn_2', '.order_item_id_cls',
            function(id) {
                return {
                    'view_order_item': true,
                    'id': id
                };
            },
            '#viewOrderItemModal', '.view_order_item_data'
        );

    });

    $(document).ready(function() {
        $('.editbtn').click(function(e) {
            e.preventDefault();

            // Retrieve the order_id from the clicked row
            var order_id = $(this).closest('tr').find('.order_id_cls').text();
            console.log(order_id);

            // Make an AJAX request to fetch order details
            $.ajax({
                method: "POST",
                url: "orders.php",
                data: {
                    'edit_order': true,
                    'order_id': order_id,
                },
                success: function(response) {
                    // Assuming the response is a JSON object with order details
                    $.each(response, function(key, value) {
                        $('#order_id').val(value['order_id']);
                        $('#order_date').val(value['order_date']);
                        $('#total_amount').val(value['total_amount']);
                        $('#supplier_id').val(value['supplier_id']);
                    });

                    // Show the modal with order data populated
                    $('#editOrderModal').modal('show');
                }
            });
        });
    });


    $(document).ready(function() {
        $('.editbtn_2').click(function(e) {
            e.preventDefault();

            // Retrieve the order_item_id from the clicked row
            var order_item_id = $(this).closest('tr').find('.order_item_id_cls').text();

            // Make an AJAX request to fetch order item details
            $.ajax({
                method: "POST",
                url: "orders.php", // Adjust the URL to your backend script
                data: {
                    'edit_order_item': true,
                    'order_item_id': order_item_id
                },
                dataType: "json",
                success: function(response) {

                    $.each(response, function(key, value) {
                        // Populate modal fields with fetched data
                        $('#order_item_id').val(value['order_item_id']);
                        $('#order_id_2').val(value['order_id']);
                        $('#product_id').val(value['product_id']);
                        $('#quantity').val(value['quantity']);
                        $('#price').val(value['price']);
                        $('#total_price').val(value['total_price']);
                    });

                    $('#editOrderItemModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response Text:', xhr.responseText); // Log the actual response text
                }
            });
        });
    });

    $(document).ready(function() {
        var orderItemId; // Variable to store the order item ID

        // Show the confirmation modal and set the order item ID
        $('.deletebtn_2').click(function(e) {
            e.preventDefault();

            // Retrieve the order_item_id from the clicked button
            orderItemId = $(this).closest('tr').find('.order_item_id_cls').text();
            console.log(orderItemId);

            // Show the confirmation modal
            $('#deleteOrderItemModal').modal('show');
        });

        // Handle the confirmation button click
        $('#confirmDeleteBtn_2').click(function() {
            // Make an AJAX request to delete the order item
            $.ajax({
                method: "POST",
                url: "orders.php", // URL to your PHP script
                data: {
                    'delete_order_item': true,
                    'order_item_id': orderItemId
                },
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        // Hide the confirmation modal
                        $('#deleteOrderItemModal').modal('hide');
                        window.location.reload();
                    }

                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    $('#deleteOrderItemModal').modal('hide');
                }
            });
        });
    });


    $(document).ready(function() {
        var orderId; // Variable to store the order item ID

        // Show the confirmation modal and set the order item ID
        $('.deletebtn').click(function(e) {
            e.preventDefault();

            // Retrieve the order_item_id from the clicked button
            orderId = $(this).closest('tr').find('.order_id_cls').text();
            console.log(orderId);

            // Show the confirmation modal
            $('#deleteOrderModal').modal('show');
        });

        // Handle the confirmation button click
        $('#confirmDeleteBtn').click(function() {
            // Make an AJAX request to delete the order item
            $.ajax({
                method: "POST",
                url: "orders.php", // URL to your PHP script
                data: {
                    'delete_order': true,
                    'delete_order_id': orderId,
                },
                success: function(response_order) {
                    if (response_order.status === 'success') {
                        // Hide the confirmation modal
                        $('#deleteOrderModal').modal('hide');
                        window.location.reload();
                    } else {
                        alert('Failed to delete order');
                    }

                },
                dataType: "json",
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    $('#deleteOrderModal').modal('hide');
                }
            });
        });
    });
</script>