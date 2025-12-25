-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 25, 2025 at 12:15 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `admin_uid` char(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 2,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `admin_uid`, `email`, `password`, `role_id`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, '018c6c80-1b2c-7a8b-9c0d-1e2f3a4b5c6d', 'admin@lifestylestore.com', '$2y$10$5/cDDtStMZKaR/9Ya93LnugG8hGr70t3A8YpV8/eWbXLZosUOxIlq', 1, 1, '2025-12-24 09:38:34', '2025-12-24 09:38:34', '2025-12-24 09:38:34'),
(3, '018c6c80-1b2c-7a8b-9c0d-1e2f3a4b5c6e', 'sales@figureshop.com', '$2y$10$ADvU6NCSWct72Wf9KCe1Kev1I0KUvE0f/ZbVaiLbELy8HoEap1njy', 2, 1, NULL, '2025-12-24 09:49:20', '2025-12-24 09:49:20');

-- --------------------------------------------------------

--
-- Table structure for table `admin_mfa`
--

CREATE TABLE `admin_mfa` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `mfa_type` enum('otp','authenticator') NOT NULL DEFAULT 'otp',
  `secret_key` varchar(255) DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT 0,
  `backup_codes` longtext DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `item_id`, `quantity`, `added_at`, `updated_at`) VALUES
(0, 0, 2, 1, '2025-12-21 18:29:41', '2025-12-21 18:29:41'),
(7, 3, 3, 1, '2025-12-21 15:50:21', '2025-12-21 15:50:21'),
(8, 3, 4, 1, '2025-12-21 15:50:21', '2025-12-21 15:50:21'),
(9, 3, 5, 1, '2025-12-21 15:50:21', '2025-12-21 15:50:21'),
(10, 3, 11, 1, '2025-12-21 15:50:21', '2025-12-21 15:50:21'),
(11, 1, 9, 1, '2025-12-21 15:50:21', '2025-12-21 15:50:21'),
(12, 1, 2, 1, '2025-12-21 15:50:21', '2025-12-21 15:50:21'),
(13, 1, 8, 1, '2025-12-21 15:50:21', '2025-12-21 15:50:21');

-- --------------------------------------------------------

--
-- Table structure for table `failed_login_attempts`
--

CREATE TABLE `failed_login_attempts` (
  `id` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `failed_login_attempts`
--

INSERT INTO `failed_login_attempts` (`id`, `identifier`, `attempt_time`) VALUES
(3, 'perflexity.ai.01@gmail.com', '2025-12-24 10:23:41');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `sku` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_new` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `description`, `price`, `stock_quantity`, `sku`, `category`, `image`, `is_active`, `is_new`, `created_at`, `updated_at`) VALUES
(1, 'Gohan SSJ2 - Dragon Ball', '', 36000000.00, 10, NULL, 'Dragon ball', 'img/products/product_1766343872_694844c07e360.jpg', 1, 0, '2025-12-21 15:50:21', '2025-12-21 19:04:32'),
(2, 'Nico Robin - One Piece', '', 40000000.00, 8, NULL, 'One Piece', 'img/products/product_1766343935_694844ff76579.jpg', 1, 0, '2025-12-21 15:50:21', '2025-12-21 19:06:26'),
(3, 'Sanji - One Piece', '', 5000000.00, 4, NULL, 'One Piece', 'img/products/product_1766343967_6948451f919d8.jpg', 1, 0, '2025-12-21 15:50:21', '2025-12-24 09:39:52'),
(4, 'Rayquaza Mega - Pokemon', '', 8000000.00, 2, NULL, 'Pokemon', 'img/products/product_1766344008_69484548c8c00.jpg', 1, 0, '2025-12-21 15:50:21', '2025-12-24 09:39:52'),
(5, 'Mega Greninja - Pokemon', '', 1300000.00, 15, NULL, 'Pokemon', 'img/mega.jpg', 1, 0, '2025-12-21 15:50:21', '2025-12-21 19:07:04'),
(6, 'Batman: The Animated Series', '', 30000000.00, 19, NULL, 'Batman', 'img/batman.png', 1, 0, '2025-12-21 15:50:21', '2025-12-24 10:26:38'),
(8, 'Dark Magician - Yu Gi Oh!', '', 18000000.00, 7, NULL, 'Yu Gi Oh!', 'img/products/product_1766344057_6948457916b17.jpg', 1, 0, '2025-12-21 15:50:21', '2025-12-21 19:07:37'),
(9, 'Yuta - Jujutsu Kaisen', '', 1500000.00, 19, NULL, 'Jujutsu Kaisen', 'img/products/product_1766344152_694845d889dd9.jpg', 1, 0, '2025-12-21 15:50:21', '2025-12-24 10:36:30'),
(10, 'Miyagi - Infinity Studio', '', 1000000.00, 29, NULL, 'Infinity Studio', 'img/products/product_1766344212_69484614d2d39.jpg', 1, 0, '2025-12-21 15:50:21', '2025-12-24 10:24:05'),
(11, 'Naruto Samurai - Naruto', '', 9000000.00, 18, NULL, 'Naruto', 'img/products/product_1766344259_694846438e9e1.jpg', 1, 1, '2025-12-21 15:50:21', '2025-12-21 19:10:59'),
(12, 'Obito - Naruto', '', 99999999.99, 12, NULL, 'Naruto', 'img/products/product_1766344323_694846831f523.jpg', 1, 0, '2025-12-21 15:50:21', '2025-12-24 10:34:25');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_uid` char(36) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(18,2) NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT 2,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_uid`, `user_id`, `total_amount`, `status_id`, `notes`, `created_at`, `updated_at`) VALUES
(1, '700646af-7134-695f-9174-e15057c39635', 4, 13000000.00, 1, NULL, '2025-12-24 09:39:52', '2025-12-24 09:39:52'),
(2, '700646af-7ae8-8c33-b808-424f9db5e1ac', 4, 1500000.00, 1, NULL, '2025-12-24 09:42:35', '2025-12-24 09:42:35'),
(4, '700646b0-186e-5fa7-afcb-581a269793fc', 5, 30000000.00, 1, NULL, '2025-12-24 10:26:38', '2025-12-24 10:26:38'),
(6, '700646b0-3447-ea8a-9b99-7b072ad1604b', 5, 399999999.96, 1, NULL, '2025-12-24 10:34:25', '2025-12-24 10:34:25'),
(7, '700646b0-3bb6-bfa3-b1f5-12f70de2b738', 5, 7500000.00, 1, NULL, '2025-12-24 10:36:30', '2025-12-24 10:36:30');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(18,2) NOT NULL,
  `subtotal` decimal(18,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_id`, `quantity`, `unit_price`, `subtotal`, `created_at`) VALUES
