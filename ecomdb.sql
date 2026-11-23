-- QCera e-commerce database (updated)
-- Import this file in phpMyAdmin (it recreates every table).
-- First admin login:  admin@qcera.com  /  Admin@12345   (change the password after first login!)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `ecomdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ecomdb`;

DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------
-- users  (new: role, created_at, unique email/username)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `username`, `email`, `address`, `password`, `role`) VALUES
(1, 'asd', 'a@gmail.com', 'asd', '$2y$10$VXkpEygN.6eBi4nmgDCdUONSMqx97k19PtMF6ogijoBLGWRpfSvlC', 'user'),
(3, 'test', 'test@gmail.com', 'test123', '$2y$10$XkxIn0Etae6W7SIZT4dqwOW7YK5PpQ7Z8JCVx7uYjWQTQR88EhdZi', 'user'),
(4, 'aaa', 'aaa@gmail.com', 'aaa', '$2y$10$idifnFD8jqQDsO8vPIkr5uPR651iA8ol1OGvmYS.EHq2q07s8v/6a', 'user'),
(5, 'admin', 'admin@qcera.com', 'QCera HQ, Quezon City', '$2y$12$F73GLkxiGJrE7yeUOMKceednaTkgmPOQA6u2lJsJzjRv9mcNUIfgy', 'admin');

ALTER TABLE `users` AUTO_INCREMENT = 6;

