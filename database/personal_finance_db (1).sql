-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 09, 2026 at 01:05 PM
-- Server version: 8.0.31
-- PHP Version: 8.0.26

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

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Income','Expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category_user` (`user_id`),
  KEY `idx_category_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `wallet_id` int UNSIGNED NOT NULL,
  `category_id` int UNSIGNED DEFAULT NULL,
  `type` enum('Income','Expense','Transfer','Adjustment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transaction_user` (`user_id`),
  KEY `idx_transaction_wallet` (`wallet_id`),
  KEY `idx_transaction_category` (`category_id`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_transaction_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `wallet_id`, `category_id`, `type`, `amount`, `transaction_date`, `description`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 4, 4, 'Income', '1000.00', '2026-08-08', 'Test income', 'First transaction test', '2026-08-08 08:33:15', '2026-08-08 08:33:15'),
(3, 2, 5, 15, 'Expense', '100.00', '2026-08-08', 'Test Expense', 'First expense test', '2026-08-08 08:45:23', '2026-08-08 08:45:23'),
(4, 1, 2, NULL, 'Transfer', '500.00', '2026-08-08', 'test transfer', 'first test transfer', '2026-08-08 12:42:37', '2026-08-08 12:42:37'),
(5, 1, 3, NULL, 'Transfer', '500.00', '2026-08-08', 'test transfer', 'first test transfer', '2026-08-08 12:42:37', '2026-08-08 12:42:37'),
(6, 1, 3, 15, 'Expense', '100.00', '2026-08-08', 'Test Expense', 'First Test Expense', '2026-08-08 12:58:17', '2026-08-08 12:58:17'),
(7, 1, 2, 1, 'Income', '2000.00', '2026-08-08', 'Test income', 'First Test income', '2026-08-08 13:00:04', '2026-08-08 13:00:04'),
(8, 1, 3, NULL, 'Transfer', '900.00', '2026-08-08', 'test transfer1', 'test transfer1', '2026-08-08 13:03:49', '2026-08-08 13:03:49'),
(9, 1, 2, NULL, 'Transfer', '900.00', '2026-08-08', 'test transfer1', 'test transfer1', '2026-08-08 13:03:49', '2026-08-08 13:03:49'),
(10, 2, 5, NULL, 'Transfer', '400.00', '2026-08-08', '', '', '2026-08-08 13:33:08', '2026-08-08 13:33:08'),
(11, 2, 4, NULL, 'Transfer', '400.00', '2026-08-08', '', '', '2026-08-08 13:33:08', '2026-08-08 13:33:08'),
(17, 1, 3, NULL, 'Adjustment', '200.00', '2026-08-08', 'Test Increase Balance', 'Test Increase Balance', '2026-08-08 15:15:38', '2026-08-08 15:15:38');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_transfers`
--

DROP TABLE IF EXISTS `transaction_transfers`;
CREATE TABLE IF NOT EXISTS `transaction_transfers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint UNSIGNED NOT NULL,
  `destination_transaction_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_transfer_transaction` (`transaction_id`),
  UNIQUE KEY `uq_transfer_destination` (`destination_transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('Admin','User') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'User',
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin', 'admin@example.com', '$2y$10$IAz/f3c626iHGwMqG2IhJeI/pNqYU4k5L9otvsoQkw4cgwm2o7vuO', 'Admin', 'Active', '2026-08-08 05:57:32', '2026-08-08 05:57:32'),
(2, 'Test user', 'testuser', 'testuser@example.com', '$2y$10$y0LdSKreLvm7xBiRdVksDOHH/SgZvbqLcCIOF5H4MME6oZBXnSET2', 'User', 'Active', '2026-08-08 06:22:41', '2026-08-08 06:22:41');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

DROP TABLE IF EXISTS `wallets`;
CREATE TABLE IF NOT EXISTS `wallets` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `wallet_type_id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `initial_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PHP',
  `status` enum('Active','Archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_wallet_user` (`user_id`),
  KEY `idx_wallet_type` (`wallet_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `wallet_type_id`, `name`, `description`, `initial_balance`, `currency`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Gcash', NULL, '10000.00', 'PHP', 'Archived', '2026-08-08 07:04:42', '2026-08-08 07:08:08'),
(2, 1, 1, 'BDO', NULL, '22500.00', 'PHP', 'Active', '2026-08-08 07:05:44', '2026-08-08 07:05:44'),
(3, 1, 4, 'Gcash', NULL, '1500.00', 'PHP', 'Active', '2026-08-08 07:21:18', '2026-08-08 07:23:40'),
(4, 2, 1, 'BPI', NULL, '10250.00', 'PHP', 'Active', '2026-08-08 07:24:54', '2026-08-08 07:24:54'),
(5, 2, 3, 'Wallet Cash', NULL, '3500.00', 'PHP', 'Active', '2026-08-08 07:25:19', '2026-08-08 07:25:19');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_types`
--

DROP TABLE IF EXISTS `wallet_types`;
CREATE TABLE IF NOT EXISTS `wallet_types` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  ADD CONSTRAINT `fk_transaction_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
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
  ADD CONSTRAINT `fk_wallet_type` FOREIGN KEY (`wallet_type_id`) REFERENCES `wallet_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wallet_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
