<?php
// Start the session if needed
include_once('./dbcon.php');

// Function to get products as JSON
function getProductsJson()
{
    global $con; // Use the global connection variable

    // Check if connection is successful
    if (!$con) {
        return json_encode(['error' => 'Connection failed']);
    }

    // Prepare the query
    $query = "SELECT product_id, product_name, sellPrice FROM product";
    $result = mysqli_query($con, $query);

    // Check if the query was successful
    if (!$result) {
        return json_encode(['error' => 'Query failed']);
    }

    // Fetch products
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    // Free the result set and close the connection
    mysqli_free_result($result);

    return json_encode($products);
}

// If this file is called with an AJAX request to fetch products
if (isset($_GET['action']) && $_GET['action'] == 'getProducts') {
    echo getProductsJson();
    exit;
}

if (isset($_POST['click_view_btn']) && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);

    // Fetch order details
    $orderQuery = "SELECT order_id, order_date, total_amount FROM orders WHERE order_id = $order_id";
    $orderResult = mysqli_query($con, $orderQuery);

    if ($orderResult && mysqli_num_rows($orderResult) > 0) {
        $orderDetails = mysqli_fetch_assoc($orderResult);

        // Fetch order items
        $itemsQuery = "SELECT p.product_name, oi.quantity, oi.price, oi.total_price
                       FROM order_items oi
                       JOIN product p ON oi.product_id = p.product_id
                       WHERE oi.order_id = $order_id";
        $itemsResult = mysqli_query($con, $itemsQuery);

        if ($itemsResult) {
            $itemsHtml = '<h4>Order ID: ' . htmlspecialchars($orderDetails['order_id']) . '</h4>';
            $itemsHtml .= '<p>Order Date: ' . htmlspecialchars($orderDetails['order_date']) . '</p>';
            $itemsHtml .= '<p>Total Amount: $' . htmlspecialchars($orderDetails['total_amount']) . '</p>';
            $itemsHtml .= '<h5>Items:</h5>';
            $itemsHtml .= '<ul class="list-group">';

            while ($item = mysqli_fetch_assoc($itemsResult)) {
                $itemsHtml .= '<li class="list-group-item">' . htmlspecialchars($item['product_name']) . ' - '
                    . htmlspecialchars($item['quantity']) . ' - $' . htmlspecialchars($item['total_price']) . '</li>';
            }
            $itemsHtml .= '</ul>';

            echo $itemsHtml;
        } else {
            echo '<p>No items found.</p>';
        }
    } else {
        echo '<p>No order details found.</p>';
    }

    mysqli_close($con);
    exit;
}
?>

<?php
include_once('./includes/header.php');
include_once('./includes/navbar.php');
?>

