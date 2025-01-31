-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2025 at 04:50 PM
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
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_number` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `total_amount`, `created_at`, `order_number`) VALUES
(83, 280.00, '2025-01-04 15:44:44', '1'),
(84, 60.00, '2025-01-04 15:45:52', 'BIMBO');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `order_number` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`, `product_name`, `order_number`) VALUES
(155, 83, 28, 1, 160.00, 'SIZZLING SISIG', '1'),
(156, 83, 33, 1, 120.00, 'BULALO', '1'),
(157, 84, 29, 1, 60.00, 'RM', 'BIMBO');

-- --------------------------------------------------------

--
-- Table structure for table `paid`
--

CREATE TABLE `paid` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `image`, `product_name`, `description`, `quantity`, `price`, `created_at`) VALUES
(28, 'product_img/sisig.jpg', 'SIZZLING SISIG', 'Now serving SIZZLING SISIG everyday!! Available for Dine in customers.', 197, 160.00, '2025-01-04 15:03:35'),
(29, 'product_img/RM.jpg', 'RM', 'A V A I  L  A  B  L  E ', 99, 60.00, '2025-01-04 15:13:06'),
(30, 'product_img/PAKLAY.jpg', 'PAKLAY', 'Playfull Bistro\'s Paklay in party tray size, good for 15-20 persons.', 100, 500.00, '2025-01-04 15:19:34'),
(31, 'product_img/Chicken skin.jpg', 'Chicken skin', 'Chicken skin ipares sa ulan😜aw! Available diri sa PlayFull Bistro', 100, 50.00, '2025-01-04 15:21:03'),
(32, 'product_img/KINILAW.jpg', 'KINILAW', 'KINILAW NGA BARILIS', 99, 100.00, '2025-01-04 15:22:43'),
(33, 'product_img/BULALO.jpg', 'BULALO', 'AVAILABLE', 99, 120.00, '2025-01-04 15:24:32'),
(34, 'product_img/ACHARA.jpg', 'ACHARA', 'AVAILABLE', 100, 20.00, '2025-01-04 15:25:36'),
(35, 'product_img/pork baby back ribs.jpg', 'PORK BABY BACK RIBS', 'Introducing our pork baby back ribs for only P150/serve. Lami gyud sa way pabor2 ', 100, 150.00, '2025-01-04 15:31:11'),
(36, 'product_img/Humba pork siki.jpg', 'HUMBA PORK SIKI', 'Ania na pud ang pwede ninyong panihapon. Humba pork siki', 100, 50.00, '2025-01-04 15:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `products_archive`
--

CREATE TABLE `products_archive` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
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
(42, 'Bimbo', '$2y$10$WUowHGWGmXc8omPN.VYUy.Y0ZEyoDSanTdYZEg8K8n.umnAGFMxJ6', 'villaganasbimbo123@gmail.com', '2024-11-16 09:22:33', '09000000000', 'admin'),
(43, 'Jessa', '$2y$10$dcUYYmhh73Ge/FSW3qWCOuNOlzg6JahUAaZImCzK7De6kN1QAOxXO', 'villaganasbimbo124@gmail.com', '2024-11-16 09:23:13', '09999999999', 'user'),
(44, 'bim', '$2y$10$So4apkcvC6Wh9Kb36q./A.BvOBzLnX87trw44L1JEuqAw6iipEI9u', 'villaganasbimbo1@gmail.com', '2024-11-16 09:23:47', '09609005374', 'employee');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

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
-- Indexes for table `products_archive`
--
ALTER TABLE `products_archive`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT for table `paid`
--
ALTER TABLE `paid`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `products_archive`
--
ALTER TABLE `products_archive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `paid`
--
ALTER TABLE `paid`
  ADD CONSTRAINT `paid_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
