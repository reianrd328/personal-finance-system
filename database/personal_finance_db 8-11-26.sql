-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 11, 2026 at 02:14 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `personal_finance_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Income','Expense') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `user_id`, `name`, `type`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Salary', 'Income', 'Salary or regular employment income', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(2, NULL, 'Freelance', 'Income', 'Freelance or project income', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(3, NULL, 'Business', 'Income', 'Business income', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(4, NULL, 'Other Income', 'Income', 'Other sources of income', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(5, NULL, 'Food', 'Expense', 'Meals, snacks and food', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(6, NULL, 'Transportation', 'Expense', 'Transportation and commuting', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(7, NULL, 'Groceries', 'Expense', 'Grocery and household purchases', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(8, NULL, 'Bills & Utilities', 'Expense', 'Electricity, water, internet and other bills', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(9, NULL, 'Rent', 'Expense', 'Rent or housing expenses', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(10, NULL, 'Shopping', 'Expense', 'General shopping', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(11, NULL, 'Entertainment', 'Expense', 'Movies, games and entertainment', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(12, NULL, 'Health', 'Expense', 'Medical and health-related expenses', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(13, NULL, 'Education', 'Expense', 'School, courses and educational expenses', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(14, NULL, 'Personal', 'Expense', 'Personal expenses', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02'),
(15, NULL, 'Other Expense', 'Expense', 'Other expenses', 'Active', '2026-08-08 05:28:02', '2026-08-08 05:28:02');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `wallet_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `type` enum('Income','Expense','Transfer','Adjustment') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `wallet_id`, `category_id`, `type`, `amount`, `transaction_date`, `description`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 4, 4, 'Income', 1000.00, '2026-08-08', 'Test income', 'First transaction test', '2026-08-08 08:33:15', '2026-08-08 08:33:15'),
(3, 2, 5, 15, 'Expense', 100.00, '2026-08-08', 'Test Expense', 'First expense test', '2026-08-08 08:45:23', '2026-08-08 08:45:23'),
(4, 1, 2, NULL, 'Transfer', 500.00, '2026-08-08', 'test transfer', 'first test transfer', '2026-08-08 12:42:37', '2026-08-08 12:42:37'),
(5, 1, 3, NULL, 'Transfer', 500.00, '2026-08-08', 'test transfer', 'first test transfer', '2026-08-08 12:42:37', '2026-08-08 12:42:37'),
(6, 1, 3, 15, 'Expense', 100.00, '2026-08-08', 'Test Expense', 'First Test Expense', '2026-08-08 12:58:17', '2026-08-08 12:58:17'),
(7, 1, 2, 1, 'Income', 2000.00, '2026-08-08', 'Test income', 'First Test income', '2026-08-08 13:00:04', '2026-08-08 13:00:04'),
(8, 1, 3, NULL, 'Transfer', 900.00, '2026-08-08', 'test transfer1', 'test transfer1', '2026-08-08 13:03:49', '2026-08-08 13:03:49'),
(9, 1, 2, NULL, 'Transfer', 900.00, '2026-08-08', 'test transfer1', 'test transfer1', '2026-08-08 13:03:49', '2026-08-08 13:03:49'),
(10, 2, 5, NULL, 'Transfer', 400.00, '2026-08-08', '', '', '2026-08-08 13:33:08', '2026-08-08 13:33:08'),
(11, 2, 4, NULL, 'Transfer', 400.00, '2026-08-08', '', '', '2026-08-08 13:33:08', '2026-08-08 13:33:08'),
(17, 1, 3, NULL, 'Adjustment', 200.00, '2026-08-08', 'Test Increase Balance', 'Test Increase Balance', '2026-08-08 15:15:38', '2026-08-08 15:15:38'),
(18, 3, 10, 5, 'Expense', 75.00, '2026-08-10', 'Lunch', 'Lunch for today', '2026-08-10 08:41:45', '2026-08-10 08:41:45'),
(19, 3, 10, NULL, 'Adjustment', 75.00, '2026-08-10', 'for lunch', 'Lunch', '2026-08-10 08:43:25', '2026-08-10 08:43:25'),
(20, 1, 3, 5, 'Expense', 100.00, '2026-08-10', 'Piatos', 'Piatos', '2026-08-10 10:46:50', '2026-08-10 10:46:50');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_transfers`
--

CREATE TABLE `transaction_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `destination_transaction_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_transfers`
--

INSERT INTO `transaction_transfers` (`id`, `transaction_id`, `destination_transaction_id`, `created_at`) VALUES
(1, 4, 5, '2026-08-08 12:42:37'),
(2, 8, 9, '2026-08-08 13:03:49'),
(3, 10, 11, '2026-08-08 13:33:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','User') NOT NULL DEFAULT 'User',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin', 'admin@example.com', '$2y$10$IAz/f3c626iHGwMqG2IhJeI/pNqYU4k5L9otvsoQkw4cgwm2o7vuO', 'Admin', 'Active', '2026-08-08 05:57:32', '2026-08-08 05:57:32'),
(2, 'Test user', 'testuser', 'testuser@example.com', '$2y$10$y0LdSKreLvm7xBiRdVksDOHH/SgZvbqLcCIOF5H4MME6oZBXnSET2', 'User', 'Active', '2026-08-08 06:22:41', '2026-08-08 06:22:41'),
(3, 'Francis Test User', 'francis', 'na@walaemail.com', '$2y$10$m91e2jrMlxoxfxY4Icg9ze45Ug/yZ4a9eZRulDi3ipVguNAVhghjK', 'Admin', 'Active', '2026-08-10 07:38:46', '2026-08-10 07:38:46'),
(4, 'Ian jay largozq', 'Iannjay', 'largozaianjay@gmail.com', '$2y$10$k.IWlJwpIzCPBn1F20cB5.lZYkoGUuGJx4SnKusWDCWPJO4lz5gb.', 'User', 'Active', '2026-08-10 10:44:09', '2026-08-10 10:44:09'),
(5, 'Franze test user1', 'franze', 'gmail@gmail.com', '$2y$10$ew636vpX9haL5u9CIsQ51OQwJLeJqa81E7Rkgx/hEQCA64RRsRYQO', 'User', 'Active', '2026-08-10 13:34:39', '2026-08-10 13:34:39');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `wallet_type_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `initial_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'PHP',
  `status` enum('Active','Archived') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `wallet_type_id`, `name`, `description`, `initial_balance`, `currency`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Gcash', NULL, 10000.00, 'PHP', 'Archived', '2026-08-08 07:04:42', '2026-08-08 07:08:08'),
