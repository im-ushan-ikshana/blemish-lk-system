  
   <!-- Sidebar -->
   <ul class="navbar-nav  sidebar sidebar-dark accordion" id="accordionSidebar" style="background-color: #8d4741;">


     <!-- Sidebar - Brand -->
     <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
       <div class="sidebar-brand-icon rotate-n-15">
         <i class="fa-solid fa-shirt"></i>
         <!-- <img src="../img/logo.jpeg" alt=""> -->
       </div>
       <div class="sidebar-brand-text mx-3">Blemish lk</div>
     </a>

     <!-- Divider -->
     <hr class="sidebar-divider my-0">

     <!-- Nav Item - Dashboard -->
     <li class="nav-item active">
       <a class="nav-link" href="index.php">
         <i class="fas fa-fw fa-tachometer-alt"></i>
         <span>Dashboard</span></a>
     </li>

     <hr class="sidebar-divider my-0">

     <li class="nav-item">
       <a class="nav-link" href="supplier.php">
         <i class="fa-solid fa-user-tie"></i>
         <span>Suppliers</span></a>
     </li>

     <hr class="sidebar-divider my-0">



     <li class="nav-item">
       <a class="nav-link" href="product.php">
       <i class="fa-solid fa-truck-fast"></i>
         <span>Products</span></a>
     </li>

     <hr class="sidebar-divider my-0">

     <li class="nav-item">
       <a class="nav-link" href="orders-new.php">
       <i class="fa-solid fa-pen-to-square"></i>
         <span>Orders</span></a>

     </li>

     <hr class="sidebar-divider my-0">

     <li class="nav-item">
       <a class="nav-link" href="sales.php">
       <i class="fa-solid fa-arrow-trend-up"></i>
         <span>Sales</span></a>

     </li>

     <hr class="sidebar-divider my-0">

     <li class="nav-item">
       <a class="nav-link" href="inventory.php">
         <i class="fas fa-fw fa-chart-area"></i>
         <span>Inventory</span></a>

     </li>

     <hr class="sidebar-divider my-0">




     <!-- Nav Item - Pages Collapse Menu -->
     <li class="nav-item">
       <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
         <i class="fas fa-fw fa-cog"></i>
         <span>ADMIN</span>
       </a>
       <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
         <div class="bg-white py-2 collapse-inner rounded">
           <h6 class="collapse-header">Admin Controls:</h6>
           <a class="collapse-item" href="category.php">Product Category</a>
           <a class="collapse-item" href="users.php">Manage Users</a>
           <a class="collapse-item" href="orders.php">Edit Orders</a>
         </div>
       </div>
     </li>







     <!-- Divider -->
     <hr class="sidebar-divider d-none d-md-block">

     <!-- Sidebar Toggler (Sidebar) -->
     <div class="text-center d-none d-md-inline">
       <button class="rounded-circle border-0" id="sidebarToggle"></button>
     </div>

   </ul>
   <!-- End of Sidebar -->

   <!-- Content Wrapper -->
   <div id="content-wrapper" class="d-flex flex-column">

     <!-- Main Content -->
     <div id="content">

       <!-- Topbar -->
       <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

         <!-- Sidebar Toggle (Topbar) -->
         <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
           <i class="fa fa-bars" style="color: #8d4741;"></i>

         </button>

         <!-- Topbar Search -->
         <!-- <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
           <div class="input-group">
             <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
             <div class="input-group-append">
               <button class="btn btn-primary" type="button" style="background-color: #8c4640; border-color: #8d4741;">
                 <i class="fas fa-search fa-sm"></i>
               </button>
             </div>
           </div>
         </form> -->
         <h5 class="ml-3 mt-2 p-2 d-none d-md-block" style="color: #8d4741;">Blemish: Where Perfection Meets Style...</h5>
         <!-- Topbar Navbar -->       <ul class="navbar-nav ml-auto">

           <!-- Nav Item - Search Dropdown (Visible Only XS) -->
           <li class="nav-item dropdown no-arrow d-sm-none">
             <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <i class="fas fa-search fa-fw"></i>
             </a>
             <!-- Dropdown - Messages -->
             <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
               <form class="form-inline mr-auto w-100 navbar-search">
                 <div class="input-group">
                   <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                   <div class="input-group-append">
                     <button class="btn btn-primary" type="button" style="background-color: #8c4640; border-color: #8d4741;">
                       <i class="fas fa-search fa-sm"></i>
                     </button>
                   </div>
                 </div>
               </form>
             </div>
           </li>

           
           <!-- Nav Item - User Information -->
           <li class="nav-item dropdown no-arrow">
             <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <span class="mr-2 d-none d-lg-inline text-gray-600 small">
               
  
               <?php 
                // session_start();
                $usernamefromlogin = $_SESSION['username'];
                 
              ?>
              <h6 class="mt-1"><?php echo $usernamefromlogin; ?></h6>

               </span>
               <i class="fa-solid fa-user ml-2" style="color: #8c4640;"></i>      </a>
             <!-- Dropdown - User Information -->
             <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
               <div class="dropdown-divider"></div>
               <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                 <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                 Logout
               </a>
             </div>
           </li>

         </ul>

       </nav>
       <!-- End of Topbar -->


       <!-- Scroll to Top Button-->
       <a class="scroll-to-top rounded" href="#page-top">
         <i class="fas fa-angle-up"></i>
       </a>


       <!-- Logout Modal-->
       <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog" role="document">
           <div class="modal-content">
             <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
               <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                 <span aria-hidden="true">×</span>
               </button>
             </div>
             <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
             <div class="modal-footer">
               <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>

               <form action="logout.php" method="POST">

                 <button type="submit" name="logout_btn" class="btn btn-primary">Logout</button>

               </form>


             </div>
           </div>
         </div>
       </div>