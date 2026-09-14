-- ==============================================================
-- SCHOOL OF CRIMINAL JUSTICE EDUCATION (SCJE) INFORMATION SYSTEM
-- MySQL Database Schema
-- ==============================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_number` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(30) NOT NULL DEFAULT 'student',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `research` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `author_name` VARCHAR(150) NOT NULL,
    `research_title` VARCHAR(300) NOT NULL,
    `month_year` VARCHAR(50) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `abstract` TEXT NULL,
    `keywords` VARCHAR(255) NULL,
    `status` VARCHAR(50) DEFAULT 'Published',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `equipment` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `equipment_code` VARCHAR(50) NOT NULL UNIQUE,
    `equipment_name` VARCHAR(150) NOT NULL,
    `brand` VARCHAR(100) NOT NULL,
    `model` VARCHAR(100) NOT NULL,
    `current_location` VARCHAR(150) NOT NULL,
    `laboratory_category` VARCHAR(100) NOT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'Good Condition',
    `serial_number` VARCHAR(100) NULL,
    `description` TEXT NULL,
    `person_accountable` VARCHAR(150) NULL DEFAULT 'Sir Jom',
    `date_acquired` DATE NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `materials_chemicals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `item_code` VARCHAR(50) NOT NULL UNIQUE,
    `qty` VARCHAR(50) NULL,
    `unit` VARCHAR(50) NULL,
    `item_name` VARCHAR(200) NOT NULL,
    `person_accountable` VARCHAR(150) NULL DEFAULT 'Sir Jom',
    `brand` VARCHAR(100) NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'Good Condition',
    `location` VARCHAR(150) NOT NULL DEFAULT 'Crime Laboratory',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `faculty` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `position` VARCHAR(150) NOT NULL,
    `role_level` VARCHAR(50) NOT NULL DEFAULT 'faculty',
    `email` VARCHAR(150) NULL,
    `specialization` VARCHAR(255) NULL,
    `research_interests` VARCHAR(255) NULL,
    `office_location` VARCHAR(150) NULL,
    `photo_url` VARCHAR(255) NULL,
    `order_index` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'unread',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `site_content` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `section_key` VARCHAR(50) NOT NULL UNIQUE,
    `title` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
