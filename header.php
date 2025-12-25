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
                       
                       <!-- Search Bar with Live Search -->
                       <form class="navbar-form navbar-left" method="POST" action="search.php" style="margin-left: 20px; position: relative;">
                           <div class="input-group" style="position: relative;">
                               <input type="text" 
                                      class="form-control live-search-input" 
                                      id="headerSearchInput"
                                      placeholder="Tìm kiếm mô hình..." 
                                      name="search"
                                      maxlength="255"
                                      autocomplete="off">
                               <span class="input-group-btn">
                                   <button class="btn btn-warning" type="submit" style="background-color: #FFD700; border-color: #FFA500;">
                                       <span class="glyphicon glyphicon-search"></span>
                                   </button>
                               </span>
                               <div id="headerSearchResults" class="live-search-results"></div>
                           </div>
                       </form>
                       
                       <ul class="nav navbar-nav navbar-right">
                           <?php
                           if(isset($_SESSION['email'])){
                               // Count number of different items in cart
                               $cart_count = 0;
                               if(isset($_SESSION['id'])){
                                   require 'connection.php';
                                   $user_id = $_SESSION['id'];
                                   $cart_count_query = "SELECT COUNT(DISTINCT item_id) as item_count FROM cart_items WHERE user_id = ?";
                                   $cart_stmt = mysqli_prepare($con, $cart_count_query);
                                   if($cart_stmt){
                                       mysqli_stmt_bind_param($cart_stmt, "i", $user_id);
                                       mysqli_stmt_execute($cart_stmt);
                                       $cart_count_result = mysqli_stmt_get_result($cart_stmt);
                                       if($row = mysqli_fetch_array($cart_count_result)){
                                           $cart_count = intval($row['item_count']);
                                       }
                                       mysqli_stmt_close($cart_stmt);
                                   }
                               }
                           ?>
                           <li>
                               <a href="cart.php" class="cart-link">
                                   <span class="glyphicon glyphicon-shopping-cart cart-icon-wrapper">
                                       <?php if($cart_count > 0): ?>
                                           <span class="cart-badge"><?php echo $cart_count; ?></span>
                                       <?php endif; ?>
                                   </span> Giỏ Hàng
                               </a>
                           </li>
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

<script type="text/javascript">
(function() {
    var searchInput = document.getElementById('headerSearchInput');
    var resultsDiv = document.getElementById('headerSearchResults');
    var searchTimeout;
    
    // XSS protection: validate search input before submission
    function isXssPayload(value) {
        const xssPatterns = [
            /<script[^>]*>.*?<\/script>/gi,           // Script tags
            /<iframe[^>]*>.*?<\/iframe>/gi,           // iFrame tags
            /on\w+\s*=/gi,                             // Event handlers
            /javascript:/gi,                           // javascript: protocol
            /data:text\/html/gi,                       // data:text/html protocol
            /vbscript:/gi,                             // vbscript: protocol
            /<svg[^>]*on\w+/gi,                        // SVG with event handlers
            /<img[^>]*on\w+/gi,                        // img with event handlers
            /<body[^>]*on\w+/gi                        // body with event handlers
        ];
        
        for (let pattern of xssPatterns) {
            if (pattern.test(value)) {
                return true; // XSS payload detected
            }
        }
        return false; // Safe
    }
    
    if (searchInput && resultsDiv) {
        searchInput.addEventListener('input', function() {
            var query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            // Check for XSS payload
            if (isXssPayload(query)) {
                resultsDiv.innerHTML = '<div class="live-search-item no-results">Lỗi: Input chứa nội dung không hợp lệ!</div>';
                resultsDiv.style.display = 'block';
                return;
            }
            
            if (query.length < 2) {
                resultsDiv.innerHTML = '';
                resultsDiv.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(function() {
                // AJAX request
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'ajax_search.php?q=' + encodeURIComponent(query), true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            var results = JSON.parse(xhr.responseText);
                            displaySearchResults(results, resultsDiv, query);
                        } catch (e) {
                            resultsDiv.innerHTML = '';
                            resultsDiv.style.display = 'none';
                        }
                    }
                };
                xhr.send();
            }, 300);
        });
        
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            var target = e.target;
            if (target !== searchInput && !resultsDiv.contains(target) && !searchInput.parentElement.contains(target)) {
                resultsDiv.style.display = 'none';
            }
        });
        
        // Hide results when pressing Escape
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                resultsDiv.style.display = 'none';
            }
        });
        
        // Validate form submission
        var form = searchInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (isXssPayload(searchInput.value)) {
                    e.preventDefault();
                    alert('Lỗi: Input chứa nội dung không hợp lệ!');
                    searchInput.focus();
                    return false;
                }
            });
        }
    }
    
    function displaySearchResults(results, container, query) {
        if (results.length === 0) {
            container.innerHTML = '<div class="live-search-item no-results">Không tìm thấy sản phẩm</div>';
            container.style.display = 'block';
            return;
        }
        
        var html = '';
        results.forEach(function(item) {
            html += '<a href="product.php?id=' + item.id + '" class="live-search-item">';
            html += '<div class="live-search-item-name">' + highlightText(item.name, query) + '</div>';
            html += '<div class="live-search-item-info">';
            html += '<span class="live-search-item-price">' + item.price + ' VNĐ</span>';
            if (item.category) {
                html += '<span class="live-search-item-category">' + item.category + '</span>';
            }
            html += '</div>';
            html += '</a>';
        });
        
        container.innerHTML = html;
        container.style.display = 'block';
    }
    
    function highlightText(text, query) {
        if (!query) return text;
        var regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }
})();
</script>