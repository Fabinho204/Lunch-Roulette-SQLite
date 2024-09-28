-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `siemens_lunch_roulette`
--
-- ------------------------------------------------------- --

--
-- TABLE `roulette_winners`
--

CREATE TABLE IF NOT EXISTS `roulette_winners` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user1` INT(11) NOT NULL,
    `user2` INT(11) NOT NULL,
    `user3` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP (),
	PRIMARY KEY (`id`)
)  ENGINE=INNODB DEFAULT CHARSET=UTF8 COLLATE = UTF8_UNICODE_CI;

--
-- TABLE `users`
--

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `participate_in_roulette` INT(11) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP (),
     PRIMARY KEY (`id`)
)  ENGINE=INNODB DEFAULT CHARSET=UTF8 COLLATE = UTF8_UNICODE_CI;

--
-- TABLE `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `password` VARCHAR(300) NOT NULL,
    `participate_in_roulette` TINYINT(1) NOT NULL,
    `isAdmin` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP (),
    PRIMARY KEY (`id`)
)  ENGINE=INNODB DEFAULT CHARSET=UTF8 COLLATE = UTF8_UNICODE_CI;

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(255) NOT NULL DEFAULT 'admin_registered',
    `setting_value` VARCHAR(255) NOT NULL DEFAULT 'no',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Update initial setting for admin registration
SET SQL_SAFE_UPDATES = 0;
INSERT INTO settings (setting_key, setting_value) VALUES ('admin_registered', 'no');
UPDATE settings SET setting_value = 'no' WHERE setting_key = 'admin_registered';

--
-- TABLE `current_roulette`
--

CREATE TABLE IF NOT EXISTS `current_roulette` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user1` int(11) NOT NULL,
  `user2` int(11) NOT NULL,
  `user3` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Select statements to verify data (for manual checks)
SELECT * FROM users;
SELECT * FROM settings;
SELECT * FROM admins;
SELECT * FROM roulette_winners;
SELECT * FROM current_roulette;

-- Truncate tables (if needed to reset)
TRUNCATE TABLE users;
TRUNCATE TABLE admins;
TRUNCATE TABLE roulette_winners;
TRUNCATE TABLE settings;
TRUNCATE TABLE current_roulette;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
