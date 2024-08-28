<?php
require('dbcon.php');
include_once('./access.php');

/* --------------------------------------------------------------------------------code.php file start-------------------------------------------------------------------- */

// Insert data start
if (isset($_POST['save_supp_data'])) {
    $username = $_POST['name'];
    $pass = $_POST['pass'];
    $confirmpass = $_POST['confirmpass'];
    $role = $_POST['role'];

    // Basic password validation
    $errors = [];
    if (strlen($pass) < 8) {
        $errors[] = "Password must be at least 8 characters long!";
    }
    if (!preg_match("/[A-Z]/", $pass)) {
        $errors[] = "Password must include at least one uppercase letter!";
    }
    if (!preg_match("/[a-z]/", $pass)) {
        $errors[] = "Password must include at least one lowercase letter!";
    }
    if (!preg_match("/[0-9]/", $pass)) {
        $errors[] = "Password must include at least one number!";
    }
    if (!preg_match("/[\W]/", $pass)) {
        $errors[] = "Password must include at least one special character!";
    }

    // Check if password and confirm password match
    if ($pass !== $confirmpass) {
        $errors[] = "Password and Confirm Password do not match!";
    }

    // If there are errors, set them in the session
    if (!empty($errors)) {
        $_SESSION['status'] = implode("<br>", $errors);
    } else {
        // Check if username is unique
        $check_username_query = "SELECT * FROM users WHERE username=?";
        $stmt = mysqli_prepare($con, $check_username_query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $check_username_run = mysqli_stmt_get_result($stmt);
    
        if (mysqli_num_rows($check_username_run) > 0) {
            $_SESSION['status'] = "Username already exists!";
        } else {
            // If all validations pass, insert the new user
            $hashed_pass = hash('sha256', $pass); // Hash the password using SHA-256
            $insert_query = "INSERT INTO users(username, password, role) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_pass, $role);
            $insert_query_run = mysqli_stmt_execute($stmt);
    
            if ($insert_query_run) {
                $_SESSION['status'] = "Data inserted successfully!";
            } else {
                $_SESSION['status'] = "Insertion of data failed!";
            }
        }
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
    $query = "SELECT * FROM users WHERE user_id = '$user_id'";
    $query_run = mysqli_query($con, $query);

    if (mysqli_num_rows($query_run) > 0) {
        $user_data = mysqli_fetch_assoc($query_run);
        // Assuming you want to return the data in some format (e.g., HTML or JSON)
        // Here is an example of returning the data as HTML
        echo "<p>Name: " . $user_data['username'] . "</p>";
        echo "<p>Role: " . $user_data['role'] . "</p>";
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

    // echo $id;
    $fetch_query = "SELECT * FROM users WHERE user_id='$id'";
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



//update data start
if (isset($_POST['update_data'])) {
    $id = $_POST['user_id'];
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if ($password !== $confirm_password) {
        $errors[] = "Password and Confirm Password do not match!";
    }

    // If there are errors, set them in the session
    if (!empty($errors)) {
        $_SESSION['status'] = implode("<br>", $errors);
    } else {
        $hashed_pass = hash('sha256', $password);
        $update_query = "UPDATE users SET username='$user_name', password = '$hashed_pass', role = '$role'  WHERE user_id='$id'";
        $update_query_run = mysqli_query($con, $update_query);
    }

    if ($update_query_run) {
        $_SESSION['status'] = "Data updated successfully !";
        header('Location: users.php');
        exit;
    } else {
        $_SESSION['status'] = "Data updation failed !";
        header('Location: users.php');
        exit;
    }
}
//update data end



//Read Data start
if (isset($_POST['click_delete_btn'])) {
    $id = $_POST['user_id'];
    $delete_permissions_query = "DELETE FROM permissions WHERE user_id='$id'";
    $delete_query_run_permissions = mysqli_query($con, $delete_permissions_query);

    $delete_query = "DELETE FROM users WHERE user_id='$id'";
    $delete_query_run_users = mysqli_query($con, $delete_query);

    if ($delete_query_run_permissions && $delete_query_run_users) {
        $_SESSION['status'] = "Data deleted successfully !";
        exit;
    } else {
        $_SESSION['status'] = "Data deletion failed !";
        exit;
    }
}
//Read Data end

/* code.php FILE End */

?>


<!-- ................................................................................supplier.php file start............................................................ -->

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
                <h5 class="modal-title" id="insertdataLabel">Add User</h5>
                
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="name">User Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Enter name">
                    </div>

                    <div class="form-group mb-3">
                        <label for="role">Role</label>
                        <select class="form-control" name="role">
                            <option value="" disabled selected>Choose one</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>


                    <div class="form-group mb-3">
                        <label for="name">Enter Password</label>
                        <input type="password" class="form-control" name="pass" placeholder="Password">
                    </div>

                    <div class="form-group mb-3">
                        <label for="name">Re-enter Password</label>
                        <input type="password" class="form-control" name="confirmpass" placeholder="Password">
                    </div>



                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" name="save_supp_data" class="btn btn-primary">Add user</button>
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
                <h5 class="modal-title" id="viewuserLabel">View User Details</h5>
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
                <h5 class="modal-title" id="editdataLabel">Edit Supplier Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">

                        <input type="hidden" class="form-control" id="user_id" name="user_id">
                    </div>

                    <div class="form-group mb-3">
                        <label for="name">User Name</label>
                        <input type="text" class="form-control" id="name" name="user_name" placeholder="enter name">

                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="enter password">

                        <label for="confirm passwrod">confirm passwrod</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="enter confirm password">


                        <label for="role">Role</label>
                        <select class="form-control" name="role">
                            <option value="" disabled selected>Choose one</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>




                    </div>
                    <!-- id use for jquery, name use for php -->

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

<div class="container-fluid  mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">

        <!-- Success messge show start -->
            <?php
            if (isset($_SESSION['status']) && $_SESSION['status'] != '') {

            ?>

                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong></strong> <?php echo $_SESSION['status']; ?>
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
                    <h4 class="text-dark fw-bold">MANAGE USERS</h4>
                    <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#insertdata">
                        Add User
                    </button>
                </div>

                <div class="card-body bg-light" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-bordered table-hover" width="100%" p-3>
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">View</th>
                                <th scope="col">Edit</th>
                                <th scope="col">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $fetch_query = "SELECT * FROM users";
                            $fetch_query_run = mysqli_query($con, $fetch_query);

                            if (mysqli_num_rows($fetch_query_run) > 0) {
                                while ($row = mysqli_fetch_array($fetch_query_run)) {
                            ?>
                                    <tr>
                                        <td class="user_id"><?php echo $row['user_id']; ?></td>
                                        <td><?php echo $row['username']; ?></td>
                                        <td>
                                            <a href="#" class="btn btn-primary btn-sm view_data">View</a>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-success btn-sm edit_data">Edit</a>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-danger btn-sm delete_btn">Delete</a>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr colspan="4">No Record Found</tr>
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
            //  console.log(user_id);

            $.ajax({
                method: "POST",
                url: "users.php",
                data: {
                    'click_view_btn': true,
                    'user_id': user_id,
                },
                success: function(response) {
                //console.log(response);

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
                url: "users.php",
                data: {
                    'click_edit_btn': true,
                    'user_id': user_id,
                },
                success: function(response) {
                    console.log(response);

                    $.each(response, function(key, value) {
                        // console.log(value['name']);
                        $('#user_id').val(value['user_id']);
                        $('#name').val(value['username']);
                        $('#role').val(value['role']);
                        //id,name,email,.. are database column names.    user_id,name,email,.. are form's field ids used in modal
                    });


                    $('#editdata').modal('show');
                    console.log(response);
                }

             
            });


        })
    });
    // Edit data end

    //Delete data start
    $(document).ready(function() {
        $('.delete_btn').click(function(e) {
            e.preventDefault();
            var confirmDelete = confirm("Are you sure you want to delete this record?");

            if(confirmDelete){
                var user_id = $(this).closest('tr').find('.user_id').text();
                console.log(user_id);

                $.ajax({
                    method: "POST",
                    url: "users.php",
                    data: {
                        'click_delete_btn': true,
                        'user_id': user_id,
                    },
                    
                    success: function(response) {
                        console.log(response);
                        window.location.reload();

                        // $('.view_user_data').html(response);
                        // $('#viewuser').modal('show');

                    }
                });
            }
            
        })
    });
    //Delete data end
</script>