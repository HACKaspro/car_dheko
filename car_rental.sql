-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 03, 2025 at 07:06 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `car_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `car_id`, `start_date`, `end_date`, `total_price`, `status`, `created_at`, `payment_method`) VALUES
(1, 2, 2, '2025-04-02', '2025-04-24', 57500.00, 'confirmed', '2025-03-31 14:05:28', 'credit_card'),
(2, 2, 2, '2025-04-04', '2025-04-17', 35000.00, 'confirmed', '2025-03-31 14:09:56', 'credit_card'),
(3, 1, 1, '2025-04-03', '2025-04-04', 7000.00, 'confirmed', '2025-04-02 12:17:55', 'credit_card'),
(4, 2, 7, '2025-04-03', '2025-04-21', 95000.00, 'confirmed', '2025-04-02 12:46:29', 'credit_card'),
(5, 1, 1, '2025-04-10', '2025-04-17', 28000.00, 'confirmed', '2025-04-02 16:31:12', 'upi');

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(11) DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `fuel_type` varchar(20) DEFAULT NULL,
  `mileage` varchar(20) DEFAULT NULL,
  `seats` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT 'default-car.jpg',
  `available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `make`, `model`, `year`, `price_per_day`, `fuel_type`, `mileage`, `seats`, `description`, `image_path`, `available`) VALUES
(1, 'Honda', 'Civic', 2018, 3500.00, 'petrol', '16', 5, ' A well-maintained sedan with a smooth drive, excellent fuel efficiency, and modern features. Ideal for city and highway travel.', '67e189df2d406.png', 1),
(2, 'Maruti Suzuki', ' Swift', 2022, 2500.00, 'petrol', '22', 5, 'A stylish and fuel-efficient hatchback, perfect for city and highway drives. Comes with modern safety features and a spacious interior.', '67ed69f7e5140.png', 1),
(4, 'Hyundai', 'i20', 2021, 2200.00, 'petrol', '20', 5, 'A premium hatchback with great performance and comfort.', '67ed6a4f32ff3.png', 1),
(5, 'Tata', 'Nexon', 2023, 3000.00, 'diesel', '24', 5, 'A rugged and powerful compact SUV with top safety ratings.', '67ee16aecd78a.png', 1),
(6, 'Honda', 'City', 2020, 3500.00, 'petrol', '18', 5, 'A spacious and luxurious sedan with great ride comfort.', '67ed6acd8e9b5.png', 1),
(7, 'Toyota', 'Innova', 2019, 5000.00, 'petrol', '15', 7, 'A reliable and spacious MPV, ideal for family and long trips.', '67ed6b14ef6f0.png', 1),
(8, 'Mahindra', 'Thar', 2022, 4500.00, 'petrol', '12', 4, 'A rugged off-road SUV designed for adventure lovers.', '67ed6b72e0134.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read','replied') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `user_id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`, `status`) VALUES
(1, NULL, 'singh', 'singh@gmail.com', '', 'hi', 'i want a car', '2025-04-02 17:39:24', 'unread'),
(2, NULL, 'anika', 'anika@gmail.com', '9431821341', 'i want a car', 'a seden', '2025-04-02 18:17:58', 'unread'),
(3, NULL, 'devesh', 'devesh@gmail.com', '9235856256', 'i want a car for a longer period', 'nano is the car i need', '2025-04-03 05:00:29', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `is_admin`, `created_at`) VALUES
(1, 'singh', 'singh@gmail.com', '$2y$10$XroHcfnG0szANqVFKbZBPOnUgxWnkjDrlsOLDaZYloex248VUOtma', 'GD', '9834402736', 1, '2025-03-24 16:25:13'),
(2, 'ravi', 'ravi@gmail.com', '$2y$10$xUdpTaGgvgfxGiRYEXcjEO9iHF5ANlxO.zwGSbdYGec0pqH3VkfPO', 'ravi', '8958329534', 0, '2025-03-24 16:37:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`);

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
