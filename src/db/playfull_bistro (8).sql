-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2025 at 05:11 PM
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
-- Database: `playfull_bistro`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `product_id`, `quantity`, `price`, `order_number`, `created_at`) VALUES
(176, 33, 1, 120.00, '2', '2025-01-29 09:16:44'),
(178, 34, 1, 20.00, '2', '2025-01-29 09:27:49'),
(179, 29, 1, 60.00, '2', '2025-01-29 09:58:11'),
(180, 34, 1, 20.00, '2', '2025-01-29 12:18:05'),
(181, 34, 1, 20.00, '2', '2025-01-29 12:37:54'),
(182, 29, 1, 60.00, '2', '2025-01-29 12:38:53'),
(183, 29, 1, 60.00, '1', '2025-01-29 12:40:05'),
(184, 33, 1, 120.00, '1', '2025-01-29 12:40:16'),
(185, 29, 1, 60.00, '1', '2025-01-29 12:40:58'),
(186, 33, 1, 120.00, '1', '2025-01-29 12:40:58'),
(187, 29, 1, 60.00, '2', '2025-01-29 12:42:08'),
(188, 31, 1, 50.00, '2', '2025-01-29 12:43:52'),
(189, 28, 1, 160.00, '2', '2025-01-29 12:45:05'),
(190, 28, 1, 160.00, '1', '2025-01-29 12:47:35'),
(191, 36, 1, 50.00, '1', '2025-01-29 12:48:01'),
(192, 28, 1, 160.00, '1', '2025-01-29 12:49:44'),
(193, 29, 1, 60.00, '1', '2025-01-29 12:50:22');

-- --------------------------------------------------------

--
-- Table structure for table `paid`
--

CREATE TABLE `paid` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paid`
--

INSERT INTO `paid` (`id`, `order_id`, `paid_at`) VALUES
(54, 176, '2025-01-29 09:27:30'),
(55, 176, '2025-01-29 09:28:13'),
(56, 178, '2025-01-29 09:28:13'),
(57, 176, '2025-01-29 09:58:57'),
(58, 178, '2025-01-29 09:58:57'),
(59, 179, '2025-01-29 09:58:57'),
(60, 176, '2025-01-29 12:33:32'),
(61, 178, '2025-01-29 12:33:32'),
(62, 179, '2025-01-29 12:33:32'),
(63, 180, '2025-01-29 12:33:32'),
(64, 181, '2025-01-29 12:38:17'),
(65, 182, '2025-01-29 12:39:41'),
(66, 183, '2025-01-29 12:40:34'),
(67, 184, '2025-01-29 12:40:34'),
(68, 185, '2025-01-29 12:41:16'),
(69, 186, '2025-01-29 12:41:16'),
(70, 187, '2025-01-29 12:45:47'),
(71, 188, '2025-01-29 12:45:47'),
(72, 189, '2025-01-29 12:45:47'),
(73, 190, '2025-01-29 12:48:40'),
(74, 191, '2025-01-29 12:48:40'),
(75, 192, '2025-01-29 12:50:50'),
(76, 193, '2025-01-29 12:50:50');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `image`, `product_name`, `description`, `quantity`, `price`, `created_at`, `status`) VALUES
(28, 'product_img/sisig.jpg', 'SIZZLING SISIG', 'Now serving SIZZLING SISIG everyday!! Available for Dine in customers.', 192, 160.00, '2025-01-04 07:03:35', 'active'),
(29, 'product_img/RM.jpg', 'RM', 'A V A I  L  A  B  L  E ', 92, 60.00, '2025-01-04 07:13:06', 'active'),
(31, 'product_img/Chicken skin.jpg', 'Chicken skin', 'Chicken skin ipares sa ulan😜aw! Available diri sa PlayFull Bistro', 97, 50.00, '2025-01-04 07:21:03', 'active'),
(33, 'product_img/BULALO.jpg', 'BULALO', 'AVAILABLE', 97, 120.00, '2025-01-04 07:24:32', 'active'),
(34, 'product_img/ACHARA.jpg', 'ACHARA', 'AVAILABLE', 95, 20.00, '2025-01-04 07:25:36', 'active'),
(35, 'product_img/pork baby back ribs.jpg', 'PORK BABY BACK RIBS', 'Introducing our pork baby back ribs for only P150/serve. Lami gyud sa way pabor2 ', 100, 150.00, '2025-01-04 07:31:11', 'active'),
(36, 'product_img/Humba pork siki.jpg', 'HUMBA PORK SIKI', 'Ania na pud ang pwede ninyong panihapon. Humba pork siki', 100, 50.00, '2025-01-04 07:34:06', 'active'),
(39, 'product_img/472347036_1285754402665743_5455920285102684592_n.jpg', 'lami', 'lami ni', 1, 99999999.99, '2025-01-08 00:32:14', 'inactive');

-- --------------------------------------------------------

--
-- Table structure for table `served`
--

CREATE TABLE `served` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `served_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `served`
--

INSERT INTO `served` (`id`, `order_id`, `served_at`) VALUES
(43, 183, '2025-01-29 12:48:22'),
(44, 184, '2025-01-29 12:48:22'),
(45, 185, '2025-01-29 12:48:22'),
(46, 186, '2025-01-29 12:48:22'),
(49, 190, '2025-01-29 12:49:50'),
(50, 191, '2025-01-29 12:49:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone_number` varchar(15) NOT NULL,
  `user_type` varchar(20) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `enable_date` datetime DEFAULT NULL,
  `disable_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `phone_number`, `user_type`, `status`, `enable_date`, `disable_date`) VALUES
(42, 'Bimbo', '$2y$10$WUowHGWGmXc8omPN.VYUy.Y0ZEyoDSanTdYZEg8K8n.umnAGFMxJ6', 'villaganasbimbo123@gmail.com', '2024-11-16 01:22:33', '09000000001', 'admin', 'active', NULL, NULL),
(43, 'Jessa', '$2y$10$dcUYYmhh73Ge/FSW3qWCOuNOlzg6JahUAaZImCzK7De6kN1QAOxXO', 'villaganasbimbo124@gmail.com', '2024-11-16 01:23:13', '09999999999', 'user', 'active', NULL, NULL),
(45, 'bim', '$2y$10$2bqYaoss7M3USxBZ.Lf43.4oEdQYr/nvJ88LNlA.nGwmNXwf1g7OS', 'escodero@gmail.com', '2025-01-29 13:26:15', '09350147772', 'employee', 'active', '2025-01-29 00:00:00', '2025-01-30 00:00:00'),
(46, 'ample', '$2y$10$mvYb/Me/NWJwt.kW3r1nBuODy3P0pcHCk1B8i6Vntze3I85rvll4K', 'ample@gmail.com', '2025-01-29 13:46:16', '09123456789', 'employee', 'active', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_fk_1` (`product_id`);

--
-- Indexes for table `paid`
--
ALTER TABLE `paid`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `served`
--
ALTER TABLE `served`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT for table `paid`
--
ALTER TABLE `paid`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `served`
--
ALTER TABLE `served`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_fk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `paid`
--
ALTER TABLE `paid`
  ADD CONSTRAINT `paid_fk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `served`
--
ALTER TABLE `served`
  ADD CONSTRAINT `served_fk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