(1, 1, 4, 1, 8000000.00, 8000000.00, '2025-12-24 09:39:52'),
(2, 1, 3, 1, 5000000.00, 5000000.00, '2025-12-24 09:39:52'),
(3, 2, 9, 1, 1500000.00, 1500000.00, '2025-12-24 09:42:35'),
(6, 4, 6, 1, 30000000.00, 30000000.00, '2025-12-24 10:26:38'),
(8, 6, 12, 4, 99999999.99, 399999999.96, '2025-12-24 10:34:25'),
(9, 7, 9, 5, 1500000.00, 7500000.00, '2025-12-24 10:36:30');

-- --------------------------------------------------------

--
-- Table structure for table `order_statuses`
--

CREATE TABLE `order_statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `order_statuses`
--

INSERT INTO `order_statuses` (`id`, `name`, `description`, `color`, `is_active`, `created_at`) VALUES
(1, 'pending', 'Item added to cart, awaiting checkout', 'warning', 1, '2025-12-21 15:50:21'),
(2, 'confirmed', 'Order has been confirmed and payment received', 'info', 1, '2025-12-21 15:50:21'),
(3, 'processing', 'Order is being prepared for shipment', 'primary', 1, '2025-12-21 15:50:21'),
(4, 'shipped', 'Order has been dispatched and is in transit', 'success', 1, '2025-12-21 15:50:21'),
(5, 'delivered', 'Order has been successfully delivered', 'success', 1, '2025-12-21 15:50:21'),
(6, 'cancelled', 'Order has been cancelled by customer or admin', 'danger', 1, '2025-12-21 15:50:21'),
(7, 'returned', 'Order has been returned by customer', 'secondary', 1, '2025-12-21 15:50:21');

-- --------------------------------------------------------

--
-- Table structure for table `password_history`
--

CREATE TABLE `password_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `password_history`
--

