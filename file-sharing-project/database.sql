-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 13, 2026 at 08:32 AM
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
-- Database: `file_sharing`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$OaAea82TcznvFo50HPZGyOA8B4sfOWqxxTWQoWtOjjx8.mu6Q8Aae', 'active', '2026-08-13 11:36:18', '2026-08-13 05:34:12', '2026-08-13 06:06:18');

-- --------------------------------------------------------

--
-- Table structure for table `downloads`
--

CREATE TABLE `downloads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_id` bigint(20) UNSIGNED NOT NULL,
  `downloaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'success'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(120) NOT NULL,
  `mime_type` varchar(190) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL,
  `sharing_code` varchar(12) NOT NULL,
  `status` enum('active','expired','disabled','deleted','download_limit_reached') NOT NULL DEFAULT 'active',
  `download_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `max_downloads` int(10) UNSIGNED NOT NULL DEFAULT 5,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'website_name', 'MS', '2026-08-13 06:16:43'),
(2, 'website_logo', '', '2026-08-13 05:32:29'),
(3, 'favicon', '', '2026-08-13 05:32:29'),
(4, 'website_description', 'Secure file sharing from any device.', '2026-08-13 05:32:29'),
(5, 'contact_email', '', '2026-08-13 05:32:29'),
(6, 'contact_phone', '', '2026-08-13 05:32:29'),
(7, 'footer_text', 'Secure file sharing made simple.', '2026-08-13 05:32:29'),
(8, 'primary_color', '#FFFFFF', '2026-08-13 06:26:10'),
(9, 'secondary_color', '#000000', '2026-08-13 06:27:29'),
(10, 'background_color', '#412BE3', '2026-08-13 06:27:53'),
(11, 'button_style', 'solid', '2026-08-13 05:32:29'),
(12, 'border_radius', '18px', '2026-08-13 05:32:29'),
(13, 'theme', 'dark', '2026-08-13 06:26:43'),
(14, 'custom_css', '', '2026-08-13 05:32:29'),
(15, 'hero_heading', 'Send Files. Anywhere. Securely.', '2026-08-13 05:32:29'),
(16, 'hero_paragraph', 'Upload a file, get a secure sharing code, and download it from anywhere on the Internet.', '2026-08-13 05:32:29'),
(17, 'hero_send_text', 'Send File', '2026-08-13 05:32:29'),
(18, 'hero_receive_text', 'Receive File', '2026-08-13 05:32:29'),
(19, 'max_file_size_mb', '100', '2026-08-13 05:32:29'),
(20, 'allowed_extensions', 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,gif,webp,zip,rar,7z,mp3,wav,mp4,mov,mkv', '2026-08-13 05:32:29'),
(21, 'max_downloads', '5', '2026-08-13 05:32:29'),
(22, 'code_length', '6', '2026-08-13 05:32:29'),
(23, 'code_expiry_hours', '24', '2026-08-13 05:32:29'),
(24, 'auto_delete_expired', '1', '2026-08-13 06:19:16'),
(25, 'maintenance_mode', '0', '2026-08-13 05:32:29'),
(26, 'accounts_required', '0', '2026-08-13 05:32:29'),
(27, 'upload_enabled', '1', '2026-08-13 05:32:29'),
(28, 'download_enabled', '1', '2026-08-13 05:32:29'),
(29, 'how_steps', '[{\"title\":\"Upload File\",\"text\":\"Select a file and upload it securely.\"},{\"title\":\"Share Code\",\"text\":\"Get a secure code with expiry and download limits.\"},{\"title\":\"Download Anywhere\",\"text\":\"Enter the code on another device and download.\"}]', '2026-08-13 05:32:29'),
(30, 'features', '[{\"title\":\"Fast sharing\",\"text\":\"Simple direct uploads with real browser progress.\",\"icon\":\"fa-bolt\"},{\"title\":\"Anywhere online\",\"text\":\"Sender and receiver do not need the same network.\",\"icon\":\"fa-globe\"},{\"title\":\"Secure download\",\"text\":\"Files are streamed through a protected endpoint.\",\"icon\":\"fa-shield-halved\"},{\"title\":\"Expiring shares\",\"text\":\"Old shares stop working automatically.\",\"icon\":\"fa-clock\"}]', '2026-08-13 05:32:29'),
(31, 'faq_items', '[{\"q\":\"Do users need an account?\",\"a\":\"No. Normal sending and receiving are account-free unless enabled by the administrator.\"},{\"q\":\"Does it require the same Wi-Fi?\",\"a\":\"No. Both devices can be anywhere with Internet access.\"},{\"q\":\"Are files public?\",\"a\":\"No. Downloads are handled by a protected PHP endpoint.\"}]', '2026-08-13 05:32:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `downloads`
--
ALTER TABLE `downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_downloads_file` (`file_id`),
  ADD KEY `idx_downloads_time` (`downloaded_at`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stored_name` (`stored_name`),
  ADD UNIQUE KEY `sharing_code` (`sharing_code`),
  ADD KEY `idx_files_status` (`status`),
  ADD KEY `idx_files_expiry` (`expires_at`),
  ADD KEY `idx_files_created` (`created_at`),
  ADD KEY `idx_files_name` (`original_name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=323;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `downloads`
--
ALTER TABLE `downloads`
  ADD CONSTRAINT `fk_download_file` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