-- --------------------------------------------------------
-- products  (replaces the PHP array in product.php)
-- --------------------------------------------------------
CREATE TABLE `products` (
  `productId` int(11) NOT NULL AUTO_INCREMENT,
  `productName` varchar(255) NOT NULL,
  `productCategory` varchar(50) NOT NULL,
  `productPrice` int(11) NOT NULL,
  `productImg` varchar(255) NOT NULL,
  `productDescription` text NOT NULL,
  `productStock` int(11) NOT NULL DEFAULT 0,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`productId`),
  KEY `idx_products_category` (`productCategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` (`productId`, `productName`, `productCategory`, `productPrice`, `productImg`, `productDescription`, `productStock`) VALUES
(1, 'KEYAN', 'Keyboard', 2749, 'imgvid/Keyan.png', 'Latest premium mechanical keyboard with soft and tactile switches. Fast response and clean aesthetic perfect for you, choose QCERA Keyan', 25),
(2, 'ECHO', 'Keyboard', 4749, 'imgvid/Echo.png', 'Low-profile wired mechanical keyboard. Silent, fast, and suits with any desks. QCERA Echo', 25),
(3, 'CREMA', 'Keyboard', 3999, 'imgvid/Crema.jpeg', 'Utilize our latest QCERA Crema for a cream-colored keyboard with vintage aesthetic and quality performance. Satisfying sound and stylish design.', 25),
(4, 'CLOUD VS2', 'Keyboard', 3499, 'imgvid/Cloud-VS2.png', 'Smooth and silent for a better desktop, use QCERA Cloud VS2. Perfect for gaming, work, and studying.', 25),
(5, 'ERGO SILENT', 'Keyboard', 2899, 'imgvid/Ergo-Silent.png', 'Ergonomic silent keyboard designed for a smoother and comfortable typing. Quiet in style with QCERA Ergo Silent keyboard.', 25),
(6, 'KEYRA', 'Keyboard', 3700, 'imgvid/Keyra.jpeg', 'Unlock QCERA Keyra for a wireless mechanical keyboard. Sleek and fast.', 25),
(7, 'NIMBUS V2', 'Keyboard', 3499, 'imgvid/Nimbus-V2.jpeg', 'Soft and stylish with QCERA Nimbus V2. A cloud-soft typing keyboard built for typists and gamers.', 25),
(8, 'PHANTOM', 'Keyboard', 4499, 'imgvid/Phantom.jpeg', 'Premium mechanical keyboard using QCERA Phantom. Bold, bright, and built to last.', 25),
(9, 'RGB HAVOC', 'Keyboard', 5759, 'imgvid/RGB-Havoc.jpeg', 'RGB keyboard with vibrant lighting effects. Tactile, responsive, and built for intense gaming. Play fast with QCERA RGB Havoc.', 25),
(10, 'RGB OLYMPUS', 'Keyboard', 5949, 'imgvid/RGB-Olympus.png', 'Hot mechanical keyboard with aluminum frame and luxury feel. Pro performance and premium RGB effects with QCERA Olympus', 25),
(11, 'CLICKERA', 'Mouse', 599, 'imgvid/Clickera.jpeg', 'Latest wireless mouse with silent clicks and smooth scroll. Use QCERA Clickera, minimal and perfect for work and school.', 25),
(12, 'ORBIT WIRED', 'Mouse', 550, 'imgvid/Orbit-Wired.jpeg', 'Comfortable in style with QCERA Orbit wired mouse for doing tasks. Stable and zero delay. Premium feel', 25),
(13, 'MANTRA WIRED', 'Mouse', 450, 'imgvid/Mantra-Wired.jpeg', 'Premium wired gaming mouse with QCERA Mantra Wired mouse. Just plug and play.', 25),
(14, 'AERO PRO', 'Mouse', 849, 'imgvid/Aero-Pro.jpeg', 'Use QCERA Aero Pro mouse for a lightweight experience built with high-precision sensor perfect for gaming and work.', 25),
(15, 'NOVA 1', 'Mouse', 700, 'imgvid/Nova-1.jpeg', 'Wireless gaming mouse with ultra-precise tracking. Designed for heavy tasks choose QCERA Nova 1', 25),
(16, 'PANORA', 'Monitor', 29999, 'imgvid/Panora.jpeg', 'Latest 34-inch ultra wide curved monitor with 165Hz refresh rate. Bigger screen for multitasking, gaming, and working. Choose QCERA Panora.', 25),
(17, 'NOVA 4K', 'Monitor', 15500, 'imgvid/Nova-4K.jpeg', '27-inch 4K monitor with crystal-clear visuals, vibrant colors, and smooth gameplay. Perfect for your tasks with QCERA Nova 4K.', 25),
(18, 'SLIM VISION', 'Monitor', 13499, 'imgvid/Slim-Vision.jpeg', 'Full HD monitor with slim and stylish build ideal for everyone. Choose QCERA Slim Vision.', 25),
(19, 'SONIC 32', 'Monitor', 10500, 'imgvid/Sonic-32.jpeg', 'Perfect for work, movies, and multitasking with QCERA Sonic 32. Premium quality for different tasks', 25),
(20, 'SPECTRE PRO', 'Monitor', 20499, 'imgvid/Spectre-Pro.jpeg', 'Ultra fast and smooth with 27-inch QCERA Spectre Pro designed for heavy loaded tasks.', 25),
(21, 'WORK EDGE', 'Monitor', 9499, 'imgvid/Work-Edge.jpeg', 'Reliable everyday monitor with clean edges and quality performance. Built for focused work with QCERA Work Edge.', 25),
(22, 'VELOCITY RAM 16GB', 'PC Component', 2499, 'imgvid/QCERA-Velocity-RAM.jpeg', 'Power through everything with QCERA Velocity RAM 16GB. High-duty 16GB memory for heavy multitasking. Premium build quality, stable performance, and easy installation for users.', 25),
(23, 'DUALCOOL USB FAN', 'PC Component', 1499, 'imgvid/QCERA-DualCool-USB-Fan.jpeg', 'Cool in style with QCERA DualCool USB Fan. An external cooling solution for laptops, consoles, and mini PCs. With precise speed control that delivers powerful cooling on demand by just plugging.', 25),
(24, 'RGB FAN (3-Pack)', 'PC Component', 2199, 'imgvid/QCERA-RGB-Fan.jpeg', 'Upgrade with QCERA RGB Fan 3-pack. Featuring three premium fans with superior airflow and silent operation.', 25),
(25, 'FROST TOWER CPU COOLER', 'PC Component', 2999, 'imgvid/QCERA-Frost.jpeg', 'Unlock premium tower air cooler for maximum performance with the QCERA Frost Tower CPU Cooler. Featuring a high-pressure silent fan for heavy work.', 25),
(26, 'SWIFT SSD', 'PC Component', 3499, 'imgvid/QCERA-Swift.jpeg', 'Utilize next level speed with the QCERA Swift SSD. Created for high-performance builds.', 25);

ALTER TABLE `products` AUTO_INCREMENT = 27;

-- --------------------------------------------------------
-- orders  (one row per order; every order has a unique order_code)
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_code` varchar(30) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `payment_method` enum('cod','online') NOT NULL DEFAULT 'cod',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `payment_ref` varchar(40) DEFAULT NULL,
  `order_status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_code` (`order_code`),
  KEY `idx_orders_user` (`user_id`),
  KEY `idx_orders_status` (`order_status`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- order_items  (quantity x price is stored here as line_total)
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `unit_price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `line_total` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_items_order` (`order_id`),
  CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`productId`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- cart  (saved per user)
-- --------------------------------------------------------
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cart_user_product` (`user_id`,`product_id`),
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`productId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
