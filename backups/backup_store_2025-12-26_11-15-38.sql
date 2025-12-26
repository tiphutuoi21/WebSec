-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: store
-- ------------------------------------------------------
-- Server version	8.0.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_mfa`
--

DROP TABLE IF EXISTS `admin_mfa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_mfa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `mfa_type` enum('otp','authenticator') NOT NULL DEFAULT 'otp',
  `secret_key` varchar(255) DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT '0',
  `backup_codes` longtext,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `admin_mfa_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_mfa`
--

LOCK TABLES `admin_mfa` WRITE;
/*!40000 ALTER TABLE `admin_mfa` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_mfa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_uid` char(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int NOT NULL DEFAULT '2',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `admin_uid` (`admin_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'018c6c80-1b2c-7a8b-9c0d-1e2f3a4b5c6d','admin@lifestylestore.com','$2y$10$5/cDDtStMZKaR/9Ya93LnugG8hGr70t3A8YpV8/eWbXLZosUOxIlq',1,1,'2025-12-24 09:38:34','2025-12-24 09:38:34','2025-12-24 09:38:34'),(3,'018c6c80-1b2c-7a8b-9c0d-1e2f3a4b5c6e','sales@figureshop.com','$2y$10$ADvU6NCSWct72Wf9KCe1Kev1I0KUvE0f/ZbVaiLbELy8HoEap1njy',2,1,NULL,'2025-12-24 09:49:20','2025-12-24 09:49:20');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `buffer_overflow_logs`
--

DROP TABLE IF EXISTS `buffer_overflow_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `buffer_overflow_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `field_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `input_length` int NOT NULL,
  `max_allowed` int NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `user_id` int DEFAULT NULL,
  `session_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_uri` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buffer_overflow_logs`
--

LOCK TABLES `buffer_overflow_logs` WRITE;
/*!40000 ALTER TABLE `buffer_overflow_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `buffer_overflow_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
INSERT INTO `cart_items` VALUES (0,0,2,1,'2025-12-21 18:29:41','2025-12-21 18:29:41'),(7,3,3,1,'2025-12-21 15:50:21','2025-12-21 15:50:21'),(8,3,4,1,'2025-12-21 15:50:21','2025-12-21 15:50:21'),(9,3,5,1,'2025-12-21 15:50:21','2025-12-21 15:50:21'),(10,3,11,1,'2025-12-21 15:50:21','2025-12-21 15:50:21'),(11,1,9,1,'2025-12-21 15:50:21','2025-12-21 15:50:21'),(12,1,2,1,'2025-12-21 15:50:21','2025-12-21 15:50:21'),(13,1,8,1,'2025-12-21 15:50:21','2025-12-21 15:50:21');
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `db_migrations`
--

DROP TABLE IF EXISTS `db_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_migrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `applied_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checksum` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`version`),
  KEY `idx_version` (`version`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `db_migrations`
--

LOCK TABLES `db_migrations` WRITE;
/*!40000 ALTER TABLE `db_migrations` DISABLE KEYS */;
INSERT INTO `db_migrations` VALUES (1,'1.0.0','Initial security tables','2025-12-26 03:46:07','testuser@example.com','add1b763ef8e0e8c12dc0ddb2f1b15d07619e7eaab93c09eac5ac89682c949be'),(2,'1.0.1','Add encrypted data support','2025-12-26 03:46:07','testuser@example.com','aaa50490de546d4bc574250221b14256b22d5fa43eb3df5a7c0cfad568d9b47a'),(3,'1.0.2','Add rate limiting table','2025-12-26 03:46:08','testuser@example.com','c2db36679ab43588e1c13dcb8d5c989d662e1fe99d3125d38de4b9d26337a2ca');
/*!40000 ALTER TABLE `db_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `encrypted_data`
--

DROP TABLE IF EXISTS `encrypted_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `encrypted_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` int NOT NULL,
  `encrypted_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `iv` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_data_type` (`data_type`),
  KEY `idx_reference_id` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `encrypted_data`
--

LOCK TABLES `encrypted_data` WRITE;
/*!40000 ALTER TABLE `encrypted_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `encrypted_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_login_attempts`
--

DROP TABLE IF EXISTS `failed_login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_identifier` (`identifier`),
  KEY `idx_time` (`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_login_attempts`
--

LOCK TABLES `failed_login_attempts` WRITE;
/*!40000 ALTER TABLE `failed_login_attempts` DISABLE KEYS */;
INSERT INTO `failed_login_attempts` VALUES (3,'perflexity.ai.01@gmail.com','2025-12-24 10:23:41'),(4,'n22dcat014@student.ptithcm.edu.vn','2025-12-26 03:19:53'),(5,'n22dcat014@student.ptithcm.edu.vn','2025-12-26 03:20:08');
/*!40000 ALTER TABLE `failed_login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `input_validation_rules`
--

DROP TABLE IF EXISTS `input_validation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `input_validation_rules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `field_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_length` int NOT NULL,
  `min_length` int DEFAULT '0',
  `regex_pattern` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_type` enum('string','integer','email','phone','url','decimal') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `is_active` tinyint(1) DEFAULT '1',
  `error_message_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error_message_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `field_name` (`field_name`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `input_validation_rules`
--

LOCK TABLES `input_validation_rules` WRITE;
/*!40000 ALTER TABLE `input_validation_rules` DISABLE KEYS */;
INSERT INTO `input_validation_rules` VALUES (1,'name',100,2,NULL,'string',1,'Tên không hợp lệ (2-100 ký tự)','Invalid name (2-100 characters)','2025-12-26 09:24:44','2025-12-26 09:24:44'),(2,'email',255,5,NULL,'email',1,'Email không hợp lệ','Invalid email format','2025-12-26 09:24:44','2025-12-26 09:24:44'),(3,'password',128,8,NULL,'string',1,'Mật khẩu phải từ 8-128 ký tự','Password must be 8-128 characters','2025-12-26 09:24:44','2025-12-26 09:24:44'),(4,'phone',20,10,NULL,'phone',1,'Số điện thoại không hợp lệ','Invalid phone number','2025-12-26 09:24:44','2025-12-26 09:24:44'),(5,'contact',20,10,NULL,'phone',1,'Số điện thoại không hợp lệ','Invalid phone number','2025-12-26 09:24:44','2025-12-26 09:24:44'),(6,'address',500,5,NULL,'string',1,'Địa chỉ từ 5-500 ký tự','Address must be 5-500 characters','2025-12-26 09:24:44','2025-12-26 09:24:44'),(7,'city',100,2,NULL,'string',1,'Tên thành phố không hợp lệ','Invalid city name','2025-12-26 09:24:44','2025-12-26 09:24:44'),(8,'product_name',200,2,NULL,'string',1,'Tên sản phẩm từ 2-200 ký tự','Product name must be 2-200 characters','2025-12-26 09:24:44','2025-12-26 09:24:44'),(9,'description',2000,0,NULL,'string',1,'Mô tả tối đa 2000 ký tự','Description max 2000 characters','2025-12-26 09:24:44','2025-12-26 09:24:44'),(10,'search_query',255,1,NULL,'string',1,'Tìm kiếm từ 1-255 ký tự','Search query must be 1-255 characters','2025-12-26 09:24:44','2025-12-26 09:24:44'),(11,'price',15,1,NULL,'decimal',1,'Giá không hợp lệ','Invalid price format','2025-12-26 09:24:44','2025-12-26 09:24:44'),(12,'quantity',10,1,NULL,'integer',1,'Số lượng phải là số nguyên dương','Quantity must be positive integer','2025-12-26 09:24:44','2025-12-26 09:24:44');
/*!40000 ALTER TABLE `input_validation_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_blacklist`
--

DROP TABLE IF EXISTS `ip_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip_blacklist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `violation_count` int DEFAULT '1',
  `is_permanent` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `blocked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `unblocked_at` timestamp NULL DEFAULT NULL,
  `unblocked_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_blacklist`
--

LOCK TABLES `ip_blacklist` WRITE;
/*!40000 ALTER TABLE `ip_blacklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_whitelist`
--

DROP TABLE IF EXISTS `ip_whitelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip_whitelist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_by` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_whitelist`
--

LOCK TABLES `ip_whitelist` WRITE;
/*!40000 ALTER TABLE `ip_whitelist` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_whitelist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int DEFAULT '0',
  `sku` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_new` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_category` (`category`),
  KEY `idx_price` (`price`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (1,'Gohan SSJ2 - Dragon Ball','',36000000.00,10,NULL,'Dragon ball','img/products/product_1766343872_694844c07e360.jpg',1,0,'2025-12-21 15:50:21','2025-12-21 19:04:32'),(2,'Nico Robin - One Piece','',40000000.00,8,NULL,'One Piece','img/products/product_1766343935_694844ff76579.jpg',1,0,'2025-12-21 15:50:21','2025-12-21 19:06:26'),(3,'Sanji - One Piece','',5000000.00,1,NULL,'One Piece','img/products/product_1766343967_6948451f919d8.jpg',1,0,'2025-12-21 15:50:21','2025-12-26 03:47:43'),(4,'Rayquaza Mega - Pokemon','',8000000.00,2,NULL,'Pokemon','img/products/product_1766344008_69484548c8c00.jpg',1,0,'2025-12-21 15:50:21','2025-12-24 09:39:52'),(5,'Mega Greninja - Pokemon','',1300000.00,15,NULL,'Pokemon','img/mega.jpg',1,0,'2025-12-21 15:50:21','2025-12-21 19:07:04'),(6,'Batman: The Animated Series','',30000000.00,19,NULL,'Batman','img/batman.png',1,0,'2025-12-21 15:50:21','2025-12-24 10:26:38'),(8,'Dark Magician - Yu Gi Oh!','',18000000.00,7,NULL,'Yu Gi Oh!','img/products/product_1766344057_6948457916b17.jpg',1,0,'2025-12-21 15:50:21','2025-12-21 19:07:37'),(9,'Yuta - Jujutsu Kaisen','',1500000.00,19,NULL,'Jujutsu Kaisen','img/products/product_1766344152_694845d889dd9.jpg',1,0,'2025-12-21 15:50:21','2025-12-24 10:36:30'),(10,'Miyagi - Infinity Studio','',1000000.00,29,NULL,'Infinity Studio','img/products/product_1766344212_69484614d2d39.jpg',1,0,'2025-12-21 15:50:21','2025-12-24 10:24:05'),(11,'Naruto Samurai - Naruto','',9000000.00,18,NULL,'Naruto','img/products/product_1766344259_694846438e9e1.jpg',1,1,'2025-12-21 15:50:21','2025-12-21 19:10:59'),(12,'Obito - Naruto','',99999999.99,12,NULL,'Naruto','img/products/product_1766344323_694846831f523.jpg',1,0,'2025-12-21 15:50:21','2025-12-24 10:34:25');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(18,2) NOT NULL,
  `subtotal` decimal(18,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_order_items_order_id` (`order_id`),
  KEY `fk_order_items_item_id` (`item_id`),
  CONSTRAINT `fk_order_items_item_id` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `fk_order_items_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,4,1,8000000.00,8000000.00,'2025-12-24 09:39:52'),(2,1,3,1,5000000.00,5000000.00,'2025-12-24 09:39:52'),(3,2,9,1,1500000.00,1500000.00,'2025-12-24 09:42:35'),(6,4,6,1,30000000.00,30000000.00,'2025-12-24 10:26:38'),(8,6,12,4,99999999.99,399999999.96,'2025-12-24 10:34:25'),(9,7,9,5,1500000.00,7500000.00,'2025-12-24 10:36:30'),(10,8,3,1,5000000.00,5000000.00,'2025-12-26 03:26:04'),(11,9,3,2,5000000.00,10000000.00,'2025-12-26 03:47:43');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_statuses`
--

DROP TABLE IF EXISTS `order_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_statuses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_statuses`
--

LOCK TABLES `order_statuses` WRITE;
/*!40000 ALTER TABLE `order_statuses` DISABLE KEYS */;
INSERT INTO `order_statuses` VALUES (1,'pending','Item added to cart, awaiting checkout','warning',1,'2025-12-21 15:50:21'),(2,'confirmed','Order has been confirmed and payment received','info',1,'2025-12-21 15:50:21'),(3,'processing','Order is being prepared for shipment','primary',1,'2025-12-21 15:50:21'),(4,'shipped','Order has been dispatched and is in transit','success',1,'2025-12-21 15:50:21'),(5,'delivered','Order has been successfully delivered','success',1,'2025-12-21 15:50:21'),(6,'cancelled','Order has been cancelled by customer or admin','danger',1,'2025-12-21 15:50:21'),(7,'returned','Order has been returned by customer','secondary',1,'2025-12-21 15:50:21');
/*!40000 ALTER TABLE `order_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_uid` char(36) NOT NULL,
  `user_id` int NOT NULL,
  `total_amount` decimal(18,2) NOT NULL,
  `status_id` int NOT NULL DEFAULT '2',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_uid` (`order_uid`),
  UNIQUE KEY `idx_orders_order_uid` (`order_uid`),
  KEY `fk_orders_user_id` (`user_id`),
  KEY `fk_orders_status_id` (`status_id`),
  CONSTRAINT `fk_orders_status_id` FOREIGN KEY (`status_id`) REFERENCES `order_statuses` (`id`),
  CONSTRAINT `fk_orders_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'700646af-7134-695f-9174-e15057c39635',4,13000000.00,1,NULL,'2025-12-24 09:39:52','2025-12-24 09:39:52'),(2,'700646af-7ae8-8c33-b808-424f9db5e1ac',4,1500000.00,1,NULL,'2025-12-24 09:42:35','2025-12-24 09:42:35'),(4,'700646b0-186e-5fa7-afcb-581a269793fc',5,30000000.00,1,NULL,'2025-12-24 10:26:38','2025-12-24 10:26:38'),(6,'700646b0-3447-ea8a-9b99-7b072ad1604b',5,399999999.96,1,NULL,'2025-12-24 10:34:25','2025-12-24 10:34:25'),(7,'700646b0-3bb6-bfa3-b1f5-12f70de2b738',5,7500000.00,1,NULL,'2025-12-24 10:36:30','2025-12-24 10:36:30'),(8,'700646d2-7409-9bcc-ae8e-247051bfead3',6,5000000.00,1,NULL,'2025-12-26 03:26:04','2025-12-26 03:26:04'),(9,'700646d2-c17a-492c-86cc-3802f64865c5',6,10000000.00,1,NULL,'2025-12-26 03:47:43','2025-12-26 03:47:43');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_history`
--

DROP TABLE IF EXISTS `password_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_history`
--

LOCK TABLES `password_history` WRITE;
/*!40000 ALTER TABLE `password_history` DISABLE KEYS */;
INSERT INTO `password_history` VALUES (1,0,'0fcde8aea1314a1b9eb4b47842779e1e','2025-12-21 19:27:26'),(2,0,'ed9b393f3bda3baf59079187cb2ada6f','2025-12-21 19:29:04'),(3,4,'$2y$10$RApCg3/JPCGcAOXdgvEL.Oj/0nSy2Q3BCQpKrUwi9Qk/tmlU1Xvei','2025-12-24 09:41:59');
/*!40000 ALTER TABLE `password_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_otp`
--

DROP TABLE IF EXISTS `password_reset_otp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_otp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_email` (`email`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_otp`
--

LOCK TABLES `password_reset_otp` WRITE;
/*!40000 ALTER TABLE `password_reset_otp` DISABLE KEYS */;
INSERT INTO `password_reset_otp` VALUES (1,4,'perflexity.ai.01@gmail.com','faa964de40788d8b8cae8f6459559e2a2d317e43d332710e89ddc22f9c589bec','2025-12-24 09:41:34','2025-12-24 09:51:34',1,'2025-12-24 09:41:50'),(2,6,'testuser@example.com','8a162147cab4cc0f7dd6600b9138205a2c58793d7eb9339e34cf2980a15eeb35','2025-12-26 03:46:52','2025-12-26 03:56:52',0,NULL);
/*!40000 ALTER TABLE `password_reset_otp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permission_category` enum('user','product','order','system','report') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sensitive` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_name` (`permission_name`),
  KEY `idx_category` (`permission_category`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view_users','Xem danh sách người dùng','user',0,'2025-12-26 09:24:44'),(2,'create_user','Tạo người dùng mới','user',0,'2025-12-26 09:24:44'),(3,'edit_user','Chỉnh sửa thông tin người dùng','user',0,'2025-12-26 09:24:44'),(4,'delete_user','Xóa người dùng','user',1,'2025-12-26 09:24:44'),(5,'change_user_role','Thay đổi vai trò người dùng','user',1,'2025-12-26 09:24:44'),(6,'reset_user_password','Reset mật khẩu người dùng','user',1,'2025-12-26 09:24:44'),(7,'view_products','Xem sản phẩm','product',0,'2025-12-26 09:24:44'),(8,'create_product','Tạo sản phẩm mới','product',0,'2025-12-26 09:24:44'),(9,'edit_product','Chỉnh sửa sản phẩm','product',0,'2025-12-26 09:24:44'),(10,'delete_product','Xóa sản phẩm','product',1,'2025-12-26 09:24:44'),(11,'manage_inventory','Quản lý tồn kho','product',0,'2025-12-26 09:24:44'),(12,'view_orders','Xem đơn hàng','order',0,'2025-12-26 09:24:44'),(13,'view_own_orders','Xem đơn hàng của mình','order',0,'2025-12-26 09:24:44'),(14,'create_order','Tạo đơn hàng','order',0,'2025-12-26 09:24:44'),(15,'edit_order','Chỉnh sửa đơn hàng','order',0,'2025-12-26 09:24:44'),(16,'delete_order','Xóa đơn hàng','order',1,'2025-12-26 09:24:44'),(17,'cancel_order','Hủy đơn hàng','order',0,'2025-12-26 09:24:44'),(18,'view_settings','Xem cài đặt hệ thống','system',0,'2025-12-26 09:24:44'),(19,'edit_settings','Chỉnh sửa cài đặt hệ thống','system',1,'2025-12-26 09:24:44'),(20,'view_logs','Xem log hệ thống','system',0,'2025-12-26 09:24:44'),(21,'manage_backups','Quản lý sao lưu','system',1,'2025-12-26 09:24:44'),(22,'run_migrations','Chạy database migrations','system',1,'2025-12-26 09:24:44'),(23,'view_reports','Xem báo cáo','report',0,'2025-12-26 09:24:44'),(24,'export_reports','Xuất báo cáo','report',0,'2025-12-26 09:24:44'),(25,'view_analytics','Xem phân tích','report',0,'2025-12-26 09:24:44');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `privilege_audit_log`
--

DROP TABLE IF EXISTS `privilege_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `privilege_audit_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_user_id` int DEFAULT NULL,
  `old_role_id` int DEFAULT NULL,
  `new_role_id` int DEFAULT NULL,
  `permission_id` int DEFAULT NULL,
  `status` enum('success','failed','blocked') COLLATE utf8mb4_unicode_ci NOT NULL,
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `details` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privilege_audit_log`
--

LOCK TABLES `privilege_audit_log` WRITE;
/*!40000 ALTER TABLE `privilege_audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `privilege_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_limit_rules`
--

DROP TABLE IF EXISTS `rate_limit_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_limit_rules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `action_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_attempts` int NOT NULL,
  `window_seconds` int NOT NULL,
  `block_duration_seconds` int DEFAULT '300',
  `requires_captcha_after` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `action_name` (`action_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_limit_rules`
--

LOCK TABLES `rate_limit_rules` WRITE;
/*!40000 ALTER TABLE `rate_limit_rules` DISABLE KEYS */;
INSERT INTO `rate_limit_rules` VALUES (1,'login',5,300,600,3,1,'Đăng nhập: 5 lần/5 phút','2025-12-26 09:24:44','2025-12-26 09:24:44'),(2,'signup',3,3600,7200,2,1,'Đăng ký: 3 lần/giờ','2025-12-26 09:24:44','2025-12-26 09:24:44'),(3,'password_reset',3,3600,7200,2,1,'Reset mật khẩu: 3 lần/giờ','2025-12-26 09:24:44','2025-12-26 09:24:44'),(4,'api_request',100,60,120,NULL,1,'API: 100 requests/phút','2025-12-26 09:24:44','2025-12-26 09:24:44'),(5,'search',30,60,120,NULL,1,'Tìm kiếm: 30 lần/phút','2025-12-26 09:24:44','2025-12-26 09:24:44'),(6,'file_upload',20,3600,7200,NULL,1,'Upload: 20 files/giờ','2025-12-26 09:24:44','2025-12-26 09:24:44'),(7,'checkout',10,300,600,NULL,1,'Thanh toán: 10 lần/5 phút','2025-12-26 09:24:44','2025-12-26 09:24:44'),(8,'otp_request',5,600,1800,NULL,1,'OTP: 5 lần/10 phút','2025-12-26 09:24:44','2025-12-26 09:24:44');
/*!40000 ALTER TABLE `rate_limit_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_limits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `action_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_action_key` (`action_key`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_limits`
--

LOCK TABLES `rate_limits` WRITE;
/*!40000 ALTER TABLE `rate_limits` DISABLE KEYS */;
INSERT INTO `rate_limits` VALUES (1,'login_testuser@example.com_::1','::1','2025-12-26 09:38:34'),(2,'login_testuser@example.com_::1','::1','2025-12-26 09:38:37'),(3,'login_testuser@example.com_::1','::1','2025-12-26 09:39:02'),(4,'login_testuser@example.com_::1','::1','2025-12-26 09:39:52');
/*!40000 ALTER TABLE `rate_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `granted_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  KEY `fk_rp_permission` (`permission_id`),
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1,1,'2025-12-26 09:24:44',NULL),(2,1,2,'2025-12-26 09:24:44',NULL),(3,1,3,'2025-12-26 09:24:44',NULL),(4,1,4,'2025-12-26 09:24:44',NULL),(5,1,5,'2025-12-26 09:24:44',NULL),(6,1,6,'2025-12-26 09:24:44',NULL),(7,1,7,'2025-12-26 09:24:44',NULL),(8,1,8,'2025-12-26 09:24:44',NULL),(9,1,9,'2025-12-26 09:24:44',NULL),(10,1,10,'2025-12-26 09:24:44',NULL),(11,1,11,'2025-12-26 09:24:44',NULL),(12,1,12,'2025-12-26 09:24:44',NULL),(13,1,13,'2025-12-26 09:24:44',NULL),(14,1,14,'2025-12-26 09:24:44',NULL),(15,1,15,'2025-12-26 09:24:44',NULL),(16,1,16,'2025-12-26 09:24:44',NULL),(17,1,17,'2025-12-26 09:24:44',NULL),(18,1,18,'2025-12-26 09:24:44',NULL),(19,1,19,'2025-12-26 09:24:44',NULL),(20,1,20,'2025-12-26 09:24:44',NULL),(21,1,21,'2025-12-26 09:24:44',NULL),(22,1,22,'2025-12-26 09:24:44',NULL),(23,1,23,'2025-12-26 09:24:44',NULL),(24,1,24,'2025-12-26 09:24:44',NULL),(25,1,25,'2025-12-26 09:24:44',NULL),(32,2,17,'2025-12-26 09:24:44',NULL),(33,2,8,'2025-12-26 09:24:44',NULL),(34,2,15,'2025-12-26 09:24:44',NULL),(35,2,9,'2025-12-26 09:24:44',NULL),(36,2,24,'2025-12-26 09:24:44',NULL),(37,2,11,'2025-12-26 09:24:44',NULL),(38,2,12,'2025-12-26 09:24:44',NULL),(39,2,7,'2025-12-26 09:24:44',NULL),(40,2,23,'2025-12-26 09:24:44',NULL),(41,2,1,'2025-12-26 09:24:44',NULL),(47,3,17,'2025-12-26 09:24:44',NULL),(48,3,14,'2025-12-26 09:24:44',NULL),(49,3,13,'2025-12-26 09:24:44',NULL),(50,3,7,'2025-12-26 09:24:44',NULL);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Full administrative access - can delete users, orders, and manage staff','2025-12-21 15:50:20'),(2,'sales_manager','Sales management - can manage products and view orders, but cannot delete users or manage staff','2025-12-21 15:50:20'),(3,'customer','Regular customer - can browse, search products and make purchases','2025-12-21 15:50:20');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_audit_log`
--

DROP TABLE IF EXISTS `security_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_audit_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `details` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_audit_log`
--

LOCK TABLES `security_audit_log` WRITE;
/*!40000 ALTER TABLE `security_audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_violations`
--

DROP TABLE IF EXISTS `security_violations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_violations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `violation_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `request_uri` text COLLATE utf8mb4_unicode_ci,
  `request_method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_data` text COLLATE utf8mb4_unicode_ci,
  `details` text COLLATE utf8mb4_unicode_ci,
  `severity` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `is_resolved` tinyint(1) DEFAULT '0',
  `resolved_by` int DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_violation_type` (`violation_type`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_severity` (`severity`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_violations`
--

LOCK TABLES `security_violations` WRITE;
/*!40000 ALTER TABLE `security_violations` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_violations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session_audit_log`
--

DROP TABLE IF EXISTS `session_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_audit_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_timestamp` (`timestamp`),
  CONSTRAINT `fk_audit_log_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_audit_log`
--

LOCK TABLES `session_audit_log` WRITE;
/*!40000 ALTER TABLE `session_audit_log` DISABLE KEYS */;
INSERT INTO `session_audit_log` VALUES (2,4,'customer_login','Successful login','::1','2025-12-24 09:39:42'),(7,4,'customer_logout','User logged out','::1','2025-12-24 10:05:22'),(8,5,'customer_login','Successful login','::1','2025-12-24 10:23:14'),(9,5,'customer_logout','User logged out','::1','2025-12-24 10:23:17'),(10,4,'customer_login','Successful login','::1','2025-12-24 10:23:25'),(11,4,'customer_logout','User logged out','::1','2025-12-24 10:23:32'),(12,5,'customer_login','Successful login','::1','2025-12-24 10:23:49'),(17,6,'customer_login','Successful login','::1','2025-12-26 03:25:45'),(18,6,'customer_login','Successful login','::1','2025-12-26 09:39:52');
/*!40000 ALTER TABLE `session_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `role_id` int NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `session_type` varchar(20) NOT NULL DEFAULT 'customer',
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `logged_out_time` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_login_time` (`login_time`),
  KEY `fk_sessions_role_id` (`role_id`),
  CONSTRAINT `fk_sessions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `fk_sessions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES (1,'jfk48ljritm3m26jm3vs9nta4v',4,'0',3,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0','customer','2025-12-24 09:39:42','2025-12-24 10:05:22','2025-12-24 10:05:22',0),(3,'anl73jp0nua39q9pm11c6s55er',5,'0',3,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0','customer','2025-12-24 10:23:14','2025-12-24 10:23:17','2025-12-24 10:23:17',0),(4,'l63bqas3of2db0hrm379fnjibt',4,'0',3,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0','customer','2025-12-24 10:23:25','2025-12-24 10:23:32','2025-12-24 10:23:32',0),(5,'3u7au77ebtf8kvmvgb6rp6bebe',5,'0',3,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0','customer','2025-12-24 10:23:49','2025-12-24 10:23:49',NULL,1),(6,'odjptol14lg627fg5ivnclls0f',6,'0',3,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0','customer','2025-12-26 03:25:45','2025-12-26 09:39:52','2025-12-26 09:39:52',0),(7,'5vvh17qgmtfra8cuqj2fdo9d8g',6,'0',3,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0','customer','2025-12-26 09:39:52','2025-12-26 09:39:52',NULL,1);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_uid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `city` varchar(100) NOT NULL,
  `address` varchar(500) NOT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `user_uid` (`user_uid`),
  KEY `idx_city` (`city`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (4,'018c6c80-1b2c-7a8b-9c0d-1e2f3a4b5c6f','HO VAN TAM','perflexity.ai.01@gmail.com','$2y$10$BYiI911KoYHnatNgaFAQG.7vQ///plVXQH9bdpGWgPZSHlihEJmhW','0999999999','Ho Chi Minh','1074/2/7 Quang Trung',1,NULL,NULL,1,'2025-12-24 09:39:22','2025-12-24 09:41:59'),(5,'700646b0-0a3b-3f1f-a8ed-3849f7c0b50a','aazcvxzxzz','ztxchou@gmail.com','$2y$10$IAqV8XfzG3zPKEyOtEk5feOenVm4YgmP0AHIeapAMHpl1JMVvLLvm','0999999999','Ho Chi Minh','1074/2/7 Quang Trung',1,NULL,NULL,1,'2025-12-24 10:22:46','2025-12-24 10:23:05'),(6,'3ea9b25d-0583-403c-a5bb-6a1cf0e71569','Test User','testuser@example.com','$2y$10$mMdE.dQV4/xplLjNIfjK7evXq.UTHrLubMa375Cy7G13Fm1Xvc2U.','0999999999','Ho Chi Minh','123 Test Street',1,NULL,NULL,1,'2025-12-26 03:25:11','2025-12-26 03:25:11');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_items`
--

DROP TABLE IF EXISTS `users_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `item_id` int NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Added to cart',
  `quantity` int DEFAULT '1',
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_items`
--

LOCK TABLES `users_items` WRITE;
/*!40000 ALTER TABLE `users_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'store'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-26 17:15:38