INSERT INTO `password_history` (`id`, `user_id`, `password_hash`, `created_at`) VALUES
(1, 0, '0fcde8aea1314a1b9eb4b47842779e1e', '2025-12-21 19:27:26'),
(2, 0, 'ed9b393f3bda3baf59079187cb2ada6f', '2025-12-21 19:29:04'),
(3, 4, '$2y$10$RApCg3/JPCGcAOXdgvEL.Oj/0nSy2Q3BCQpKrUwi9Qk/tmlU1Xvei', '2025-12-24 09:41:59');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otp`
--

CREATE TABLE `password_reset_otp` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `password_reset_otp`
--

INSERT INTO `password_reset_otp` (`id`, `user_id`, `email`, `otp`, `created_at`, `expires_at`, `is_used`, `used_at`) VALUES
(1, 4, 'perflexity.ai.01@gmail.com', 'faa964de40788d8b8cae8f6459559e2a2d317e43d332710e89ddc22f9c589bec', '2025-12-24 09:41:34', '2025-12-24 09:51:34', 1, '2025-12-24 09:41:50');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'admin', 'Full administrative access - can delete users, orders, and manage staff', '2025-12-21 15:50:20'),
(2, 'sales_manager', 'Sales management - can manage products and view orders, but cannot delete users or manage staff', '2025-12-21 15:50:20'),
(3, 'customer', 'Regular customer - can browse, search products and make purchases', '2025-12-21 15:50:20');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `session_type` varchar(20) NOT NULL DEFAULT 'customer',
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logged_out_time` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `session_id`, `user_id`, `user_email`, `role_id`, `ip_address`, `user_agent`, `session_type`, `login_time`, `last_activity`, `logged_out_time`, `is_active`) VALUES
(1, 'jfk48ljritm3m26jm3vs9nta4v', 4, '0', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'customer', '2025-12-24 09:39:42', '2025-12-24 10:05:22', '2025-12-24 10:05:22', 0),
(3, 'anl73jp0nua39q9pm11c6s55er', 5, '0', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'customer', '2025-12-24 10:23:14', '2025-12-24 10:23:17', '2025-12-24 10:23:17', 0),
(4, 'l63bqas3of2db0hrm379fnjibt', 4, '0', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'customer', '2025-12-24 10:23:25', '2025-12-24 10:23:32', '2025-12-24 10:23:32', 0),
(5, '3u7au77ebtf8kvmvgb6rp6bebe', 5, '0', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'customer', '2025-12-24 10:23:49', '2025-12-24 10:23:49', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `session_audit_log`
--

CREATE TABLE `session_audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `session_audit_log`
--

INSERT INTO `session_audit_log` (`id`, `user_id`, `action`, `details`, `ip_address`, `timestamp`) VALUES
(2, 4, 'customer_login', 'Successful login', '::1', '2025-12-24 09:39:42'),
(7, 4, 'customer_logout', 'User logged out', '::1', '2025-12-24 10:05:22'),
(8, 5, 'customer_login', 'Successful login', '::1', '2025-12-24 10:23:14'),
(9, 5, 'customer_logout', 'User logged out', '::1', '2025-12-24 10:23:17'),
(10, 4, 'customer_login', 'Successful login', '::1', '2025-12-24 10:23:25'),
(11, 4, 'customer_logout', 'User logged out', '::1', '2025-12-24 10:23:32'),
(12, 5, 'customer_login', 'Successful login', '::1', '2025-12-24 10:23:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_uid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `city` varchar(100) NOT NULL,
  `address` varchar(500) NOT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_uid`, `name`, `email`, `password`, `contact`, `city`, `address`, `email_verified`, `verification_token`, `token_expiry`, `is_active`, `created_at`, `updated_at`) VALUES
(4, '018c6c80-1b2c-7a8b-9c0d-1e2f3a4b5c6f', 'HO VAN TAM', 'perflexity.ai.01@gmail.com', '$2y$10$BYiI911KoYHnatNgaFAQG.7vQ///plVXQH9bdpGWgPZSHlihEJmhW', '0999999999', 'Ho Chi Minh', '1074/2/7 Quang Trung', 1, NULL, NULL, 1, '2025-12-24 09:39:22', '2025-12-24 09:41:59'),
(5, '700646b0-0a3b-3f1f-a8ed-3849f7c0b50a', 'aazcvxzxzz', 'ztxchou@gmail.com', '$2y$10$IAqV8XfzG3zPKEyOtEk5feOenVm4YgmP0AHIeapAMHpl1JMVvLLvm', '0999999999', 'Ho Chi Minh', '1074/2/7 Quang Trung', 1, NULL, NULL, 1, '2025-12-24 10:22:46', '2025-12-24 10:23:05');

-- --------------------------------------------------------

--
-- Table structure for table `users_items`
--

CREATE TABLE `users_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Added to cart',
  `quantity` int(11) DEFAULT 1,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `admin_uid` (`admin_uid`);

--
-- Indexes for table `admin_mfa`
--
ALTER TABLE `admin_mfa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier` (`identifier`),
  ADD KEY `idx_time` (`attempt_time`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_uid` (`order_uid`),
  ADD UNIQUE KEY `idx_orders_order_uid` (`order_uid`),
  ADD KEY `fk_orders_user_id` (`user_id`),
  ADD KEY `fk_orders_status_id` (`status_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_order_id` (`order_id`),
  ADD KEY `fk_order_items_item_id` (`item_id`);

--
-- Indexes for table `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `password_history`
--
ALTER TABLE `password_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_login_time` (`login_time`),
  ADD KEY `fk_sessions_role_id` (`role_id`);

--
-- Indexes for table `session_audit_log`
--
ALTER TABLE `session_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_uid` (`user_uid`);

--
-- Indexes for table `users_items`
--
ALTER TABLE `users_items`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_mfa`
--
ALTER TABLE `admin_mfa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_statuses`
--
ALTER TABLE `order_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `password_history`
--
ALTER TABLE `password_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `session_audit_log`
--
ALTER TABLE `session_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users_items`
--
ALTER TABLE `users_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_mfa`
--
ALTER TABLE `admin_mfa`
  ADD CONSTRAINT `admin_mfa_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_status_id` FOREIGN KEY (`status_id`) REFERENCES `order_statuses` (`id`),
  ADD CONSTRAINT `fk_orders_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_item_id` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `fk_order_items_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `fk_sessions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `session_audit_log`
--
ALTER TABLE `session_audit_log`
  ADD CONSTRAINT `fk_audit_log_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
