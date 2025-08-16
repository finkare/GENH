-- Flix9 Hub Database Schema
-- Version 1.0

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `contact_address` TEXT,
  `city` VARCHAR(100),
  `state` VARCHAR(100),
  `country` VARCHAR(100),
  `id_front_path` VARCHAR(255) NOT NULL,
  `id_back_path` VARCHAR(255) DEFAULT NULL, -- Optional, as a passport might only have one side
  `otp` VARCHAR(10) DEFAULT NULL,
  `otp_expires_at` DATETIME DEFAULT NULL,
  `is_verified` BOOLEAN NOT NULL DEFAULT FALSE, -- Tracks if the user has verified their email via OTP
  `is_active` BOOLEAN NOT NULL DEFAULT FALSE, -- To be enabled by an admin for Indian investors before they can invest
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- More tables for investments, transactions, etc., will be added later.
