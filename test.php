<?php
session_start();
include('includes/header.php');
include('includes/navbar.php');
?>

<?php 
  require('dbcon.php');
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
                <h5 class="modal-title" id="insertdataLabel">Add Suppliers</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="code.php" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="name">Supplier Name</label>
                        <input type="text" class="form-control" name="name" placeholder="enter name">
                    </div>

                    <div class="form-group">
                        <label for="email">Supplier Email</label>
                        <input type="text" class="form-control" name="email" placeholder="enter email">
                    </div>

                    <div class="form-group">
                        <label for="phone">Supplier Contact No</label>
                        <input type="text" class="form-control" name="phone" placeholder="enter number">
                    </div>

                    <div class="form-group">
                        <label for="address">Supplier Address</label>
                        <input type="text" class="form-control" name="address" placeholder="enter address">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" name="save_data" class="btn btn-primary">Add Supplier</button>
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
        <h5 class="modal-title" id="viewuserLabel">View Supplier Details</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="view_user_data">

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!--View Modal End-->

<div class="container-fluid  mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Success messge show start -->
            <?php
            if (isset($_SESSION['status']) && $_SESSION['status'] != '') {

            ?>

                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Hey !</strong> <?php echo $_SESSION['status']; ?>
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
                    <h4 class="text-dark fw-bold">MANAGE SUPPLIERS</h4>
                    <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#insertdata">
                        Add Suppliers
                    </button>
                </div>

                <div class="card-body bg-light" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-bordered table-hover" width="100%" p-3>
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone No</th>
                                <th scope="col">Address</th>
                                <th scope="col">View</th>
                                <th scope="col">Edit</th>
                                <th scope="col">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $fetch_query = "SELECT * FROM supplier";
                                $fetch_query_run = mysqli_query($con, $fetch_query);

                                if(mysqli_num_rows($fetch_query_run) > 0){
                                    while($row = mysqli_fetch_array($fetch_query_run)){
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
                                                <a href="" class="btn btn-success btn-sm">Edit</a>
                                            </td>
                                            <td>
                                                <a href="" class="btn btn-danger btn-sm">Delete</a>
                                            </td>
                                        </tr>
                                        <?php
                                    }                                       
                                }

                                else{
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
    $(document).ready(function(){
        $('.view_data').click(function(e){
            e.preventDefault();

            
            var user_id = $(this).closest('tr').find('.user_id').text();
            // console.log(user_id);

            $.ajax({
                method: "POST",
                url: "code.php",
                data: {
                    'click_view_btn': true,
                    'user_id': user_id,
                },
                success: function(response){
                    // console.log(response);

                    $('.view_user_data').html(response);
                    $('#viewuser').modal('show');

                }
            });


        })
    });
</script>