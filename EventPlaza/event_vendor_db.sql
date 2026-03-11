-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 10, 2026 at 09:50 AM
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
-- Database: `event_vendor_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `event_details` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `total_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `customer_id`, `vendor_id`, `event_date`, `event_details`, `status`, `total_price`, `created_at`) VALUES
(1, 2, 1, '2026-02-20', 'sdf', 'confirmed', NULL, '2026-02-19 02:56:43'),
(2, 2, 2, '2026-02-27', 'SDF', 'pending', NULL, '2026-02-19 05:13:34'),
(3, 2, 3, '2026-02-27', 'ASDF', 'pending', NULL, '2026-02-19 05:27:13'),
(4, 2, 3, '2026-02-26', 'QWERT', 'pending', NULL, '2026-02-19 05:30:56'),
(5, 2, 3, '2026-02-20', 'ER', 'pending', NULL, '2026-02-19 05:39:41'),
(6, 2, 1, '2026-02-28', 'ASDF', 'pending', NULL, '2026-02-19 05:51:07'),
(7, 6, 3, '2026-03-12', 'wertghb', 'pending', NULL, '2026-03-10 06:23:54');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`, `description`) VALUES
(1, 'Photographers', NULL, 'Capture your special moments.'),
(2, 'Caterers', NULL, 'Delicious food for your guests.'),
(3, 'Decorators', NULL, 'Transform your venue with style.'),
(4, 'Venues', NULL, 'Perfect locations for your events.'),
(5, 'Musicians', NULL, 'Live music to entertain your guests.');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Janith nethsara', 'janithyapa0713890040@gmail.com', 'Zxcv', '2026-02-18 08:25:39'),
(2, 'nethsara', 'janithyapa0713890040@gmail.com', 'Inquiry to vendor \'nethsara\' (ID:1):\nQWER', '2026-02-19 05:49:44');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `vendor_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `booking_id`, `rating`, `comment`, `created_at`, `vendor_id`, `customer_id`) VALUES
(1, NULL, 4, 'GOOG', '2026-02-19 05:13:59', 2, 2),
(2, NULL, 1, 'ACV', '2026-02-19 05:41:46', 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','vendor','customer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$pvEpstnVds4lPy.gafiCi.ZER7snzqh7gbwXd9a6TrnzumcqV7sbC', 'admin', '2026-02-18 04:49:51'),
(2, 'janithnethsara', 'janithyapa0713890040@gmail.com', '$2y$10$yDV5k3wLIjKw6nLTHHed4.jKS31aIb6ZpDxR0HbvgttvtTCZTdtGu', 'customer', '2026-02-18 04:54:24'),
(3, 'nethsara', 'janith@gmail.com', '$2y$10$7BHu3fOidSGlmerrSt8jsu91uMeLiUh8ffH65vuIX9g2w1MkukaHG', 'vendor', '2026-02-18 07:34:55'),
(4, 'jj', 'jj@gmail.com', '$2y$10$LwgCxF81rRNcMTI.1R1evOzYOczyrUvYuZr39KaYh1aMc1Pk7A9.y', 'vendor', '2026-02-19 02:45:55'),
(5, 'pp', 'pp@gmail.com', '$2y$10$qhHUUbpEFfI2P2Ib1ja30Oyrx5RpgAgQTOn3KjhFgEEtVjm9z2Or6', 'vendor', '2026-02-19 03:07:54'),
(6, 'janith', 'Heshannawarathnams@gmail.com', '$2y$10$Qe0QkVNH/h2bQO3HWVpWQ.aKpfj2E9JoV0JhotUJWNc.aE0c4GQ6G', 'customer', '2026-03-10 06:22:48');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `business_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price_range` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `availability_calendar` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(30) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(30) DEFAULT NULL,
  `years_exp` int(11) DEFAULT NULL,
  `events_done` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `user_id`, `category_id`, `business_name`, `description`, `price_range`, `location`, `profile_image`, `is_verified`, `availability_calendar`, `created_at`, `phone`, `website`, `facebook`, `instagram`, `whatsapp`, `years_exp`, `events_done`) VALUES
(1, 3, 1, 'nethsara', 'HI', 'RS.10000', 'kandy', 'uploads/1771469068_WIN_20250314_10_11_42_Pro.jpg', 1, NULL, '2026-02-18 07:34:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 4, 1, 'jj\'s Business', 'Please update your profile', '$$', 'City', NULL, 1, NULL, '2026-02-19 02:45:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 5, 3, 'chuti Business', 'Please update your profile', '$', 'kurunagala', 'uploads/1771470688_WIN_20250314_10_11_34_Pro.jpg', 1, NULL, '2026-02-19 03:07:54', '', '', '', '', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vendor_gallery`
--

CREATE TABLE `vendor_gallery` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor_gallery`
--

INSERT INTO `vendor_gallery` (`id`, `vendor_id`, `image_path`) VALUES
(1, 2, 'uploads/1771469960_0_Screenshot 2025-11-06 115558.png'),
(2, 1, 'uploads/1771470400_0_Screenshot (1).png'),
(3, 1, 'uploads/1771470400_1_Screenshot (2).png'),
(4, 1, 'uploads/1771470400_2_Screenshot (3).png'),
(5, 1, 'uploads/1771470400_3_Screenshot (4).png'),
(6, 1, 'uploads/1771470400_4_Screenshot 2025-11-06 115558.png'),
(7, 1, 'uploads/1771470400_5_Screenshot 2025-11-21 143437.png'),
(8, 3, 'uploads/1771470502_0_Screenshot (1).png'),
(9, 3, 'uploads/1771470502_1_Screenshot (2).png'),
(10, 3, 'uploads/1771470502_2_Screenshot (3).png'),
(11, 3, 'uploads/1771470502_3_Screenshot (4).png'),
(12, 3, 'uploads/1771470502_4_Screenshot 2025-11-06 115558.png'),
(13, 3, 'uploads/1771470502_5_Screenshot 2025-11-21 143437.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `vendor_gallery`
--
ALTER TABLE `vendor_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vendor_gallery`
--
ALTER TABLE `vendor_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `vendors`
--
ALTER TABLE `vendors`
  ADD CONSTRAINT `vendors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendors_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `vendor_gallery`
--
ALTER TABLE `vendor_gallery`
  ADD CONSTRAINT `vendor_gallery_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
