
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for table `roles`
-- Stores admin/staff roles with permissions
-- --------------------------------------------------------

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL UNIQUE,
  `description` varchar(255),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Full administrative access - can delete users, orders, and manage staff'),
(2, 'sales_manager', 'Sales management - can manage products and view orders, but cannot delete users or manage staff'),
(3, 'customer', 'Regular customer - can browse, search products and make purchases');

-- --------------------------------------------------------
-- Table structure for table `order_statuses`
-- Defines all possible order statuses
-- --------------------------------------------------------

CREATE TABLE `order_statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL UNIQUE,
  `description` varchar(255),
  `color` varchar(20),
  `is_active` boolean DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `order_statuses` (`id`, `name`, `description`, `color`, `is_active`) VALUES
(1, 'pending', 'Item added to cart, awaiting checkout', 'warning', 1),
(2, 'confirmed', 'Order has been confirmed and payment received', 'info', 1),
(3, 'processing', 'Order is being prepared for shipment', 'primary', 1),
(4, 'shipped', 'Order has been dispatched and is in transit', 'success', 1),
(5, 'delivered', 'Order has been successfully delivered', 'success', 1),
(6, 'cancelled', 'Order has been cancelled by customer or admin', 'danger', 1),
(7, 'returned', 'Order has been returned by customer', 'secondary', 1);

-- --------------------------------------------------------
-- Table structure for table `items`
-- Product catalog with enhanced fields following ecommerce best practices
-- --------------------------------------------------------

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10, 2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `sku` varchar(50) UNIQUE,
  `category` varchar(100),
  `is_active` boolean DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `items` (`id`, `name`, `price`, `stock_quantity`, `category`, `is_active`) VALUES
(1, 'Cannon EOS', 36000.00, 10, 'Cameras', 1),
(2, 'Sony DSLR', 40000.00, 8, 'Cameras', 1),
(3, 'Sony DSLR', 50000.00, 5, 'Cameras', 1),
(4, 'Olympus DSLR', 80000.00, 3, 'Cameras', 1),
(5, 'Titan Model #301', 13000.00, 15, 'Watches', 1),
(6, 'Titan Model #201', 3000.00, 20, 'Watches', 1),
(7, 'HMT Milan', 8000.00, 12, 'Watches', 1),
(8, 'Favre Lueba #111', 18000.00, 7, 'Watches', 1),
(9, 'Raymond', 1500.00, 25, 'Shirts', 1),
(10, 'Charles', 1000.00, 30, 'Shirts', 1),
(11, 'HXR', 900.00, 18, 'Shirts', 1),
(12, 'PINK', 1200.00, 22, 'Shirts', 1);

-- --------------------------------------------------------
-- Table structure for table `users`
-- Customer accounts with enhanced security and audit fields
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `city` varchar(100) NOT NULL,
  `address` varchar(500) NOT NULL,
  `email_verified` boolean DEFAULT 0,
  `verification_token` varchar(255),
  `token_expiry` datetime,
  `is_active` boolean DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `users` (`id`, `name`, `email`, `password`, `contact`, `city`, `address`, `is_active`) VALUES
(1, 'Sajal', 'sajal.agrawal1997@gmail.com', '57f231b1ec41dc6641270cb09a56f897', '8899889988', 'Indore', '100 palace colony, Indore', 1),
(2, 'Ram', 'ram1234@xyz.com', '57f231b1ec41dc6641270cb09a56f897', '8899889989', 'Pune', '100 palace colony, Pune', 1),
(3, 'Shyam', 'shyam@xyz.com', '57f231b1ec41dc6641270cb09a56f897', '8899889990', 'Bangalore', '100 palace colony, Bangalore', 1);

-- --------------------------------------------------------
-- Table structure for table `orders`
-- Order transactions (replaces users_items for confirmed orders)
-- --------------------------------------------------------

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10, 2) NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT 2,
  `notes` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
-- Table structure for table `order_items`
-- Individual items in each order with quantity and pricing
-- --------------------------------------------------------

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10, 2) NOT NULL,
  `subtotal` decimal(10, 2) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
-- Table structure for table `cart_items`
-- Shopping cart items (pending orders)
-- --------------------------------------------------------

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `cart_items` (`id`, `user_id`, `item_id`, `quantity`) VALUES
(7, 3, 3, 1),
(8, 3, 4, 1),
(9, 3, 5, 1),
(10, 3, 11, 1),
(11, 1, 9, 1),
(12, 1, 2, 1),
(13, 1, 8, 1);

-- --------------------------------------------------------
-- Legacy table for backward compatibility
-- Maps to new cart_items table
-- --------------------------------------------------------

CREATE TABLE `users_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Added to cart',
  `quantity` int(11) DEFAULT 1,
  `order_date` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `users_items` (`id`, `user_id`, `item_id`, `status`, `quantity`) VALUES
(7, 3, 3, 'Added to cart', 1),
(8, 3, 4, 'Added to cart', 1),
(9, 3, 5, 'Added to cart', 1),
(10, 3, 11, 'Added to cart', 1),
(11, 1, 9, 'Added to cart', 1),
(12, 1, 2, 'Added to cart', 1),
(13, 1, 8, 'Added to cart', 1);

-- --------------------------------------------------------
-- Table structure for table `admins`
-- Staff/Admin accounts with role-based access
-- --------------------------------------------------------

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 2,
  `is_active` boolean DEFAULT 1,
  `last_login` datetime,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `admins` (`id`, `email`, `password`, `role_id`, `is_active`) VALUES
(1, 'admin@lifestylestore.com', '57f231b1ec41dc6641270cb09a56f897', 1, 1),
(2, 'sales@lifestylestore.com', '57f231b1ec41dc6641270cb09a56f897', 2, 1);

-- --------------------------------------------------------
-- Indexes for tables
-- --------------------------------------------------------

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_item` (`user_id`, `item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `users_items`
--
ALTER TABLE `users_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`, `item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `is_active` (`is_active`);

-- --------------------------------------------------------
-- AUTO_INCREMENT for tables
-- --------------------------------------------------------

ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `order_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

ALTER TABLE `users_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

-- --------------------------------------------------------
-- Foreign Key Constraints
-- --------------------------------------------------------

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `order_statuses` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users_items` (Legacy)
--
ALTER TABLE `users_items`
  ADD CONSTRAINT `users_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `users_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;