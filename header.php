<nav class="navbar navbar-inverse navabar-fixed-top">
               <div class="container">
                   <div class="navbar-header">
                       <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                           <span class="icon-bar"></span>
                           <span class="icon-bar"></span>
                           <span class="icon-bar"></span>
                       </button>
                       <a href="index.php" class="navbar-brand">
                           <img src="img/avatar.png" alt="Figure Shop" class="logo-img">
                           <span class="logo-text">Figure Shop</span>
                       </a>
                   </div>
                   
                   <div class="collapse navbar-collapse" id="myNavbar">
                       <!-- Main Navigation - 3 Categories -->
                       <ul class="nav navbar-nav">
                           <?php
                           $current_category = isset($_GET['category']) ? $_GET['category'] : '';
                           $current_page = basename($_SERVER['PHP_SELF']);
                           ?>
                           <li class="<?php echo ($current_category == 'new' || ($current_page == 'index.php' && $current_category == '')) ? 'active' : ''; ?>">
                               <a href="products.php?category=new">Hàng Mới Về</a>
                           </li>
                           <li class="<?php echo ($current_category == 'bestseller') ? 'active' : ''; ?>">
                               <a href="products.php?category=bestseller">Best Seller</a>
                           </li>
                           <li class="<?php echo ($current_category == '' && $current_page == 'products.php') ? 'active' : ''; ?>">
                               <a href="products.php">Tất Cả Sản Phẩm</a>
                           </li>
                       </ul>
                       
                       <!-- Search Bar -->
                       <form class="navbar-form navbar-left" method="POST" action="search.php" style="margin-left: 20px;">
                           <div class="input-group">
                               <input type="text" 
                                      class="form-control" 
                                      placeholder="Tìm kiếm mô hình..." 
                                      name="search"
                                      maxlength="255"
                                      autocomplete="off">
                               <span class="input-group-btn">
                                   <button class="btn btn-warning" type="submit" style="background-color: #FFD700; border-color: #FFA500;">
                                       <span class="glyphicon glyphicon-search"></span>
                                   </button>
                               </span>
                           </div>
                       </form>
                       
                       <ul class="nav navbar-nav navbar-right">
                           <?php
                           if(isset($_SESSION['email'])){
                           ?>
                           <li><a href="cart.php"><span class="glyphicon glyphicon-shopping-cart"></span> Giỏ Hàng</a></li>
                           <li><a href="settings.php"><span class="glyphicon glyphicon-cog"></span> Cài Đặt</a></li>
                           <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Đăng Xuất</a></li>
                           <?php
                           }else{
                            ?>
                            <li><a href="signup.php"><span class="glyphicon glyphicon-user"></span> Đăng Ký</a></li>
                           <li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Đăng Nhập</a></li>
                           <?php
                           }
                           ?>
                           
                       </ul>
                   </div>
               </div>
</nav>