-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2025 at 07:38 AM
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
(166, 28, 1, 160.00, '1', '2025-01-29 05:42:14'),
(167, 29, 1, 60.00, '1', '2025-01-29 05:42:15'),
(168, 28, 1, 160.00, '1', '2025-01-29 05:42:52'),
(169, 31, 1, 50.00, '1', '2025-01-29 05:43:05');

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
(45, 166, '2025-01-29 05:42:31'),
(46, 167, '2025-01-29 05:42:31');

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
(28, 'product_img/sisig.jpg', 'SIZZLING SISIG', 'Now serving SIZZLING SISIG everyday!! Available for Dine in customers.', 194, 160.00, '2025-01-04 07:03:35', 'active'),
(29, 'product_img/RM.jpg', 'RM', 'A V A I  L  A  B  L  E ', 99, 60.00, '2025-01-04 07:13:06', 'active'),
(31, 'product_img/Chicken skin.jpg', 'Chicken skin', 'Chicken skin ipares sa ulan😜aw! Available diri sa PlayFull Bistro', 97, 50.00, '2025-01-04 07:21:03', 'active'),
(33, 'product_img/BULALO.jpg', 'BULALO', 'AVAILABLE', 99, 120.00, '2025-01-04 07:24:32', 'active'),
(34, 'product_img/ACHARA.jpg', 'ACHARA', 'AVAILABLE', 99, 20.00, '2025-01-04 07:25:36', 'active'),
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
  `user_type` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `phone_number`, `user_type`) VALUES
(42, 'Bimbo', '$2y$10$WUowHGWGmXc8omPN.VYUy.Y0ZEyoDSanTdYZEg8K8n.umnAGFMxJ6', 'villaganasbimbo123@gmail.com', '2024-11-16 01:22:33', '09000000000', 'admin'),
(43, 'Jessa', '$2y$10$dcUYYmhh73Ge/FSW3qWCOuNOlzg6JahUAaZImCzK7De6kN1QAOxXO', 'villaganasbimbo124@gmail.com', '2024-11-16 01:23:13', '09999999999', 'user'),
(44, 'bim', '$2y$10$So4apkcvC6Wh9Kb36q./A.BvOBzLnX87trw44L1JEuqAw6iipEI9u', 'villaganasbimbo1@gmail.com', '2024-11-16 01:23:47', '09609005374', 'employee');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT for table `paid`
--
ALTER TABLE `paid`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `served`
--
ALTER TABLE `served`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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