<!-- Page Content -->
<!-- this is the new orders page -->
<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h4 class="card-title font-weight-bold">Orders</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="process_orders.php">
                        <!-- Order Table Fields -->
                        <div class="form-row">
                            <div class="form-group col-lg-6">
                                <label for="order_date">Order Date</label>
                                <input type="date" class="form-control" id="order_date" name="order_date" required>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="total_amount">Total Amount</label>
                                <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" required readonly>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="supplier-name-id">Supplier</label>
                                <select class="form-control" id="supplier-name-id" name="supplier-name-id" required>
                                    <?php
                                    // Query to get supplier names
                                    $query = "SELECT id, name FROM supplier";
                                    $result = mysqli_query($con, $query);

                                    // Check if the query was successful
                                    if ($result) {
                                        // Loop through each row and create an <option> element
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
                                        }
                                    } else {
                                        // Handle query error
                                        echo '<option value="">No suppliers available</option>';
                                    }

                                    // Close the result set
                                    mysqli_free_result($result);
                                    ?>
                                </select>
                            </div>


                            <div class="col-lg-12">
                                <hr>
                            </div>
                        </div>

                        <!-- Order Items Table Fields -->
                        <div id="products-container">
                            <div class="form-row">
                                <div class="form-group col-lg-6">
                                    <label for="product_id">Product</label>
                                    <select class="form-control" id="product_id" name="product_id[]" onchange="updatePrices()" required>
                                        <?php
                                        $products = json_decode(getProductsJson(), true);
                                        foreach ($products as $product) {
                                            echo "<option value='" . htmlspecialchars($product['product_id']) . "' data-price='" . htmlspecialchars($product['sellPrice']) . "'>" . htmlspecialchars($product['product_name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group col-lg-3">
                                    <label for="quantity">Quantity</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity[]" oninput="updatePrices()" required>
                                </div>

                                <div class="form-group col-lg-3">
                                    <label for="price">Price</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price[]" oninput="updatePrices()" required>
                                </div>
                                <div class="col-12 d-lg-none">
                                    <hr>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-secondary" onclick="addProductRow()">Add Another Product</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h4 class="card-title font-weight-bold">Recent Orders</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Order Date</th>
                                <th>Total Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($con) {
                                $query = "SELECT order_id, order_date, total_amount FROM orders ORDER BY order_date DESC LIMIT 10";
                                $result = mysqli_query($con, $query);

                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td class='order_id_cls'>" . htmlspecialchars($row['order_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['total_amount']) . "</td>";
                                        echo "<td><button type='button' data-toggle='modal' data-target='#viewOrderModal' class='btn btn-info btn-sm view_data' data-order-id='" . htmlspecialchars($row['order_id']) . "'>View</button></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No recent orders found</td></tr>";
                                }

                                mysqli_free_result($result);
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>Connection error</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- View Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewOrderModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body view_order_data">
                <!-- Order details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
include_once('./includes/footer.php');
include_once('./includes/scripts.php');
?>

<script>
    async function getProductOptions() {
        const response = await fetch('?action=getProducts');
        const data = await response.json();

        if (data.error) {
            console.error(data.error);
            return '';
        }

        return data.map(product =>
            `<option value="${product.product_id}" data-price="${product.sellPrice}">${product.product_name}</option>`
        ).join('');
    }

    function updatePrices() {
        const productSelects = document.querySelectorAll('select[name="product_id[]"]');
        const quantities = document.querySelectorAll('input[name="quantity[]"]');
        const prices = document.querySelectorAll('input[name="price[]"]');
        let totalAmount = 0;

        productSelects.forEach((select, index) => {
            // Get the price from the price input field
            const priceInput = prices[index];
            const quantityInput = quantities[index];

            // Convert price and quantity to numbers
            const price = parseFloat(priceInput.value) || 0; // Ensure price is a number
            const quantity = parseFloat(quantityInput.value) || 0; // Ensure quantity is a number

            // Calculate item total
            const itemTotal = price * quantity;

            // Update the total amount for each item
            prices[index].dataset.itemTotal = itemTotal.toFixed(2);

            // Add the item total to the total amount
            totalAmount += itemTotal;
        });

        // Update the total amount input field
        document.getElementById('total_amount').value = totalAmount.toFixed(2);
    }

    // Function to initialize event listeners for existing rows and to handle dynamically added rows
    function initializeEventListeners() {
        document.querySelectorAll('select[name="product_id[]"], input[name="quantity[]"], input[name="price[]"]').forEach(element => {
            element.addEventListener('input', updatePrices);
        });
    }

    // Add a new product row dynamically
    async function addProductRow() {
        const productsContainer = document.getElementById('products-container');
        const newRow = document.createElement('div');
        newRow.className = 'form-row';

        const productOptions = await getProductOptions();

        newRow.innerHTML =
            `<div class="form-group col-lg-6">
            <label for="product_id">Product</label>
            <select class="form-control" name="product_id[]" required>
                ${productOptions}
            </select>
        </div>
        <div class="form-group col-lg-3">
            <label for="quantity">Quantity</label>
            <input type="number" class="form-control" name="quantity[]" required>
        </div>
        <div class="form-group col-lg-3">
            <label for="price">Price</label>
            <input type="number" step="0.01" class="form-control" name="price[]" required>
        </div>
        <div class="col-12 d-lg-none">
            <hr>
        </div>`;
        productsContainer.appendChild(newRow);

        // Reinitialize event listeners to include new rows
        initializeEventListeners();
    }

    // Initialize event listeners on page load
    initializeEventListeners();

    document.querySelectorAll('.view_data').forEach(button => {
        button.addEventListener('click', async (e) => {
            const orderId = e.target.dataset.orderId;
            const response = await fetch('orders-new.php', {
                method: 'POST',
                body: new URLSearchParams({
                    'click_view_btn': true,
                    'order_id': orderId
                })
            });
            const data = await response.text();
            document.querySelector('.view_order_data').innerHTML = data;
        });
    });

    // Check if the query parameter exists and show the modal
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const showModal = urlParams.get('showModal');

        if (showModal === 'true') {
            showAlertModal('Successfull !', 'Order has been placed successfully.');

            // Remove the query parameter from the URL without reloading the page
            const newUrl = window.location.href.split('?')[0];
            window.history.replaceState(null, '', newUrl);
        }
    };
</script>