(2, 1, 1, 'BDO', NULL, 22500.00, 'PHP', 'Active', '2026-08-08 07:05:44', '2026-08-08 07:05:44'),
(3, 1, 4, 'Gcash', NULL, 1500.00, 'PHP', 'Active', '2026-08-08 07:21:18', '2026-08-08 07:23:40'),
(4, 2, 1, 'BPI', NULL, 10250.00, 'PHP', 'Active', '2026-08-08 07:24:54', '2026-08-08 07:24:54'),
(5, 2, 3, 'Wallet Cash', NULL, 3500.00, 'PHP', 'Active', '2026-08-08 07:25:19', '2026-08-08 07:25:19'),
(6, 3, 6, 'Maribank (pera ni mami)', 'Pera ni mami sa maribank ko', 482.38, 'PHP', 'Archived', '2026-08-10 07:44:12', '2026-08-10 13:32:24'),
(7, 3, 6, 'Gcash (pera ni kuya ken sa gcash ko)', 'pera ni kuya ken sa gcash ko', 7265.00, 'PHP', 'Archived', '2026-08-10 07:45:20', '2026-08-10 13:32:20'),
(8, 3, 6, 'Maribank (pera ni ayey)', 'Pera ni ayey sa maribank ko (pang spaylater)', 2601.00, 'PHP', 'Archived', '2026-08-10 07:46:23', '2026-08-10 13:32:13'),
(9, 3, 1, 'Maribank (own money)', 'My own money', 13714.19, 'PHP', 'Archived', '2026-08-10 07:49:03', '2026-08-10 13:32:07'),
(10, 3, 2, 'Gcash (Own money)', 'My own money', 720.69, 'PHP', 'Archived', '2026-08-10 07:50:25', '2026-08-10 13:32:04'),
(11, 3, 6, 'Physical wallet(1k and 500)', '1k and 500 on my wallet', 1000.00, 'PHP', 'Archived', '2026-08-10 07:52:17', '2026-08-10 13:31:57'),
(12, 3, 2, 'Gotyme', 'My gotyme', 10.00, 'PHP', 'Archived', '2026-08-10 07:52:48', '2026-08-10 13:31:53'),
(13, 5, 6, 'Salary', 'From salary', 7562.50, 'PHP', 'Active', '2026-08-10 13:37:40', '2026-08-10 13:37:40');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_types`
--

CREATE TABLE `wallet_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_types`
--

INSERT INTO `wallet_types` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Bank Account', 'Traditional bank account', 'Active', '2026-08-08 05:24:30', '2026-08-08 05:24:30'),
(2, 'E-Wallet', 'Electronic wallet such as GCash or Maya', 'Active', '2026-08-08 05:24:30', '2026-08-08 05:24:30'),
(3, 'Cash', 'Physical cash wallet', 'Active', '2026-08-08 05:24:30', '2026-08-08 05:24:30'),
(4, 'Credit Card', 'Credit card account', 'Active', '2026-08-08 05:24:30', '2026-08-08 05:24:30'),
(5, 'Investment', 'Investment or savings account', 'Active', '2026-08-08 05:24:30', '2026-08-08 05:24:30'),
(6, 'Other', 'Other type of financial account', 'Active', '2026-08-08 05:24:30', '2026-08-08 05:24:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_user` (`user_id`),
  ADD KEY `idx_category_type` (`type`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaction_user` (`user_id`),
  ADD KEY `idx_transaction_wallet` (`wallet_id`),
  ADD KEY `idx_transaction_category` (`category_id`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_transaction_type` (`type`);

--
-- Indexes for table `transaction_transfers`
--
ALTER TABLE `transaction_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_transfer_transaction` (`transaction_id`),
  ADD UNIQUE KEY `uq_transfer_destination` (`destination_transaction_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wallet_user` (`user_id`),
  ADD KEY `idx_wallet_type` (`wallet_type_id`);

--
-- Indexes for table `wallet_types`
--
ALTER TABLE `wallet_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `transaction_transfers`
--
ALTER TABLE `transaction_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `wallet_types`
--
ALTER TABLE `wallet_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_category_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transaction_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transactions_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transactions_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaction_transfers`
--
ALTER TABLE `transaction_transfers`
  ADD CONSTRAINT `fk_transfer_destination` FOREIGN KEY (`destination_transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transfer_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `fk_wallet_type` FOREIGN KEY (`wallet_type_id`) REFERENCES `wallet_types` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wallet_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
