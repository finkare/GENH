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

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `cast` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `ott_rights` VARCHAR(255) DEFAULT 'OTT Rights | Tamil Language',
  `returns_range` VARCHAR(100) NOT NULL,
  `tenure_months` INT UNSIGNED NOT NULL,
  `min_investment` DECIMAL(12, 2) NOT NULL,
  `management_fee` DECIMAL(10, 2) NOT NULL,
  `trailer_url` VARCHAR(255) NULL,
  `poster_image_url` VARCHAR(255) NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE, -- To control which projects are shown on the frontend
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dummy data for table `projects`
--
-- In a real application, these URLs would point to actual video and image files.
-- For now, we can use placeholders.
INSERT INTO `projects`
  (`title`, `cast`, `description`, `returns_range`, `tenure_months`, `min_investment`, `management_fee`, `trailer_url`, `poster_image_url`)
VALUES
  ('Project Alpha', 'Actor A, Actress B', '🎯 A perfect short-term, high-potential OTT investment!', '5% – 10% Returns Every Month', 3, 50000.00, 140.00, NULL, NULL),
  ('Project Beta', 'Actor C, Actor D', '🚀 Seats are filling fast – Secure your ticket today!', '6% – 9% Returns Every Month', 4, 75000.00, 160.00, NULL, NULL),
  ('Project Gamma', 'Actress E, Actor F', '🎯 A perfect short-term, high-potential OTT investment!', '7% – 11% Returns Every Month', 5, 100000.00, 180.00, NULL, NULL);
