<?php
require('dbcon.php');
include_once('./access.php');

/* code.php file start */

// Insert data start
if (isset($_POST['save_supp_data'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $insert_query = "INSERT INTO supplier(name,email,phone,address) VALUES ('$name', '$email', '$phone', '$address')";
    $insert_query_run = mysqli_query($con, $insert_query);

    if ($insert_query_run) {
        $_SESSION['status'] = "Data inserted successfully!";
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
    $query = "SELECT * FROM supplier WHERE id = '$user_id'";
    $query_run = mysqli_query($con, $query);

    if (mysqli_num_rows($query_run) > 0) {
        $user_data = mysqli_fetch_assoc($query_run);
        echo "<p>Name: " . $user_data['name'] . "</p>";
        echo "<p>Email: " . $user_data['email'] . "</p>";
        echo "<p>Phone: " . $user_data['phone'] . "</p>";
        echo "<p>Address: " . $user_data['address'] . "</p>";
    } else {
        echo "No data found for this user.";
    }
    exit;
}
// View data end

// Edit data start
if (isset($_POST['click_edit_btn'])) {
    $id = $_POST['user_id'];
    $arrayresult = [];

    $fetch_query = "SELECT * FROM supplier WHERE id='$id'";
    $fetch_query_run = mysqli_query($con, $fetch_query);

    if (mysqli_num_rows($fetch_query_run) > 0) {
        while ($row = mysqli_fetch_array($fetch_query_run)) {
            array_push($arrayresult, $row);
            header('content-type: application/json');
            echo json_encode($arrayresult);
            exit;
        }
    } else {
        echo '<h4>No record found</h4>';
    }
}
// Edit data end

// Update data start
if (isset($_POST['update_data'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $update_query = "UPDATE supplier SET name='$name', email='$email', phone='$phone', address='$address' WHERE id='$id'";
    $update_query_run = mysqli_query($con, $update_query);

    if ($update_query_run) {
        $_SESSION['status'] = "Data updated successfully!";
        header('Location: supplier.php');
        exit;
    } else {
        $_SESSION['status'] = "Data updation failed!";
        header('Location: supplier.php');
        exit;
    }
}
// Update data end

// Delete data start
if (isset($_POST['click_delete_btn'])) {
    $id = $_POST['user_id'];

    $delete_query = "DELETE FROM supplier WHERE id='$id'";
    $delete_query_run = mysqli_query($con, $delete_query);

    if ($delete_query_run) {
        $_SESSION['status'] = "Data deleted successfully!";
        exit;
    } else {
        $_SESSION['status'] = "Data deletion failed!";
        exit;
    }
}
// Delete data end

// Search functionality start
if (isset($_POST['search_supplier'])) {
    $search_query = mysqli_real_escape_string($con, $_POST['search_query']);

    // SQL query to search in the supplier table
    $fetch_query = "SELECT * FROM supplier WHERE name LIKE '%$search_query%' OR email LIKE '%$search_query%' OR phone LIKE '%$search_query%' OR address LIKE '%$search_query%'";
} else {
    // Default query to fetch all records if no search query is provided
    $fetch_query = "SELECT * FROM supplier";
}

$fetch_query_run = mysqli_query($con, $fetch_query);
// Search functionality end

/* code.php FILE End */
?>

<!-- supplier.php file start -->

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

<!-- Insert Modal Start -->
<div class="modal fade" id="insertdata" tabindex="-1" role="dialog" aria-labelledby="insertdataLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="insertdataLabel">Add Suppliers</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="name">Supplier Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Enter name">
                    </div>

                    <div class="form-group">
                        <label for="email">Supplier Email</label>
                        <input type="email" class="form-control" name="email" placeholder="Enter email">
                    </div>

                    <div class="form-group">
                        <label for="phone">Supplier Contact No</label>
                        <input type="number" class="form-control" name="phone" placeholder="Enter number">
                    </div>

                    <div class="form-group">
                        <label for="address">Supplier Address</label>
                        <input type="text" class="form-control" name="address" placeholder="Enter address">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" name="save_supp_data" class="btn btn-primary">Add Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Insert Modal End -->

<!-- View Modal Start -->
<div class="modal fade" id="viewuser" tabindex="-1" role="dialog" aria-labelledby="viewuserLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewuserLabel">View Supplier Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="view_user_data"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- View Modal End -->

<!-- Edit Modal Start -->
<div class="modal fade" id="editdata" tabindex="-1" role="dialog" aria-labelledby="editdataLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editdataLabel">Edit Supplier Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <input type="hidden" class="form-control" id="supplier_id" name="id">
                    </div>

                    <div class="form-group mb-3">
                        <label for="name">Supplier Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter name">
                    </div>

                    <div class="form-group">
                        <label for="email">Supplier Email</label>
                        <input type="text" class="form-control" id="email" name="email" placeholder="Enter email">
                    </div>

                    <div class="form-group">
                        <label for="phone">Supplier Contact No</label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter number">
                    </div>

                    <div class="form-group">
                        <label for="address">Supplier Address</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Enter address">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" name="update_data" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Edit Modal End -->

<!-- Edit Middle Part start-->
<div class="container-fluid mt-5">
    <div class="card mt-4">
        <div class="card-header">
            <h4 class="text-dark fw-bold">MANAGE SUPPLIERS</h4>
            <button type="button" class="btn btn-primary float-right d-sm-block col-sm-auto col-12 p-2" data-toggle="modal" data-target="#insertdata">
                Add Suppliers
            </button>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="form-inline col-sm-auto col-12 pt-2 pt-sm-0 px-0">
                <div class="col-12 col-sm-auto p-0">
                    <input type="text" class="form-control p-2" name="search_query" placeholder="Search...">
                </div>
                <div class="col-12 col-sm-auto pt-2 pt-sm-0 px-0 pl-sm-2 pl-0">
                    <button type="submit" name="search_supplier" class="btn btn-dark pt-sm-2 col-12 col-sm-auto">Search</button>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Supplier Name</th>
                            <th>Supplier Email</th>
                            <th>Supplier Phone</th>
                            <th>Supplier Address</th>
                            <th>View</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($fetch_query_run) > 0) {
                            while ($row = mysqli_fetch_array($fetch_query_run)) {
                        ?>
                                <tr>
                                    <td class="user_id"><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['phone']; ?></td>
                                    <td><?php echo $row['address']; ?></td>
                                    <td>
                                        <a href="#" class="btn btn-primary btn-sm view_data">View</a>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-success btn-sm edit_data">Edit</a>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-danger btn-sm delete_btn" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
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
<!-- Edit Middle Part End-->

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>

<!-- View Modal Script -->
<script>
    $(document).ready(function() {
        $('.view_data').click(function(e) {
            e.preventDefault();

            var user_id = $(this).closest('tr').find('.user_id').text();
            $.ajax({
                type: "POST",
                url: "supplier.php",
                data: {
                    'click_view_btn': true,
                    'user_id': user_id,
                },
                success: function(response) {
                    $('.view_user_data').html(response);
                    $('#viewuser').modal('show');
                }
            });
        });
    });
</script>

<!-- Edit Modal Script -->
<script>
    $(document).ready(function() {
        $('.edit_data').click(function(e) {
            e.preventDefault();

            var user_id = $(this).closest('tr').find('.user_id').text();
            $.ajax({
                type: "POST",
                url: "supplier.php",
                data: {
                    'click_edit_btn': true,
                    'user_id': user_id,
                },
                success: function(response) {
                    $.each(response, function(key, value) {
                        $('#supplier_id').val(value['id']);
                        $('#name').val(value['name']);
                        $('#email').val(value['email']);
                        $('#phone').val(value['phone']);
                        $('#address').val(value['address']);
                    });

                    $('#editdata').modal('show');
                }
            });
        });
    });
</script>

<!-- Delete Script -->
<script>
    $(document).ready(function() {
        $('.delete_btn').click(function(e) {
            e.preventDefault();

            var user_id = $(this).closest('tr').find('.user_id').text();
            $.ajax({
                type: "POST",
                url: "supplier.php",
                data: {
                    'click_delete_btn': true,
                    'user_id': user_id,
                },
                success: function(response) {
                    alert(response);
                    location.reload(); // Reload page after deletion
                }
            });
        });
    });
</script>