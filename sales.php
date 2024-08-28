<?php
// Start the session if needed
session_start();
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

if (isset($_POST['click_view_btn']) && isset($_POST['sales_id'])) {
    $sales_id = intval($_POST['sales_id']);

    // Fetch sale details
    $saleQuery = "SELECT sales_id, sale_date, total_amount FROM sales WHERE sales_id = $sales_id";
    $saleResult = mysqli_query($con, $saleQuery);

    if ($saleResult && mysqli_num_rows($saleResult) > 0) {
        $saleDetails = mysqli_fetch_assoc($saleResult);

        // Fetch sale items
        $itemsQuery = "SELECT p.product_name, si.quantity, si.price, si.total_price
                       FROM sales_items si
                       JOIN product p ON si.product_id = p.product_id
                       WHERE si.sales_id = $sales_id";
        $itemsResult = mysqli_query($con, $itemsQuery);

        if ($itemsResult) {
            $itemsHtml = '<h4>Sale ID: ' . htmlspecialchars($saleDetails['sales_id']) . '</h4>';
            $itemsHtml .= '<p>Sale Date: ' . htmlspecialchars($saleDetails['sale_date']) . '</p>';
            $itemsHtml .= '<p>Total Amount: $' . htmlspecialchars($saleDetails['total_amount']) . '</p>';
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
        echo '<p>No sale details found.</p>';
    }

    mysqli_close($con);
    exit;
}

?>

<?php
include_once('./includes/header.php');
include_once('./includes/navbar.php');
?>

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h4 class="card-title font-weight-bold">Sales</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="process_sales.php">
                        <!-- Sales Table Fields -->
                        <div class="form-row">
                            <div class="form-group col-lg-6">
                                <label for="sale_date">Sale Date</label>
                                <input type="date" class="form-control" id="sale_date" name="sale_date" required>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="total_amount">Total Amount</label>
                                <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" required readonly>
                            </div>

                            <!-- Discount Input Field -->
                            <div class="form-group col-lg-6">
                                <label for="discount">Discount (%)</label>
                                <input type="number" step="0.01" class="form-control" id="discount" name="discount" placeholder="Enter discount percentage" oninput="updatePrices()">
                            </div>

                            <div class="col-lg-12">
                                <hr>
                            </div>
                        </div>

                        <!-- Sales Items Table Fields -->
                        <div id="products-container">
                            <div class="form-row">
                                <div class="form-group col-lg-6 product-id-cls">
                                    <label for="product_id">Product</label>
                                    <select class="form-control product-select" id="product_id" name="product_id[]" onchange="updatePrices()" required>
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
                                    <input type="number" class="form-control quantity-input" id="quantity" name="quantity[]" oninput="updatePrices()" required>
                                    <div class="invalid-feedback">
                                        Value exceeds the maximum limit.
                                    </div>
                                </div>

                                <div class="form-group col-lg-3">
                                    <label for="price">Price</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price[]" required readonly>
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

<!-- sales page body -->
<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h4 class="card-title font-weight-bold">Recent Sales</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Sale ID</th>
                                <th>Sale Date</th>
                                <th>Total Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($con) {
                                $query = "SELECT sales_id, sale_date, total_amount FROM sales ORDER BY sale_date DESC LIMIT 10";
                                $result = mysqli_query($con, $query);

                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td class='sales_id_cls'>" . htmlspecialchars($row['sales_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['sale_date']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['total_amount']) . "</td>";
                                        echo "<td><button type='button' data-toggle='modal' data-target='#viewSaleModal' class='btn btn-info btn-sm view_data' data-sales-id='" . htmlspecialchars($row['sales_id']) . "'>View</button></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No recent sales found</td></tr>";
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
<!-- Modal -->
<div class="modal fade" id="viewSaleModal" tabindex="-1" aria-labelledby="viewSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSaleModalLabel">Sale Details</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body view_sale_data">
                <!-- Sale details will be loaded here -->
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
    // Fetch product options as HTML for the dropdown list
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

// Update the prices based on the selected product, quantity, and discount
function updatePrices() {
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const productSelects = document.querySelectorAll('select[name="product_id[]"]');
    const quantityInputs = document.querySelectorAll('input[name="quantity[]"]');
    const priceInputs = document.querySelectorAll('input[name="price[]"]');
    let totalAmount = 0;

    productSelects.forEach((select, index) => {
        const selectedOption = select.options[select.selectedIndex];
        const basePrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const quantity = parseFloat(quantityInputs[index].value) || 1;
        const finalPrice = basePrice * ((100 - discount) / 100);
        priceInputs[index].value = finalPrice.toFixed(2);

        // Update total amount
        totalAmount += finalPrice * quantity;

        // Log the selected product ID
        console.log('Selected Product ID:', select.value);
    });

    document.getElementById('total_amount').value = totalAmount.toFixed(2);
}

// Function to add a new product row
async function addProductRow() {
    const container = document.getElementById('products-container');
    const productOptions = await getProductOptions();

    const newRow = document.createElement('div');
    newRow.className = 'form-row';
    newRow.innerHTML = `
        <div class="form-group col-lg-6 product-id-cls">
            <label for="product_id">Product</label>
            <select class="form-control product-select" name="product_id[]" required>
                ${productOptions}
            </select>
        </div>
        <div class="form-group col-lg-3">
            <label for="quantity">Quantity</label>
            <input type="number" class="form-control quantity-input" name="quantity[]" required>
            <div class="invalid-feedback">
                Value exceeds the maximum limit.
            </div>
        </div>
        <div class="form-group col-lg-3">
            <label for="price">Price</label>
            <input type="number" step="0.01" class="form-control" name="price[]" required readonly>
        </div>
        <div class="col-12 d-lg-none">
            <hr>
        </div>
    `;
    container.appendChild(newRow);
    updatePrices(); // Update prices for the new row
}

// Event listener for change and input events to handle price updates
document.addEventListener('change', function(event) {
    if (event.target.matches('select[name="product_id[]"]')) {
        updatePrices();
    }
});

document.addEventListener('input', function(event) {
    if (event.target.matches('input[name="quantity[]"], #discount')) {
        updatePrices();
    }
});

// Initialize with existing rows
updatePrices();

// view data start
$(document).ready(function() {
    $('.view_data').click(function(e) {
        e.preventDefault();

        // Get the sales_id from the clicked row
        var sales_id = $(this).closest('tr').find('.sales_id_cls').text();

        $.ajax({
            method: "POST",
            url: "<?php echo $_SERVER['PHP_SELF']; ?>",
            data: {
                'click_view_btn': true,
                'sales_id': sales_id,
            },
            success: function(response) {
                $('.view_sale_data').html(response);
                $('#viewSaleModal').modal('show');
            }
        });
    });

    // Event delegation to handle changes in select elements
    $('#products-container').on('change', '.product-select', function() {
        var selectedProductId = $(this).val(); // Get the selected product ID
        console.log('Selected Product ID (via jQuery):', selectedProductId); // Output to console
    });

    // PHP-generated max value
    var maxValue = <?php echo $maxValue = 100; ?>; // Replace 100 with your PHP logic

    // Event delegation: Attach event listener to the container for input validation
    $('#products-container').on('input', '.quantity-input', function() {
        var currentValue = parseInt($(this).val(), 10); // Get the current input value

        if (isNaN(currentValue)) {
            currentValue = 0;
        }

        // Check if the current value is less than or equal to the max value
        if (currentValue > maxValue) {
            $(this).addClass('is-invalid'); // Add Bootstrap class for invalid input
        } else {
            $(this).removeClass('is-invalid'); // Remove invalid class if value is valid
        }
    });
});
// view data end
</script>

</script>