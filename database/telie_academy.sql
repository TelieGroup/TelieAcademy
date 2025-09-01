-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2025 at 01:45 PM
-- Server version: 10.1.29-MariaDB
-- PHP Version: 7.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `telie_academy`
--

-- --------------------------------------------------------

--
-- Table structure for table `ad_placements`
--

CREATE TABLE `ad_placements` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `ad_code` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ad_placements`
--

INSERT INTO `ad_placements` (`id`, `name`, `location`, `ad_code`, `is_active`, `created_at`) VALUES
(1, 'Homepage Banner', 'homepage', '<div class=\"ad-banner\" id=\"homepage-banner-ad\"><!-- AdSense code here --></div>', 1, '2025-08-04 19:01:18'),
(2, 'Categories Banner', 'categories', '<div class=\"ad-banner\" id=\"categories-banner-ad\"><!-- AdSense code here --></div>', 1, '2025-08-04 19:01:18'),
(3, 'Tags Banner', 'tags', '<div class=\"ad-banner\" id=\"tags-banner-ad\"><!-- AdSense code here --></div>', 1, '2025-08-04 19:01:18'),
(4, 'Post In-Content', 'post-content', '<div class=\"ad-banner\" id=\"post-content-ad\"><!-- AdSense code here --></div>', 1, '2025-08-04 19:01:18'),
(5, 'Post Footer', 'post-footer', '<div class=\"ad-banner\" id=\"post-footer-ad\"><!-- AdSense code here --></div>', 1, '2025-08-04 19:01:18');

-- --------------------------------------------------------

--
-- Table structure for table `affiliate_products`
--

CREATE TABLE `affiliate_products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `image_url` varchar(255) DEFAULT NULL,
  `affiliate_link` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `affiliate_products`
--

INSERT INTO `affiliate_products` (`id`, `name`, `description`, `image_url`, `affiliate_link`, `price`, `category`, `is_active`, `created_at`) VALUES
(1, 'Eloquent JavaScript', 'A modern introduction to programming with JavaScript', '/images/books/eloquent-javascript.jpg', 'https://amzn.to/example1', '29.99', 'Books', 1, '2025-08-04 19:01:17'),
(2, 'React: Up & Running', 'Build web applications with React', '/images/books/react-up-running.jpg', 'https://amzn.to/example2', '34.99', 'Books', 1, '2025-08-04 19:01:17'),
(3, 'Python Crash Course', 'A hands-on introduction to Python programming', '/images/books/python-crash-course.jpg', 'https://amzn.to/example3', '39.99', 'Books', 1, '2025-08-04 19:01:17'),
(4, 'VS Code Pro', 'Professional code editor with advanced features', '/images/tools/vscode-pro.jpg', 'https://example.com/vscode', '0.00', 'Tools', 1, '2025-08-04 19:01:17'),
(5, 'GitHub Pro', 'Advanced features for GitHub repositories', '/images/tools/github-pro.jpg', 'https://github.com/pro', '4.99', 'Tools', 1, '2025-08-04 19:01:17');

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'JavaScript', 'javascript', 'Modern JavaScript tutorials and guides', 'active', '2025-08-04 19:01:17', NULL),
(2, 'React', 'react', 'React.js development tutorials', 'active', '2025-08-04 19:01:17', NULL),
(3, 'Python', 'python', 'Python programming tutorials', 'active', '2025-08-04 19:01:17', NULL),
(4, 'Web Development', 'web-development', 'General web development topics', 'active', '2025-08-04 19:01:17', NULL),
(5, 'Data Science', 'data-science', 'Data science and analytics tutorials.', 'active', '2025-08-04 19:01:17', '2025-08-22 07:36:01'),
(6, 'Business', 'business', '', 'active', '2025-08-28 08:32:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `guest_email` varchar(100) DEFAULT NULL,
  `content` text NOT NULL,
  `status` enum('pending','approved','spam') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reply_count` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comment_replies`
--

CREATE TABLE `comment_replies` (
  `id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `guest_email` varchar(100) DEFAULT NULL,
  `content` text NOT NULL,
  `status` enum('pending','approved','spam') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID of the user who sent the message (if logged in)',
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `newsletter_subscribe` tinyint(1) DEFAULT '0' COMMENT 'Whether user wants to subscribe to newsletter',
  `status` enum('new','in_progress','replied','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'new' COMMENT 'Message status',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'medium' COMMENT 'Message priority',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `admin_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Internal admin notes',
  `assigned_to` int(11) DEFAULT NULL COMMENT 'Admin user ID assigned to handle this message',
  `last_read_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_last_viewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `user_id`, `first_name`, `last_name`, `email`, `phone`, `subject`, `message`, `newsletter_subscribe`, `status`, `priority`, `created_at`, `updated_at`, `admin_notes`, `assigned_to`, `last_read_at`, `user_last_viewed_at`) VALUES
(2, NULL, 'Jane', 'Smith', 'jane.smith@example.com', '+1987654321', 'Technical Support', 'I am unable to access the course materials. Getting an error message.', 0, 'replied', 'high', '2025-08-12 16:24:03', '2025-08-12 16:36:58', NULL, NULL, '2025-08-12 17:05:34', NULL),
(16, NULL, 'TWZEYIMANA', 'Elie', 'teliegroup.co@gmail.com', '0783836399', 'technical', 'Please help me!', 1, 'replied', 'medium', '2025-08-12 16:50:36', '2025-08-12 16:52:31', NULL, NULL, '2025-08-12 17:05:34', NULL),
(17, 16, 'TWZEYIMANA', 'Elie', 'teliegroup.co@gmail.com', '0783836399', 'general', 'Please help me the profile picture is not updating', 0, 'replied', 'medium', '2025-08-15 09:21:28', '2025-08-21 13:11:44', NULL, 16, '2025-08-15 09:21:28', '2025-08-21 15:11:44'),
(18, 12, 'UWERA', 'ALINE', 'twizeyimana1elia@gmail.com', '0781950604', 'general', 'Please help us', 0, 'replied', 'medium', '2025-08-15 09:43:14', '2025-08-29 10:30:55', NULL, 16, '2025-08-15 09:43:14', '2025-08-29 12:30:55'),
(19, 6, 'Moise', 'MUSIRIMU', 'musirimumoses2021@gmail.com', '0781950604', 'course', 'Please provide more content.', 1, 'replied', 'medium', '2025-08-21 12:50:17', '2025-08-21 16:19:24', NULL, 16, '2025-08-21 12:50:17', '2025-08-21 18:19:24'),
(20, 6, 'Moise', 'MUSIRIMU', 'musirimumoses2021@gmail.com', '0781950604', 'course', 'Still waiting', 1, 'replied', 'medium', '2025-08-21 13:11:23', '2025-08-21 16:19:24', NULL, 16, '2025-08-21 13:11:23', '2025-08-21 18:19:24'),
(21, 6, 'Moise', 'MUSIRIMU', 'musirimumoses2021@gmail.com', NULL, 'general', 'bjhbjbjhsfc', 1, 'replied', 'medium', '2025-08-21 13:15:39', '2025-08-21 16:19:24', NULL, 16, '2025-08-21 13:15:39', '2025-08-21 18:19:24');

-- --------------------------------------------------------

--
-- Table structure for table `contact_replies`
--

CREATE TABLE `contact_replies` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL COMMENT 'ID of the contact message being replied to',
  `admin_id` int(11) NOT NULL COMMENT 'ID of the admin who sent the reply',
  `reply_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_internal` tinyint(1) DEFAULT '0' COMMENT 'Whether this is an internal note (not sent to user)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_replies`
--

INSERT INTO `contact_replies` (`id`, `message_id`, `admin_id`, `reply_message`, `is_internal`, `created_at`) VALUES
(11, 17, 16, 'You can try again; the problem has been fixed.', 0, '2025-08-15 09:41:11'),
(12, 18, 16, 'Okay try again.', 0, '2025-08-15 09:43:47'),
(13, 18, 16, 'Is it working?', 0, '2025-08-15 09:44:24'),
(14, 18, 16, 'Hello!', 0, '2025-08-15 09:49:11'),
(15, 19, 16, 'Very soon!', 0, '2025-08-21 12:52:22'),
(16, 18, 16, '\r\nok', 0, '2025-08-21 12:53:56'),
(17, 18, 16, '\r\nok', 0, '2025-08-21 12:56:20'),
(18, 19, 16, 'Fine', 0, '2025-08-21 12:56:32'),
(19, 19, 16, 'Fine', 0, '2025-08-21 13:01:42'),
(20, 21, 16, 'Okay', 0, '2025-08-21 13:22:26'),
(21, 20, 16, 'Next week!', 0, '2025-08-21 13:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `thumbnail` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `slug`, `description`, `thumbnail`, `is_active`, `created_at`, `updated_at`) VALUES
(7, 'Software Development(SOD)', 'software-development(sod)', 'Software development course materials', '../uploads/course_covers/68a57987887b0_cover_SOD.png', 1, '2025-08-20 07:30:15', '2025-08-28 15:02:41');

-- --------------------------------------------------------

--
-- Table structure for table `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `course_enrollments`
--

INSERT INTO `course_enrollments` (`id`, `user_id`, `course_id`, `enrolled_at`, `completed_at`, `is_active`) VALUES
(2, 16, 7, '2025-08-27 10:26:31', '2025-09-01 06:46:17', 1),
(3, 6, 7, '2025-09-01 11:36:47', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `course_materials`
--

CREATE TABLE `course_materials` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `cover_image_path` varchar(500) DEFAULT NULL,
  `download_count` int(11) DEFAULT '0',
  `preview_count` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `required_lesson_id` int(11) DEFAULT NULL COMMENT 'Post ID that must be completed to access this material',
  `related_lesson_id` int(11) DEFAULT NULL COMMENT 'Post ID this material is related to',
  `order_index` int(11) DEFAULT '0' COMMENT 'Order of material within module'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `course_materials`
--

INSERT INTO `course_materials` (`id`, `module_id`, `title`, `description`, `file_name`, `file_path`, `file_size`, `file_type`, `cover_image`, `cover_image_path`, `download_count`, `preview_count`, `is_active`, `created_at`, `updated_at`, `required_lesson_id`, `related_lesson_id`, `order_index`) VALUES
(8, 26, 'Organize a business', 'This module covers the skills, knowledge and attitude to organize a business which is linked to \r\norganizational strategic outcomes and facilitates the achievement of service delivery.', 'L5-SOD-Organise a Business.pdf', '../uploads/course_materials/68a58fb1c6898_L5-SOD-Organise a Business.pdf', 1680384, 'pdf', 'Screenshot 2025-08-20 110430.png', '../uploads/material_covers/68a58fb1c698b_cover_Screenshot 2025-08-20 110430.png', 3, 1, 1, '2025-08-20 09:04:49', '2025-09-01 11:22:56', NULL, NULL, 0),
(11, 24, 'Create a Business', 'This module covers the skills, knowledge and attitudes needed to create a business. It \r\nwill describe the necessary skills, knowledge and right attitude required to Describe basic \r\naspects of Entrepreneurship, Assess Business Environment and Generate Business Idea.', 'L3-SOD-Create a Business .pdf', '../uploads/course_materials/68b04fa673bca_L3-SOD-Create a Business .pdf', 828245, 'pdf', 'Screenshot 2025-08-20 104050.png', '../uploads/material_covers/68b04fa673c66_cover_Screenshot 2025-08-20 104050.png', 13, 1, 1, '2025-08-28 12:46:30', '2025-09-01 11:23:58', 34, 34, 0),
(12, 25, 'Develop a business plan', 'This module covers the skills, knowledge and attitude to develop a business plan \r\nwhich is linked to organizational strategic outcomes and facilitates the \r\nachievement of service delivery.', 'L4-SOD-Business plan.pdf', '../uploads/course_materials/68b067fda18f1_L4-SOD-Business plan.pdf', 1537764, 'pdf', 'Screenshot 2025-08-20 110042.png', '../uploads/material_covers/68b067fda19d5_cover_Screenshot 2025-08-20 110042.png', 1, 1, 1, '2025-08-28 14:30:21', '2025-09-01 11:40:35', 35, 35, 0);

-- --------------------------------------------------------

--
-- Table structure for table `course_modules`
--

CREATE TABLE `course_modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `order_index` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `course_modules`
--

INSERT INTO `course_modules` (`id`, `course_id`, `title`, `slug`, `description`, `order_index`, `is_active`, `created_at`, `updated_at`) VALUES
(24, 7, 'Level 3', 'level-3', 'Level 3 Software development content materials', 0, 1, '2025-08-20 08:43:15', '2025-08-20 09:05:55'),
(25, 7, 'Level 4', 'level-4', 'Level 4 Software development content materials', 1, 1, '2025-08-20 09:01:01', '2025-08-20 09:01:01'),
(26, 7, 'Level 5', 'level-5', 'Level 5 Software development content materials', 2, 1, '2025-08-20 09:04:49', '2025-08-20 09:04:49');

-- --------------------------------------------------------

--
-- Table structure for table `course_progress`
--

CREATE TABLE `course_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `progress_percentage` decimal(5,2) DEFAULT '0.00',
  `time_spent_minutes` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `course_progress`
--

INSERT INTO `course_progress` (`id`, `user_id`, `course_id`, `module_id`, `post_id`, `completed_at`, `progress_percentage`, `time_spent_minutes`, `created_at`, `updated_at`) VALUES
(30, 16, 7, 24, 34, '2025-09-01 06:46:17', '74.00', 347, '2025-08-28 08:38:43', '2025-09-01 06:47:47'),
(80, 16, 7, 25, 35, '2025-08-28 14:26:59', '79.00', 21, '2025-08-28 13:30:14', '2025-08-29 14:15:32'),
(112, 16, 7, 26, 36, '2025-08-29 10:24:09', '40.00', 19, '2025-08-28 14:36:16', '2025-08-29 10:26:38'),
(113, 6, 7, 24, 34, '2025-09-01 11:37:06', '0.00', 11, '2025-09-01 11:36:51', '2025-09-01 11:39:06'),
(128, 6, 7, 25, 35, NULL, '85.00', 0, '2025-09-01 11:40:20', '2025-09-01 11:40:55');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('newsletter','notification','premium','test') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'newsletter',
  `sent_at` datetime NOT NULL,
  `status` enum('sent','failed','bounced') COLLATE utf8mb4_unicode_ci DEFAULT 'sent',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `subscriber_id`, `subject`, `type`, `sent_at`, `status`, `error_message`, `created_at`) VALUES
(13, 14, 'System update', 'newsletter', '2025-08-14 13:45:09', 'sent', NULL, '2025-08-14 11:45:09'),
(14, 13, 'System update', 'newsletter', '2025-08-14 13:45:14', 'sent', NULL, '2025-08-14 11:45:14'),
(15, 14, 'Testing clear URL', 'newsletter', '2025-08-21 15:47:37', 'sent', NULL, '2025-08-21 13:47:37'),
(16, 13, 'Testing clear URL', 'newsletter', '2025-08-21 15:47:42', 'sent', NULL, '2025-08-21 13:47:42');

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_logs`
--

CREATE TABLE `email_verification_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `email_verification_logs`
--

INSERT INTO `email_verification_logs` (`id`, `user_id`, `email`, `token`, `expires_at`, `verified_at`, `created_at`) VALUES
(1, 15, 'niyomuhozajeandedieu80@gmail.com', 'ed5161aebb10c53302a0fe76194166952954e47225f4a200514ae88a6f95d54f', '2025-08-15 11:41:14', NULL, '2025-08-14 11:41:14');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `alt_text` text,
  `caption` text,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `filename`, `original_name`, `file_path`, `file_type`, `file_size`, `width`, `height`, `alt_text`, `caption`, `uploaded_by`, `created_at`) VALUES
(1, 'post_img_68a712149aa2c_1755779604.png', 'Screenshot 2025-06-16 073632.png', 'uploads/posts/post_img_68a712149aa2c_1755779604.png', 'image/png', 82177, 1623, 856, 'Screenshot 2025-06-16 073632', 'password recovery', 16, '2025-08-21 12:33:24'),
(2, 'post_img_68a7123524ba7_1755779637.png', 'Screenshot 2025-04-25 075416.png', 'uploads/posts/post_img_68a7123524ba7_1755779637.png', 'image/png', 94681, 1917, 510, 'Screenshot 2025-04-25 075416', '', 16, '2025-08-21 12:33:57'),
(3, 'post_img_68a733abd4a65_1755788203.jpg', 'python.jpg', 'uploads/posts/post_img_68a733abd4a65_1755788203.jpg', 'image/jpeg', 12850, 840, 430, 'python', '', 16, '2025-08-21 14:56:43'),
(4, 'post_img_68a733bb3497d_1755788219.jpg', 'python.jpg', 'uploads/posts/post_img_68a733bb3497d_1755788219.jpg', 'image/jpeg', 12850, 840, 430, 'python', '', 16, '2025-08-21 14:56:59'),
(5, 'post_img_68a73d21c9207_1755790625.jpg', 'python-codes.jpg', 'uploads/posts/post_img_68a73d21c9207_1755790625.jpg', 'image/jpeg', 259741, 1400, 1050, 'python-codes', '', 16, '2025-08-21 15:37:05'),
(6, 'post_img_68b049fc03a39_1756383740.gif', 'Download2BestDownloadGIF.gif', 'uploads/posts/post_img_68b049fc03a39_1756383740.gif', 'image/gif', 21376, 498, 374, 'Download2BestDownloadGIF', '', 16, '2025-08-28 12:22:20'),
(7, 'post_img_68b04c25e6b18_1756384293.png', 'download.png', 'uploads/posts/post_img_68b04c25e6b18_1756384293.png', 'image/png', 4782, 308, 166, 'download', '', 16, '2025-08-28 12:31:33');

-- --------------------------------------------------------

--
-- Table structure for table `media_files`
--

CREATE TABLE `media_files` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_files`
--

INSERT INTO `media_files` (`id`, `filename`, `original_name`, `file_type`, `file_size`, `file_path`, `uploaded_by`, `description`, `alt_text`, `tags`, `uploaded_at`, `updated_at`) VALUES
(1, '689efd6d74c73_1755250029.pdf', 'Machine Learning Notes_1 (2).pdf', 'application/pdf', 385372, 'uploads/689efd6d74c73_1755250029.pdf', 16, 'Machine learning notes Learning outcome one', 'Machine learning notes LO1', '', '2025-08-15 09:27:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_campaigns`
--

CREATE TABLE `newsletter_campaigns` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `template` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'default',
  `status` enum('draft','scheduled','sending','sent','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `target_audience` text COLLATE utf8mb4_unicode_ci,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `total_recipients` int(11) DEFAULT '0',
  `total_sent` int(11) DEFAULT '0',
  `total_delivered` int(11) DEFAULT '0',
  `total_opened` int(11) DEFAULT '0',
  `total_clicked` int(11) DEFAULT '0',
  `total_bounced` int(11) DEFAULT '0',
  `total_unsubscribed` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_sends`
--

CREATE TABLE `newsletter_sends` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `status` enum('pending','sent','delivered','bounced','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT NULL,
  `bounce_reason` text COLLATE utf8mb4_unicode_ci,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subscription_type` enum('newsletter','premium') DEFAULT 'newsletter',
  `is_trial` tinyint(1) DEFAULT '0',
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `premium_started_at` timestamp NULL DEFAULT NULL,
  `premium_expires_at` timestamp NULL DEFAULT NULL,
  `payment_status` enum('pending','active','cancelled','expired') DEFAULT 'active',
  `subscription_notes` text,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `preferences` text,
  `frequency` enum('daily','weekly','monthly') DEFAULT 'weekly',
  `source` varchar(50) DEFAULT 'website',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `verification_token` varchar(255) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `unsubscribe_token` varchar(255) DEFAULT NULL,
  `unsubscribe_confirmation_token` varchar(255) DEFAULT NULL,
  `unsubscribe_reason` varchar(100) DEFAULT NULL,
  `unsubscribe_feedback` text,
  `unsubscribe_requested_at` timestamp NULL DEFAULT NULL,
  `last_email_sent` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `subscribed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `user_id`, `subscription_type`, `is_trial`, `trial_ends_at`, `premium_started_at`, `premium_expires_at`, `payment_status`, `subscription_notes`, `email`, `name`, `preferences`, `frequency`, `source`, `ip_address`, `user_agent`, `verification_token`, `verified_at`, `unsubscribe_token`, `unsubscribe_confirmation_token`, `unsubscribe_reason`, `unsubscribe_feedback`, `unsubscribe_requested_at`, `last_email_sent`, `updated_at`, `is_active`, `subscribed_at`) VALUES
(10, 8, 'newsletter', 0, NULL, '2025-08-12 09:32:11', '2025-09-12 09:32:11', 'active', NULL, 'twizeyimana12elia@gmail.com', 'Elie', '{\"new_tutorials\":true,\"weekly_digest\":true,\"post_notifications\":true,\"trending_content\":true}', 'weekly', 'modal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'f8d2be9b76a82703d3598de6e2d2affd6c143aafa844362e746a90e79bb3461b', NULL, 'e1103835b881ed32571102adf59253ef3729e8a3fb9cd99d49ba7a745838c10c', NULL, NULL, NULL, NULL, NULL, '2025-08-12 15:17:06', 0, '2025-08-12 09:32:11'),
(13, 12, 'premium', 0, NULL, '2025-08-13 21:34:39', '2025-09-13 21:34:39', 'active', NULL, 'twizeyimana1elia@gmail.com', 'Twizeyimana Elie', '{\"new_tutorials\":true,\"weekly_digest\":true,\"post_notifications\":true,\"trending_content\":false}', 'weekly', 'modal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'c8079602058a0ee57cb533d1b27ed5f5c4d8ff706f286aaa37357054370a2645', NULL, '197e0149d132a00dd85a952366e746f67c7dec3ee194efd32609dff9ec18171a', NULL, NULL, NULL, NULL, '2025-08-21 13:47:42', '2025-08-29 10:33:15', 0, '2025-08-13 21:34:39'),
(14, 15, 'premium', 0, NULL, '2025-08-14 11:42:35', '2025-09-14 11:42:35', 'active', NULL, 'niyomuhozajeandedieu80@gmail.com', 'test', '{\"new_tutorials\":true,\"weekly_digest\":true,\"post_notifications\":true,\"trending_content\":true}', 'weekly', 'modal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '728832ecb6d64fe5f13cfd1e40113b4d87d1fc811da95bf36919b827f6828edd', NULL, '27bdf3adf723422cffdbef1f10265955193e2693072d63ec36a9ccbb4813584c', NULL, NULL, NULL, NULL, '2025-08-21 13:47:37', '2025-08-21 13:47:37', 1, '2025-08-14 11:42:35'),
(15, 6, 'premium', 0, NULL, '2025-09-01 11:40:08', '2025-10-01 11:40:08', 'active', NULL, 'musirimumoses2021@gmail.com', 'musirimumoses', '{\"new_tutorials\":true,\"weekly_digest\":true,\"post_notifications\":true,\"trending_content\":true}', 'weekly', 'modal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '1bd30861e514daf385051c59c51f8937994c743ea89ab052789b206a2b63de5b', NULL, 'b7f56d9fd64886696519fab4c0dd553b1a19c20e7bbb9d21760958662b29d6c7', NULL, NULL, NULL, NULL, NULL, '2025-09-01 11:40:08', 1, '2025-08-20 07:03:51'),
(16, 18, 'premium', 0, NULL, '2025-08-21 16:39:15', '2025-09-21 16:39:15', 'active', NULL, 'twizeyimanaelia@gmail.com', 'twizeyimanaelia', '{\"new_tutorials\":true,\"weekly_digest\":true,\"post_notifications\":false,\"trending_content\":false}', 'weekly', 'modal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '0cdbb7c3e17372cfcca1d403b7baad7a21036b8161a3aa994c3d38c64fe89ec5', NULL, 'b23b3704106bfe68b1687a81c1d74bb64d1e3603f79b526086023b520b8be6fd', NULL, NULL, NULL, NULL, NULL, '2025-08-21 16:39:15', 1, '2025-08-21 16:39:15'),
(17, 16, 'premium', 0, NULL, '2025-08-29 17:17:11', '2025-09-29 17:17:11', 'active', NULL, 'teliegroup.co@gmail.com', 'teliegroup', '{\"new_tutorials\":true,\"weekly_digest\":true,\"post_notifications\":true,\"trending_content\":true}', 'monthly', 'modal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '8d98937fac726b236637ae0bee5ea0443180587f532f139005a9087736bea347', NULL, '1780c24e19047815f49813293fb5ba77fa6fd4713d419a85c274ae19973b9fb9', NULL, NULL, NULL, NULL, NULL, '2025-08-29 17:17:11', 1, '2025-08-29 17:17:11');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_templates`
--

CREATE TABLE `newsletter_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_template` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `html_content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `text_content` longtext COLLATE utf8mb4_unicode_ci,
  `variables` text COLLATE utf8mb4_unicode_ci,
  `is_default` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_tokens`
--

CREATE TABLE `oauth_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `provider` enum('linkedin','google','github') NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_logs`
--

CREATE TABLE `password_reset_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `course_module_id` int(11) DEFAULT NULL,
  `lesson_order` int(11) DEFAULT '0',
  `is_premium` tinyint(1) DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0',
  `upvotes` int(11) NOT NULL DEFAULT '0',
  `downvotes` int(11) NOT NULL DEFAULT '0',
  `vote_score` int(11) NOT NULL DEFAULT '0',
  `status` enum('draft','published') DEFAULT 'published',
  `published_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `view_count` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `author_id`, `category_id`, `course_module_id`, `lesson_order`, `is_premium`, `is_featured`, `upvotes`, `downvotes`, `vote_score`, `status`, `published_at`, `created_at`, `updated_at`, `view_count`) VALUES
(34, 'Entrepreneurship - Create a Business - BDCPC301', 'entrepreneurship---create-a-business---bdcpc301', 'This module covers the skills, knowledge and attitudes needed to create a business. It \r\nwill describe the necessary skills, knowledge and right attitude required to Describe basic \r\naspects of Entrepreneurship, Assess Business Environment and Generate Business Idea.', '                                        <p class=\"text-muted\">\r\n\r\n<!--StartFragment--></p><div class=\"WordSection1\"><p class=\"MsoNormal\"><b><span></span></b></p><h1><b>Learning outcome 1: Describe basic aspects of Entrepreneurship</b></h1><div><b><h2>IC.1.1 Explanation of the Concepts Associated with Entrepreneurship</h2><br></b></div><h1><span><span></span></span></h1><p class=\"MsoListParagraph\"><b><span><span></span></span></b></p><h3>1.&nbsp; Definition of Concepts:</h3><div><div class=\"WordSection1\"><p class=\"MsoListParagraph\"><b><span></span></b></p><b>1. Business&nbsp;</b></div><div class=\"WordSection1\">A <b>business</b> is an organization or entity engaged in commercial, industrial, or professional activities to generate profit by providing goods or services to customers.<p class=\"MsoListParagraph\"></p><b>2. Entrepreneurship&nbsp;</b></div><div class=\"WordSection1\"><b>Entrepreneurship</b> is the process of identifying opportunities, taking risks, and creating a new business or venture to meet market needs while aiming for growth and profitability.<p class=\"MsoListParagraph\"><span><span></span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><b></b></p><b>3. Intrapreneurship&nbsp;</b></div><div class=\"WordSection1\"><b>Intrapreneurship</b> refers to entrepreneurial behavior within an existing organization, where employees innovate, take initiative, and drive new projects or products without starting their own company.<p class=\"MsoListParagraph\"><span></span><b></b></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><b><span></span></b></p><b>4. Entrepreneur&nbsp;</b></div><div class=\"WordSection1\">An <b>entrepreneur </b>is an individual who starts and operates a new business, taking financial risks in pursuit of profit and growth. They are innovators and leaders who drive economic change.</div><div class=\"WordSection1\"><p class=\"MsoListParagraph\"><!--[if !supportLists]--><b><span></span></b></p><b>5. Intrapreneur&nbsp;</b></div><div class=\"WordSection1\">An <b>intrapreneur</b> is an employee within a company who acts like an entrepreneur developing new ideas, products, or processes to benefit the organization while leveraging its resources.<p class=\"MsoBodyText\"><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><b><span></span></b></p><b>6. Enterprise&nbsp;</b></div><div class=\"WordSection1\">An <b>enterprise</b> is a business or company, often characterized by innovation, risk-taking, and scalability. It can also refer to a large, ambitious project or initiative.<p class=\"MsoBodyText\"><span></span></p>\r\n\r\n</div>\r\n\r\n<p class=\"MsoListParagraph\"><b><span></span></b></p><b>7. Invention&nbsp;</b></div><div>An <b>invention</b> is the creation of a new product, process, or technology that did not previously exist. It may or may not have immediate commercial value.<p class=\"MsoBodyText\"><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><b><span></span></b></p><b>8. Innovation&nbsp;</b></div><div><b>Innovation</b> is the practical implementation of an invention or idea to improve products, services, or processes, creating value for businesses and customers.<p class=\"MsoBodyText\"><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><b><span></span></b></p><b>9. Creativity&nbsp;</b></div><div><b>Creativity</b> is the ability to generate original and valuable ideas. In business, it fuels problem-solving, product development, and strategic thinking, leading to innovation and competitive advantage.<p class=\"MsoBodyText\"><span></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>These<span> </span>concepts<span> </span>are<span> </span><span>interconnected </span><b>entrepreneurs </b>and <b>intrapreneurs\r\n</b>use <b>creativity </b>to drive <b>innovation</b>, turning<span> </span><b>inventions<span> </span></b>into<span>\r\n</span>viable<span> </span><b>enterprises<span> </span></b>within<span> </span>a<span> </span><b>business<span>\r\n</span></b>ecosystem.</span></p>\r\n\r\n<!--EndFragment-->\r\n\r\n\r\n\r\n<br></div></div><div class=\"WordSection2\"><h3></h3><h3><span><span></span></span></h3><h3>2.&nbsp;&nbsp;Identification&nbsp;of&nbsp;Entrepreneur\'s&nbsp;Characteristics:</h3><span><span></span></span><p class=\"MsoListParagraph\"></p><ul><li><b><span>Creativity</span></b><span>:<span><span>&nbsp;</span></span>Ability<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>think<span><span>&nbsp;</span></span>outside<span><span>&nbsp;</span></span>the<span><span>&nbsp;</span></span>box<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>generate<span><span>&nbsp;</span></span>innovative<span><span>&nbsp;</span></span><span>ideas.</span></span></li><li><b><span>Responsibility</span></b><span>:<span><span>&nbsp;</span></span>Taking<span><span>&nbsp;</span></span>ownership<span><span>&nbsp;</span></span>of<span><span>&nbsp;</span></span>decisions<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span><span>outcomes.</span></span></li><li><b><span>Curious</span></b><span>:<span><span>&nbsp;</span></span>Eager<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>learn<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>explore<span><span>&nbsp;</span></span>new<span><span>&nbsp;</span>opportunities.</span></span></li><li><span><b>Goal-Oriented</b>:<span><span>&nbsp;</span></span>Focused<span><span>&nbsp;</span></span>on<span><span>&nbsp;</span></span>achieving<span><span>&nbsp;</span></span>specific<span><span>&nbsp;</span></span><span>objectives.</span></span></li><li><b><span>Independent</span></b><span>:<span><span>&nbsp;</span></span>Self-reliant<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>able<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>work<span><span>&nbsp;</span></span><span>autonomously.</span></span></li><li><b><span>Risk<span><span>&nbsp;</span></span>Taker</span></b><span>:<span><span>&nbsp;</span></span>Willing<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>take<span><span>&nbsp;</span></span>calculated<span><span>&nbsp;</span></span>risks<span><span>&nbsp;</span></span>for<span><span>&nbsp;</span></span>potential<span><span>&nbsp;</span></span><span>rewards.</span></span></li><li><b><span>Action<span><span>&nbsp;</span></span>Oriented</span></b><span>:<span><span>&nbsp;</span></span>Proactive<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>decisive<span><span>&nbsp;</span></span>in<span><span>&nbsp;</span></span>implementing<span><span>&nbsp;</span></span><span>ideas.</span></span></li><li><b><span>Positive<span><span>&nbsp;</span></span>Attitude</span></b><span>:<span><span>&nbsp;</span></span>Optimistic<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>resilient<span><span>&nbsp;</span></span>in<span><span>&nbsp;</span></span>the<span><span>&nbsp;</span></span>face<span><span>&nbsp;</span></span>of<span><span>&nbsp;</span></span><span>challenges.</span></span></li><li><b><span>Adaptability</span></b><span>:<span><span>&nbsp;</span></span>Ability<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>adjust<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>changing<span><span>&nbsp;</span></span>circumstances,<span><span>&nbsp;</span></span>markets,<span><span>&nbsp;</span></span>and<span>&nbsp;</span><span>environments.</span></span></li><li><b><span>Resilience</span></b><span>:<span><span>&nbsp;</span></span>Capacity<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>recover<span><span>&nbsp;</span></span>quickly<span><span>&nbsp;</span></span>from<span><span>&nbsp;</span></span>setbacks,<span><span>&nbsp;</span></span>failures,<span><span>&nbsp;</span></span>or<span><span>&nbsp;</span></span>obstacles.<span><span>&nbsp;</span></span>This trait helps entrepreneurs stay persistent and motivated.</span></li><li><b><span>Visionary<span><span>&nbsp;</span></span>Thinking</span></b><span>:<span><span>&nbsp;</span></span>Ability<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>see<span><span>&nbsp;</span></span>the<span><span>&nbsp;</span></span>bigger<span><span>&nbsp;</span></span>picture<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>envision<span><span>&nbsp;</span></span>long-term goals and strategies.&nbsp;</span></li><li><b><span>Networking<span><span>&nbsp;</span></span>Skills</span></b><span>:<span><span>&nbsp;</span></span>Strong<span><span>&nbsp;</span></span>interpersonal<span><span>&nbsp;</span></span>skills<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>build<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>maintain<span><span>&nbsp;</span></span>relationships with clients, partners, investors, and mentors.</span></li><li><b><span>Time<span><span>&nbsp;</span></span>Management</span></b><span>:<span><span>&nbsp;</span></span>Efficiently<span><span>&nbsp;</span></span>prioritizing<span><span>&nbsp;</span></span>tasks<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>managing<span><span>&nbsp;</span></span>time<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>maximize productivity and achieve goals.</span></li><li><b><span>Financial<span><span>&nbsp;</span></span>Acumen</span></b><span>:<span><span>&nbsp;</span></span>Understanding<span><span>&nbsp;</span></span>of<span><span>&nbsp;</span></span>financial<span><span>&nbsp;</span></span>management,<span><span>&nbsp;</span></span>budgeting,<span><span>&nbsp;</span></span>and<span>&nbsp;</span><span>investment.</span></span></li><li><b><span>Passion</span></b><span>: Deep enthusiasm and commitment to their work or industry. Passion drives<span><span>&nbsp;</span></span>entrepreneurs<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>overcome<span><span>&nbsp;</span></span>challenges<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>stay<span><span>&nbsp;</span></span>motivated<span><span>&nbsp;</span></span>during<span><span>&nbsp;</span></span>tough<span>&nbsp;</span><span>times.</span></span></li></ul><span></span><p></p></div><div class=\"WordSection3\"><p class=\"MsoListParagraph\"><b><span><span></span></span></b></p><h3>3.&nbsp; Description of the Role of an Entrepreneur:</h3><div><b>A. Roles of an Entrepreneur in Business</b><br></div><p></p><p class=\"MsoListParagraph\"><b>i.&nbsp;Innovator</b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>introduce<span><span>&nbsp;</span></span>new<span><span>&nbsp;</span></span>ideas,<span><span>&nbsp;</span></span>products,<span><span>&nbsp;</span></span>services,<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>processes to the market, driving innovation and keeping businesses competitive.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>ii.</b><span>&nbsp;</span></span></span><b><span>Risk-Taker</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>take<span><span>&nbsp;</span></span>calculated<span><span>&nbsp;</span></span>risks<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>start<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>grow<span><span>&nbsp;</span></span>businesses, often investing their own resources and time to achieve success.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>iii.</b>&nbsp;</span></span><b><span>Decision-Maker</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>make<span><span>&nbsp;</span></span>critical<span><span>&nbsp;</span></span>decisions<span><span>&nbsp;</span></span>regarding<span><span>&nbsp;</span></span>business strategy, operations, finances, and market positioning.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>iv.&nbsp;</b></span></span><b><span>Leader</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>provide<span><span>&nbsp;</span></span>vision<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>direction,<span><span>&nbsp;</span></span>motivating<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>guiding<span><span>&nbsp;</span></span>their teams to achieve business goals.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>v.</b><span>&nbsp;</span></span></span><b><span>Problem-Solver</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>identify<span><span>&nbsp;</span></span>challenges<span><span>&nbsp;</span></span>within<span><span>&nbsp;</span></span>the<span><span>&nbsp;</span></span>business<span><span>&nbsp;</span></span>or<span><span>&nbsp;</span></span>market and develop creative solutions to overcome them.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>vi.</b>&nbsp;</span></span><b><span>Resource<span><span>&nbsp;</span></span>Manager</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>efficiently<span><span>&nbsp;</span></span>allocate<span><span>&nbsp;</span></span>resources<span><span>&nbsp;</span></span>such<span><span>&nbsp;</span></span>as<span><span>&nbsp;</span></span>capital, labor, and technology to maximize productivity and profitability.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>vii.</b>&nbsp;</span></span><b><span>Market<span><span>&nbsp;</span></span>Analyst</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>study<span><span>&nbsp;</span></span>market<span><span>&nbsp;</span></span>trends,<span><span>&nbsp;</span></span>customer<span><span>&nbsp;</span></span>needs,<span><span>&nbsp;</span></span>and competitor behavior to identify opportunities and adapt their strategies.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>viii.</b><span><span>&nbsp;</span></span></span></span><b><span>Brand<span><span>&nbsp;</span></span>Builder</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>create<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>promote<span><span>&nbsp;</span></span>a<span><span>&nbsp;</span></span>strong<span><span>&nbsp;</span></span>brand<span><span>&nbsp;</span></span>identity, building trust and loyalty among customers.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>ix.</b>&nbsp;</span></span><b><span>Job<span><span>&nbsp;</span></span>Creator</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>establish<span><span>&nbsp;</span></span>businesses<span><span>&nbsp;</span></span>that<span><span>&nbsp;</span></span>generate<span><span>&nbsp;</span></span>employment opportunities, contributing to economic growth.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>x.</b>&nbsp;</span></span><b><span>Customer<span><span>&nbsp;</span></span>Focus<span><span>&nbsp;</span></span>Advocate</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>prioritize<span><span>&nbsp;</span></span>customer<span><span>&nbsp;</span></span>satisfaction<span><span>&nbsp;</span></span>by delivering value, addressing feedback, and improving products or services.</span></p><p class=\"MsoListParagraph\"><span><b>B. Roles of an Entrepreneur in the Community</b><br></span></p></div><p class=\"MsoListParagraph\"><span><span><b>I.</b><span>&nbsp;</span></span></span><b><span>Economic<span><span>&nbsp;</span></span>Contributor</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>stimulate<span><span>&nbsp;</span></span>local<span><span>&nbsp;</span></span>economies<span><span>&nbsp;</span></span>by<span><span>&nbsp;</span></span>creating jobs, paying taxes, and supporting other businesses through partnerships.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>II.&nbsp;</b></span></span><b><span>Community Developer</span></b><span>: Entrepreneurs invest in community projects, infrastructure,<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>initiatives<span><span>&nbsp;</span></span>that<span><span>&nbsp;</span></span>improve<span><span>&nbsp;</span></span>the<span><span>&nbsp;</span></span>quality<span><span>&nbsp;</span></span>of<span><span>&nbsp;</span></span>life<span><span>&nbsp;</span></span>for<span><span>&nbsp;</span></span>residents.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>III.<span>&nbsp;</span></b></span></span><b><span>Role<span><span>&nbsp;</span></span>Model</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>inspire<span><span>&nbsp;</span></span>others<span><span>&nbsp;</span></span>in<span><span>&nbsp;</span></span>the<span><span>&nbsp;</span></span>community,<span><span>&nbsp;</span></span>especially<span><span>&nbsp;</span></span>aspiring business owners, by demonstrating resilience, creativity, and success.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>IV.</b><span>&nbsp;</span></span></span><b><span>Social<span><span>&nbsp;</span></span>Problem-Solver</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>address<span><span>&nbsp;</span></span>community<span><span>&nbsp;</span></span>challenges,<span><span>&nbsp;</span></span>such<span><span>&nbsp;</span></span>as poverty, unemployment, or lack of services, through innovative business<span>&nbsp;</span><span>solutions.</span></span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>V.<span>&nbsp;</span></b></span></span><b><span>Philanthropist</span></b><span>:<span><span>&nbsp;</span></span>Many<span><span>&nbsp;</span></span>entrepreneurs<span><span>&nbsp;</span></span>give<span><span>&nbsp;</span></span>back<span><span>&nbsp;</span></span>to<span><span>&nbsp;</span></span>their<span><span>&nbsp;</span></span>communities<span><span>&nbsp;</span></span>through donations, sponsorships, or charitable initiatives.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>VI.&nbsp;</b></span></span><b><span>Educator</span></b><span>: Entrepreneurs share their knowledge and expertise by mentoring young people,<span><span>&nbsp;</span></span>offering<span><span>&nbsp;</span></span>training<span><span>&nbsp;</span></span>programs,<span><span>&nbsp;</span></span>or<span><span>&nbsp;</span></span>speaking<span><span>&nbsp;</span></span>at<span><span>&nbsp;</span></span>community<span><span>&nbsp;</span></span>events.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>VII.<span><span>&nbsp;</span></span></b></span></span><b><span>Cultural<span><span>&nbsp;</span></span>Promoter</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>support<span><span>&nbsp;</span></span>local<span><span>&nbsp;</span></span>arts,<span><span>&nbsp;</span></span>culture,<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>traditions<span><span>&nbsp;</span></span>by promoting and preserving them through their businesses.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>VIII.</b><span>&nbsp;</span></span></span><b><span>Environmental<span><span>&nbsp;</span></span>Advocate</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>implement<span><span>&nbsp;</span></span>sustainable<span><span>&nbsp;</span></span>practices in their businesses and advocate for environmental conservation in the<span>&nbsp;</span><span>community.</span></span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>IX.<span>&nbsp;</span></b></span></span><b><span>Networking<span><span>&nbsp;</span></span>Facilitator</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>connect<span><span>&nbsp;</span></span>people<span><span>&nbsp;</span></span>and<span><span>&nbsp;</span></span>organizations<span><span>&nbsp;</span></span>within the community, fostering collaboration and partnerships.</span><span></span></p><p class=\"MsoListParagraph\"><span><span><b>X.<span>&nbsp;</span></b></span></span><b><span>Empowerment<span><span>&nbsp;</span></span>Agent</span></b><span>:<span><span>&nbsp;</span></span>Entrepreneurs<span><span>&nbsp;</span></span>empower<span><span>&nbsp;</span></span>marginalized<span><span>&nbsp;</span></span>groups<span><span>&nbsp;</span></span>by providing opportunities for employment, skill development, and<span>&nbsp;</span><span>entrepreneurship.</span></span></p><!--EndFragment--><b><i>\r\n\r\n<blockquote>For more notes, download the attached course material at the bottom of this post.</blockquote></i></b><p></p>\r\n                                    ', NULL, 16, 6, 24, 1, 0, 1, 2, 0, 2, 'published', '2025-08-29 10:29:57', '2025-08-28 08:37:18', '2025-09-01 10:43:53', 3);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `author_id`, `category_id`, `course_module_id`, `lesson_order`, `is_premium`, `is_featured`, `upvotes`, `downvotes`, `vote_score`, `status`, `published_at`, `created_at`, `updated_at`, `view_count`) VALUES
(35, 'ENTREPRENEURSHIP - Develop a business plan - CCMBP402', 'entrepreneurship---develop-a-business-plan---ccmbp402', 'This module covers the skills, knowledge and attitude to develop a business plan \r\nwhich is linked to organizational strategic outcomes and facilitates the \r\nachievement of service delivery.', '                                        <p class=\"text-muted\"></p><div class=\"WordSection1\"><p class=\"MsoNormal\"><b><span></span></b></p><h1><b>Learning outcome\r\n1: Identify elements\r\nof business plan</b></h1><b><span></span></b><b><span></span></b><p></p>\r\n\r\n<p class=\"MsoNormal\"><b><span></span></b></p><h2><b>IC. 1.1 Definition of the business\r\nplan concepts</b></h2><b><span></span></b><p></p>\r\n\r\n<p class=\"MsoBodyText\"><b><span></span>1 Business Plan</b><span style=\"font-size: 1.25rem;\"></span></p>\r\n\r\n<p class=\"MsoBodyText\"><b><span>A business plan\r\n</span></b><span>is a formal document that outlines the\r\ngoals, strategies, and financial projections<span>\r\n</span>of<span> </span>a<span> </span>business.<span> </span>It<span> </span>serves<span> </span>as\r\na<span> </span>roadmap<span> </span>for<span> </span>the<span> </span>organization<span> </span>and<span> </span>is<span> </span>often<span> </span>used\r\nto secure funding or guide decision-making.</span></p>\r\n\r\n<p class=\"MsoBodyText\">&nbsp;<b>2.&nbsp;\r\n</b>A <b>Project\r\nPlan </b>is a formal document that outlines how a project will be executed,\r\nmonitored, controlled, and closed. It serves as a roadmap for the project team\r\nand stakeholders, ensuring everyone\r\nunderstands the goals,\r\ntimelines, responsibilities, and risks involved.</p>\r\n\r\n<h5><span>Key<span> </span>Elements<span>\r\n</span>of<span> </span>a<span> </span>Project<span> </span><span>Plan:</span></span></h5>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>1.<span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Objectives<span> </span>&amp;<span>\r\n</span>Scope<span> </span></span></b><span>â€“<span> </span>What<span> </span>the<span> </span>project<span>\r\n</span>aims<span> </span>to<span> </span>achieve<span> </span>and<span> </span>what<span> </span>is<span> </span><span>included/excluded.</span></span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>2.<span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Stakeholders<span> </span></span></b><span>â€“<span> </span>Who<span> </span>is<span> </span>involved<span> </span>and<span> </span>their<span> </span><span>roles.</span></span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>3.<span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Deliverables<span> </span></span></b><span>â€“<span> </span>Tangible<span> </span>outcomes<span>\r\n</span>(e.g.,<span> </span>software,<span> </span>report,<span>\r\n</span><span>product).</span></span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>4.<span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Timeline<span> </span>&amp;<span> </span>Milestones<span> </span></span></b><span>â€“<span> </span>Key<span> </span>deadlines and<span> </span><span>phases.</span></span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>5.<span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Budget<span> </span>&amp;<span> </span>Resources<span> </span></span></b><span>â€“<span> </span>Estimated<span> </span>costs,<span>\r\n</span>funding,<span> </span>and<span> </span>required<span>\r\n</span><span>tools/people.</span></span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>6.<span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Risk<span> </span>Management<span> </span></span></b><span>â€“<span> </span>Potential<span> </span>challenges<span> </span>and<span> </span>mitigation<span> </span><span>strategies.</span></span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>7.<span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Communication<span> </span>Plan<span> </span></span></b><span>â€“<span> </span>How<span> </span>updates<span> </span>will<span> </span>be<span> </span>shared<span>\r\n</span>(meetings,<span> </span>reports,<span> </span><span>etc.).</span></span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>8.<span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Success<span> </span>Metrics<span>\r\n</span></span></b><span>â€“<span> </span>How<span> </span>project<span>\r\n</span>success<span> </span>will<span> </span>be<span> </span>measured<span> </span><span>(KPIs).</span></span><span></span></p>\r\n\r\n<h5><!--[if !supportLists]--><b><span><span>3.<span>&nbsp; </span></span></span><!--[endif]--><span>Importance<span> </span>of<span> </span>a<span> </span>Business<span> </span><span>Plan</span></span></b></h5>\r\n\r\n<p class=\"MsoBodyText\"><span>A<span> </span>business<span>\r\n</span>plan<span> </span>is<span> </span>crucial<span> </span>for<span> </span>several<span>\r\nreasons:</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>1.<span>&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><b><span>Guides<span>\r\n</span>Decision-Making</span></b><span>:<span> </span>Provides<span> </span>a<span> </span>clear<span> </span>direction<span> </span>for<span> </span>the<span> </span>business<span>\r\n</span>and<span> </span>helps<span> </span>in making informed decisions.</span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>2.<span>&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><b><span>Attracts<span>\r\n</span>Investors</span></b><span>:<span> </span>Demonstrates<span> </span>the<span> </span>viability<span> </span>of<span> </span>the<span> </span>business<span>\r\n</span>to<span> </span>potential investors or\r\nlenders.</span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>3.<span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Sets<span> </span>Goals<span>\r\n</span>and<span> </span>Objectives</span></b><span>:<span> </span>Helps<span> </span>define<span> </span>short-term<span> </span>and<span> </span>long-term<span> goals.</span></span><span></span></p>\r\n\r\n</div>\r\n\r\n<div class=\"WordSection2\"><p class=\"MsoListParagraph\"><span><span>4.<span>&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><b><span>Identifies<span> </span>Challenges</span></b><span>:<span> </span>Allows<span> </span>businesses<span> </span>to<span> </span>anticipate<span> </span>risks<span> </span>and<span> </span>plan<span> </span>for\r\n<span>contingencies.</span></span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>5.<span>&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><b><span>Measures<span> </span>Progress</span></b><span>:<span> </span>Serves<span>\r\n</span>as<span> </span>a<span> </span>benchmark<span> </span>to<span> </span>track<span> </span>growth<span> </span>and<span> </span><span>performance.</span></span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>6.<span>&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><b><span>Improves<span> </span>Credibility</span></b><span>:<span> </span>Shows<span> </span>professionalism<span> </span>and<span> </span>commitment<span> </span>to<span> </span><span>stakeholders.</span></span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>7.<span>&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><b><span>Secures<span> </span>Funding</span></b><span>:<span> </span>Essential<span> </span>for<span> </span>obtaining<span> </span>loans<span> </span>or<span> </span>attracting<span> </span>venture<span> </span><span>capital.</span></span><span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><b><span><span>4.<span>&nbsp; </span></span></span></b><!--[endif]--><b><span>Needs<span> </span>of<span> </span>the<span> </span>business<span> </span>plan:<span> </span>Internal<span> </span>use<span> </span>External<span> </span><span>use</span></span></b></p>\r\n\r\n<h5><!--[if !supportLists]--><span><span>A.<span>&nbsp;&nbsp; </span></span></span><!--[endif]--><span>Internal<span> </span><span>Use</span></span></h5>\r\n\r\n<p class=\"MsoBodyText\"><span>The<span> </span>business<span> </span>plan<span> </span>is<span> </span>primarily<span> </span>a<span> </span>strategic<span> </span>tool<span> </span>for<span> </span>the<span> </span>business<span> </span>owners,<span>\r\n</span>management team,<span> </span>and<span> </span>employees.<span> </span>It<span> </span>helps<span> </span>guide<span> </span>decision-making,<span> </span>set<span> </span>goals,<span> </span>and<span> </span>align<span> </span>the<span> </span><span>team.</span></span></p>\r\n\r\n<p class=\"MsoBodyText\">&nbsp;These stakeholders use the business\r\nplan for <b>strategic decision-making, operations, and performance tracking</b>.</p><p class=\"MsoBodyText\"></p><table class=\"table table-bordered\"><thead><tr><th>User</th><th>How They Use the Business Plan <br></th></tr></thead><!--EndFragment-->\r\n\r\n<tbody><tr><td><b>Founders/Executives</b></td><td>- Set long-term goals and vision.<br>-	Allocate resources (budget, staff).<br>-	Make high-level strategic decisions.<br></td></tr><tr><td><b>Management Team</b></td><td>-	Align departments (marketing, finance, operations).\r\n<br>-	Set KPIs and performance targets.\r\n<br>-	Track progress and adjust strategies.\r\n<br></td></tr><tr><td><b>Employees</b><br></td><td>-	Understand company direction.\r\n<br>-	Align individual roles with business objectives.\r\n<br>-	Stay motivated with growth plans.\r\n<br></td></tr><tr><td><b>Board of Directors</b><br></td><td>-	Evaluate leadership\'s strategy.<br>-	Approve major investments or pivots.<br>-	Ensure financial health.\r\n<br></td></tr></tbody></table><p></p></div><div class=\"WordSection4\">\r\n\r\n<p class=\"MsoHeading7\"><span><b>Key<span> </span><span>Needs:</span></b></span></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><b><span>Strategic<span> </span>Planning</span></b><span>:<span> </span>Define<span> </span>the<span> </span>company\'s<span>\r\n</span>mission,<span> </span>vision,<span> </span>and<span> </span>long-term<span> </span><span>goals.</span></span></li><li><b><span>Operational<span> </span>Guidance</span></b><span>:<span> </span>Outline<span>\r\n</span>processes,<span> </span>roles,<span> </span>and<span> </span>responsibilities<span> </span>to<span> </span>ensure\r\nsmooth operations.</span></li><li><b><span>Goal<span> </span>Setting</span></b><span>:<span> </span>Establish<span>\r\n</span>short-term<span> </span>and<span> </span>long-term<span>\r\n</span>objectives<span> </span>with<span> </span>measurable <span>KPIs.</span></span></li><li><b><span>Resource<span> </span>Allocation</span></b><span>:<span> </span>Plan<span> </span>for<span> </span>budgeting,<span> </span>staffing,<span> </span>and<span> </span>resource<span>\r\n</span><span>distribution.</span></span></li><li><b><span>Performance<span> </span>Tracking</span></b><span>:<span> </span>Provide<span>\r\n</span>a<span> </span>benchmark to<span> </span>measure<span>\r\n</span>progress<span> </span>and<span> </span>adjust strategies as needed.</span></li><li><b><span>Risk<span> </span>Management</span></b><span>:<span> </span>Identify<span>\r\n</span>potential<span> </span>risks and<span> </span>develop<span>\r\n</span>contingency<span> </span><span>plans.</span></span></li><li><b><span>Team<span> </span>Alignment</span></b><span>:<span> </span>Ensure<span>\r\n</span>all<span> </span>team<span> </span>members<span>\r\n</span>understand<span> </span>the<span> </span>companyâ€™s<span> </span>direction and priorities.</span></li></ul><!--[if !supportLists]--><span></span><p></p>\r\n\r\n</div>\r\n\r\n<div class=\"WordSection5\">\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n<h5><!--[if !supportLists]--><span><span>B.<span>&nbsp; </span></span></span><!--[endif]--><span>External<span> </span><span>Use</span></span></h5>\r\n\r\n<p class=\"MsoBodyText\"><span>The<span> </span>business<span> </span>plan<span> </span>is<span> </span>often<span>\r\n</span>shared<span> </span>with<span> </span>external<span>\r\n</span>stakeholders<span> </span>to<span> </span>secure<span> </span>funding,<span> </span>attract partners, or build credibility.</span></p>\r\n\r\n<p class=\"MsoBodyText\">&nbsp;These stakeholders rely on the business plan to <b>assess credibility, invest, or collaborate</b>.</p>\r\n\r\n<p class=\"MsoBodyText\"><!--[if gte vml 1]><o:wrapblock><v:shape\r\n  id=\"Graphic_x0020_28\" o:spid=\"_x0000_s2058\" style=\'position:absolute;\r\n  margin-left:1in;margin-top:5.05pt;width:364.75pt;height:.75pt;z-index:-15723520;\r\n  visibility:visible;mso-wrap-style:square;mso-wrap-distance-left:0;\r\n  mso-wrap-distance-top:0;mso-wrap-distance-right:0;mso-wrap-distance-bottom:0;\r\n  mso-position-horizontal:absolute;mso-position-horizontal-relative:page;\r\n  mso-position-vertical:absolute;mso-position-vertical-relative:text;\r\n  v-text-anchor:top\' coordsize=\"4632325,9525\" o:gfxdata=\"UEsDBBQABgAIAAAAIQC75UiUBQEAAB4CAAATAAAAW0NvbnRlbnRfVHlwZXNdLnhtbKSRvU7DMBSF\r\ndyTewfKKEqcMCKEmHfgZgaE8wMW+SSwc27JvS/v23KTJgkoXFsu+P+c7Ol5vDoMTe0zZBl/LVVlJ\r\ngV4HY31Xy4/tS3EvRSbwBlzwWMsjZrlprq/W22PELHjb51r2RPFBqax7HCCXIaLnThvSAMTP1KkI\r\n+gs6VLdVdad08ISeCho1ZLN+whZ2jsTzgcsnJwldluLxNDiyagkxOquB2Knae/OLUsyEkjenmdzb\r\nmG/YhlRnCWPnb8C898bRJGtQvEOiVxjYhtLOxs8AySiT4JuDystlVV4WPeM6tK3VaILeDZxIOSsu\r\nti/jidNGNZ3/J08yC1dNv9v8AAAA//8DAFBLAwQUAAYACAAAACEArTA/8cEAAAAyAQAACwAAAF9y\r\nZWxzLy5yZWxzhI/NCsIwEITvgu8Q9m7TehCRpr2I4FX0AdZk2wbbJGTj39ubi6AgeJtl2G9m6vYx\r\njeJGka13CqqiBEFOe2Ndr+B03C3WIDihMzh6RwqexNA281l9oBFTfuLBBhaZ4ljBkFLYSMl6oAm5\r\n8IFcdjofJ0z5jL0MqC/Yk1yW5UrGTwY0X0yxNwri3lQgjs+Qk/+zfddZTVuvrxO59CNCmoj3vCwj\r\nMfaUFOjRhrPHaN4Wv0VV5OYgm1p+LW1eAAAA//8DAFBLAwQUAAYACAAAACEAWgQeZbICAABOBwAA\r\nHwAAAGNsaXBib2FyZC9kcmF3aW5ncy9kcmF3aW5nMS54bWysVV1P2zAUfZ+0/2D5HfJB20BFQIwN\r\nNAkBoqA9u47bRHNsz3ZDyq/fdRyT0El8bFOl+ib3+OTc42v7+LStOWqYNpUUOU72Y4yYoLKoxDrH\r\nD/cXe4cYGUtEQbgULMdbZvDpyedPx2S+1kSVFUXAIMyc5Li0Vs2jyNCS1cTsS8UE5FZS18TCo15H\r\nhSaPwFzzKI3jWVSTSuCTgeorsQRtdPUXVFzSn6w4J6IhBig5nY/f9Bo5/XdmMhfNpVYLdaudcnrd\r\n3GpUFTkG5wSpwSIc9YkeBo/Rzqz1QNCudO3wcrVCbY6PkskkBqptjieTJI6zqadjrUUU8pPZQXqQ\r\nTjGigDiaQuS/Vt68Pp+W315jAIFeCAQjcUY5aaL5s9oU+sKXe9m3AbwJhQe4UVewLMYbAE75950Z\r\nA8T7+D9seC4ClmVj7CWTnbOkuTK2k7YuQkTKENFWhFAzahHPMcfI5thipHOsMVrmeOlLU8S6ec4T\r\nF6LH0XqU/XK4ZC0bdi87mHWLmmSH8Sw+6FY1dlSgdMBw8RKbJdP0JTYgwqg6Vt8lgS/kwjjGuKbq\r\nPxvSYfSwJMuSSZZ2At8BHqr5EHhXKuXSMO+Gs/PDtsJeSLLZ7J22gujkJTZ4EMbgxYB8s7yxhA+B\r\n3/QCWuS53SAeN7SRvCouKs6dY0avl+dco4ZA556l7tcv9QgGBKY/sGy76Da1bb/IYusYljDCGQaX\r\ngL2BvxWX0NeUVwqjUuqn3XePsN9zbH5tiGYY8e/CdGefDYEOwTIE2vJzCeri7qxX2tj79gfRCrkQ\r\nNhqcbddyURLFOkDYr86BZ6wTKuTZxspV5TYzJL1ul+DGLuyWu16COe6PieKWaHIHdXHirjIm9h4W\r\nvTGAgOmDARvDFuoONr/n9Q51lgFw5ybppvY3n7uuxs8nvwEAAP//AwBQSwMEFAAGAAgAAAAhAOFR\r\nNx/PBgAA5hsAABoAAABjbGlwYm9hcmQvdGhlbWUvdGhlbWUxLnhtbOxZzW/cRBS/I/E/jHxvs9/N\r\nRt1U2c1uA23aKNkW9Thrz9rTjD3WzGzSvaH2iISEKIgDlbhxQEClVuJS/ppAERSp/wJvZmyvJ+uQ\r\ntI2gguaQtZ9/877fm6/LV+7FDB0QISlPel79Ys1DJPF5QJOw590ajy6sekgqnASY8YT0vDmR3pX1\r\n99+7jNd8RtMJxyIYRyQmCBglcg33vEipdG1lRfpAxvIiT0kC36ZcxFjBqwhXAoEPQUDMVhq1Wmcl\r\nxjTx1oGj0oyGDP4lSmqCz8SeZkNQgmOQfnM6pT4x2GC/rhFyLgdMoAPMeh7wDPjhmNxTHmJYKvjQ\r\n82rmz1tZv7yC17JBTJ0wtjRuZP6ycdmAYL9hZIpwUgitj1rdS5sFfwNgahk3HA4Hw3rBzwCw74Ol\r\nVpcyz9Zotd7PeZZA9nGZ96DWrrVcfIl/c0nnbr/fb3czXSxTA7KPrSX8aq3T2mg4eAOy+PYSvtXf\r\nGAw6Dt6ALL6zhB9d6nZaLt6AIkaT/SW0DuholHEvIFPOtirhqwBfrWXwBQqyocguLWLKE3VSrsX4\r\nLhcjAGggw4omSM1TMsU+5OQAxxNBsRaA1wgufbEkXy6RtCwkfUFT1fM+THHilSAvn33/8tkTdHT/\r\n6dH9n44ePDi6/6Nl5IzawklYHvXi28/+fPQx+uPJNy8eflGNl2X8rz988svPn1cDoXwW5j3/8vFv\r\nTx8//+rT3797WAHfEHhSho9pTCS6QQ7RLo/BMOMVV3MyEa82YhxhWh6xkYQSJ1hLqeA/VJGDvjHH\r\nLIuOo0efuB68LaB9VAGvzu46Cu9FYqZoheRrUewAtzlnfS4qvXBNyyq5eTxLwmrhYlbG7WJ8UCV7\r\ngBMnvsNZCn0zT0vH8EFEHDV3GE4UDklCFNLf+D4hFdbdodTx6zb1BZd8qtAdivqYVrpkTCdONi0G\r\nbdEY4jKvshni7fhm+zbqc1Zl9SY5cJFQFZhVKD8mzHHjVTxTOK5iOcYxKzv8OlZRlZJ7c+GXcUOp\r\nINIhYRwNAyJl1ZibAuwtBf0aho5VGfZtNo9dpFB0v4rndcx5GbnJ9wcRjtMq7B5NojL2A7kPKYrR\r\nDldV8G3uVoh+hzjg5MRw36bECffp3eAWDR2VFgmiv8yEjiW0aqcDxzT5u3bMKPRjmwPn146hAT7/\r\n+lFFZr2tjXgD5qSqStg61n5Pwh1vugMuAvr299xNPEt2CKT58sTzruW+a7nef77lnlTPZ220i94K\r\nbVevG+yi2CyR4xNXyFPK2J6aM3JdmkWyhHkiGAFRjzM7QVLsmNIIHrO+7uBCgc0YJLj6iKpoL8Ip\r\nLLDrnmYSyox1KFHKJWzsDLmSt8bDIl3ZbWFbbxhsP5BYbfPAkpuanO8LCjZmtgnN5jMX1NQMziqs\r\neSljCma/jrC6VurM0upGNdPqHGmFyRDDZdOAWHgTFiAIli3g5Q7sxbVo2JhgRgLtdzv35mExUTjP\r\nEMkIBySLkbZ7OUZ1E6Q8V8xJAORORYz0Ju8Ur5WkdTXbN5B2liCVxbVOEJdH702ilGfwIkq6bo+V\r\nI0vKxckSdNjzuu1G20M+TnveFPa08BinEHWp13yYhXAa5Cth0/7UYjZVvohmNzfMLYI6HFNYvy8Z\r\n7PSBVEi1iWVkU8N8ylKAJVqS1b/RBreelwE2019Di+YqJMO/pgX40Q0tmU6Jr8rBLlG07+xr1kr5\r\nTBGxFwWHaMJmYhdD+HWqgj0BlXA0YTqCfoFzNO1t88ltzlnRlU+vDM7SMUsjnLVbXaJ5JVu4qeNC\r\nB/NWUg9sq9TdGPfqppiSPydTymn8PzNFzydwUtAMdAR8OJQVGOl67XlcqIhDF0oj6o8ELBxM74Bs\r\ngbNY+AxJBSfI5leQA/1ra87yMGUNGz61S0MkKMxHKhKE7EBbMtl3CrN6NndZlixjZDKqpK5MrdoT\r\nckDYWPfAjp7bPRRBqptukrUBgzuef+57VkGTUC9yyvXm9JBi7rU18E+vfGwxg1FuHzYLmtz/hYoV\r\ns6odb4bnc2/ZEP1hscxq5VUBwkpTQTcr+9dU4RWnWtuxlixutHPlIIrLFgOxWBClcN6D9D+Y/6jw\r\nmb1t0BPqmO9Cb0Vw0aCZQdpAVl+wCw+kG6QlTmDhZIk2mTQr69ps6aS9lk/W57zSLeQec7bW7Czx\r\nfkVnF4szV5xTi+fp7MzDjq8t7URXQ2SPlyiQpvlGxgSm6tZpG6doEtZ7Htz8QKDvwRPcHXlAa2ha\r\nQ9PgCS6EYLFkb3F6XvaQU+C7pRSYZk5p5phWTmnllHZOgcVZdl+SUzrQqfQVB1yx6R8P5bcZsILL\r\nbj/ypupcza3/BQAA//8DAFBLAwQUAAYACAAAACEAnGZGQbsAAAAkAQAAKgAAAGNsaXBib2FyZC9k\r\ncmF3aW5ncy9fcmVscy9kcmF3aW5nMS54bWwucmVsc4SPzQrCMBCE74LvEPZu0noQkSa9iNCr1AcI\r\nyTYtNj8kUezbG+hFQfCyMLPsN7NN+7IzeWJMk3ccaloBQae8npzhcOsvuyOQlKXTcvYOOSyYoBXb\r\nTXPFWeZylMYpJFIoLnEYcw4nxpIa0cpEfUBXNoOPVuYio2FBqrs0yPZVdWDxkwHii0k6zSF2ugbS\r\nL6Ek/2f7YZgUnr16WHT5RwTLpRcWoIwGMwdKV2edNS1dgYmGff0m3gAAAP//AwBQSwECLQAUAAYA\r\nCAAAACEAu+VIlAUBAAAeAgAAEwAAAAAAAAAAAAAAAAAAAAAAW0NvbnRlbnRfVHlwZXNdLnhtbFBL\r\nAQItABQABgAIAAAAIQCtMD/xwQAAADIBAAALAAAAAAAAAAAAAAAAADYBAABfcmVscy8ucmVsc1BL\r\nAQItABQABgAIAAAAIQBaBB5lsgIAAE4HAAAfAAAAAAAAAAAAAAAAACACAABjbGlwYm9hcmQvZHJh\r\nd2luZ3MvZHJhd2luZzEueG1sUEsBAi0AFAAGAAgAAAAhAOFRNx/PBgAA5hsAABoAAAAAAAAAAAAA\r\nAAAADwUAAGNsaXBib2FyZC90aGVtZS90aGVtZTEueG1sUEsBAi0AFAAGAAgAAAAhAJxmRkG7AAAA\r\nJAEAACoAAAAAAAAAAAAAAAAAFgwAAGNsaXBib2FyZC9kcmF3aW5ncy9fcmVscy9kcmF3aW5nMS54\r\nbWwucmVsc1BLBQYAAAAABQAFAGcBAAAZDQAAAAA=\r\n\" path=\"m1780603,r-9080,l,,,9144r1771472,l1780603,9144r,-9144xem4631766,l1780616,r,9144l4631766,9144r,-9144xe\"\r\n  fillcolor=\"#a2a2a2\" stroked=\"f\">\r\n  <v:path arrowok=\"t\"/>\r\n  <w:wrap type=\"topAndBottom\" anchorx=\"page\"/>\r\n </v:shape><![endif]--><!--[if !vml]--><span>\r\n </span></p>\r\n  \r\n   \r\n  \r\n  \r\n   \r\n   \r\n  \r\n \r\n <!--[endif]--><!--[if gte vml 1]></o:wrapblock><![endif]--><table class=\"table table-bordered\"><thead><tr><th>\r\n\r\n<!--StartFragment-->User<!--EndFragment-->\r\n\r\n</th><th>How They Use the Business Plan </th></tr></thead><!--EndFragment-->\r\n\r\n<tbody><tr><td>\r\n\r\n<!--StartFragment--><b>Investors (VCs, Angels)</b><!--EndFragment-->\r\n\r\n</td><td>- Assess profitability &amp; ROI. <br>- Decide whether to fund the business. <br>- Evaluate growth potential. <br></td></tr><tr><td><b>Banks &amp; Lenders </b></td><!--EndFragment-->\r\n\r\n<td>- Check creditworthiness. <br>- Determine loan eligibility. <br>- Assess repayment ability.<br></td></tr><tr><td>\r\n\r\n<!--StartFragment--><b>Partners &amp; Suppliers</b><!--EndFragment-->\r\n\r\n</td><td>- Check creditworthiness. <br>- Determine loan eligibility. <br>- Assess repayment ability.<br></td></tr><tr><td>\r\n\r\n<!--StartFragment--><b>Government Agencies </b><!--EndFragment-->\r\n\r\n<br></td><td>- Verify legal compliance. <br>- Approve grants/licenses. <br>- Assess tax obligations. <br></td></tr><tr><td>\r\n\r\n<!--StartFragment--><b>Potential Clients </b><!--EndFragment-->\r\n\r\n<br></td><td>- Understand company reliability. <br>- Evaluate long-term service viability. <br></td></tr></tbody></table><p class=\"MsoHeading7\">Key Needs:</p></div><div class=\"WordSection8\">\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><b><span>Attracting<span> </span>Investors</span></b><span>:<span> </span>Convince<span>\r\n</span>investors<span> </span>or<span> </span>lenders<span>\r\n</span>of<span> </span>the<span> </span>business\'s<span> </span>viability<span> </span>and potential for return on investment.</span></li><li><b><span>Securing<span> </span>Loans</span></b><span>:<span> </span>Provide<span>\r\n</span>banks<span> </span>or<span> </span>financial<span> </span>institutions<span> </span>with<span> </span>a<span> </span>clear<span> </span>picture<span>\r\n</span>of<span> </span>the business\'s financial\r\nhealth and repayment ability.</span></li><li><b><span>Partnerships</span></b><span>:<span> </span>Demonstrate<span> </span>the<span> </span>value<span> </span>proposition<span> </span>to<span> </span>potential<span> </span>partners,<span>\r\n</span>suppliers, or collaborators.</span></li><li><b><span>Building<span> </span>Credibility</span></b><span>:<span> </span>Showcase<span>\r\n</span>the<span> </span>business\'s<span> </span>professionalism,<span> </span>market understanding, and growth potential.</span></li><li><b><span>Regulatory<span> </span>Compliance</span></b><span>:<span> </span>Meet<span> </span>requirements<span> </span>for<span> </span>licenses,<span> </span>permits,<span>\r\n</span>or <span>certifications</span></span></li></ul><!--[if !supportLists]--><span></span><p></p>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n<p class=\"MsoNormal\"><b><span></span></b></p><h2><b>IC.1.2 Description of the elements\r\nof a business plan</b></h2><b><span></span></b><p></p>\r\n\r\n<p class=\"MsoBodyText\"><b><span>&nbsp;</span>1\r\nTitle/Cover Page</b>: The cover of the\r\nbusiness plan is often the first impression of a business for interested parties or investors. The purpose of a cover is to tell the reader what document is about</p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li>Business<span> name</span></li><li>Business<span> </span>logo<span> </span>Product <span>mark</span></li><li>Address<span> </span>including:<span> </span>Location,<span>\r\n</span>telephone,<span> </span>fax,<span> </span>email<span> </span>and<span> </span>company<span>\r\n</span>website,<span> </span><span>etc.</span></li><li>Name<span> </span>of<span> </span>person<span>\r\n</span>who<span> </span>developed<span> </span>the<span> </span>business<span> plan</span></li><li>In\r\nwhich day, month and year plan is issued.</li></ul><p></p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>2<span> </span>Executive<span> </span><span>Summary</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>The<span> </span>executive<span> </span>summary<span>\r\n</span>provides<span> </span>a<span> </span>concise<span>\r\n</span>overview<span> </span>of<span> </span>the<span> </span>business<span> </span>plan,<span> </span>highlighting\r\nthe key elements of the proposed venture.</span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>It<span> </span>is<span> </span>designed<span>\r\n</span>to<span> </span>capture<span> </span>the<span> </span>reader\'s<span> </span>attention and<span> </span>provide<span> </span>a<span> </span>clear<span>\r\n</span>understanding<span> </span>of<span> </span>the business opportunity, objectives, and\r\nstrategy.</span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>It<span> </span>is<span> </span>made<span> </span><span>up</span></span></p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Business<span> </span>Name<span> </span>and<span> </span><span>Location:</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>[Insert<span> </span>Business<span>\r\n</span>Name]<span> </span>is<span> </span>located<span>\r\n</span>in<span> </span>[Insert<span> </span>City,<span> </span><span>State/Country],</span></span></p>\r\n\r\n</div>\r\n\r\n<div class=\"WordSection9\"><p class=\"MsoHeading7\"><span><b>Type<span> </span>of<span> </span><span>Business:</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>The<span> </span>business<span> </span>operates<span>\r\n</span>as<span> </span>a<span> </span>[Insert<span> </span>Type<span> </span>of<span> </span>Business,<span> </span>e.g.,<span> </span>tech<span> </span>startup,<span>\r\n</span>retail<span> </span>store,<span> </span>service <span>provider]</span></span></p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Industry/Market:</b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>[Insert<span> </span>Business<span>\r\n</span>Name]<span> </span>operates<span> </span>in<span> </span>the<span> </span>[Insert<span>\r\n</span>Industry,<span> </span>e.g.,<span> </span>technology,<span> </span>healthcare, retail] industry, targeting [Insert Target Market,\r\ne.g., small businesses, millennials, healthcare providers]..</span></p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Uniqueness<span> </span>of<span> </span><span>Products/Services:</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>Our\r\nproducts/services stand out due to [Insert Unique Selling Proposition, e.g.,\r\nproprietary<span> </span>technology,<span> </span>unique<span>\r\n</span>design,<span> </span>exceptional<span> </span>customer<span>\r\n</span>service].<span> </span>We<span> </span>hold<span> </span>[Insert\r\nProprietary Rights, e.g., trademarks, copyrights]</span></p>\r\n\r\n<p class=\"MsoHeading7\"><b><span>Curre</span>nt Stage\r\nof Development:</b></p><p class=\"MsoHeading7\">The venture is currently in the [Insert\r\nStage, e.g., ideation,\r\ndevelopment, launch] phase.</p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Legal<span> </span>Form<span> </span>of<span> </span><span>Organization:</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>The<span> </span>business<span> </span>is<span> </span>structured<span> </span>as<span> </span>a<span> </span>[Insert<span>\r\n</span>Legal<span> </span>Form,<span> </span>e.g.,<span> </span>LLC,<span> </span>Corporation,<span> </span>Partnership]<span> </span>to\r\n[Insert Reason, e.g., limit liability, facilitate investment, streamline\r\noperations</span></p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Key<span> </span>Management<span> </span><span>Personnel:</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>Our<span> </span>team comprises experienced<span> </span>professionals with<span> </span>expertise in<span> </span>[Insert\r\nRelevant Skills, e.g., product development, marketing, finance]. [Insert Key\r\nPersonnel Name], [Insert Title],<span> </span>brings<span> </span>[Insert<span>\r\n</span>Relevant<span> </span>Experience,<span> </span>e.g., 10<span>\r\n</span>years<span> </span>in<span> </span>the<span> </span>industry,<span> </span>a<span> </span>track<span> </span>record<span> </span>of\r\nsuccessful ventures], ensuring the business is well-positioned for success.</span></p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Projection<span> </span>of<span>\r\nSales:</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>We<span> </span>anticipate generating<span> </span>[Insert<span>\r\n</span>Sales<span> </span>Projection,<span> </span>e.g.,<span> </span>$1<span> </span>million<span>\r\n</span>in<span> </span>revenue]<span> </span>within<span>\r\n</span>the first [Insert Timeframe, e.g., year, two years], driven by [Insert\r\nKey Drivers, e.g., market demand, marketing strategy, product launches].</span></p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Break-Even<span> </span><span>Analysis:</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>The<span> </span>business<span> </span>is<span> </span>expected<span> </span>to<span> </span>break<span> </span>even<span> </span>within<span> </span>[Insert<span>\r\n</span>Timeframe,<span> </span>e.g.,<span> </span>18<span> </span>months],<span> </span>based on<span>\r\n</span>[Insert<span> </span>Key<span> </span>Assumptions,<span> </span>e.g., current<span> </span>sales\r\ntrends,<span> </span>cost<span> </span>structure,<span> </span>market<span> </span>conditions].</span></p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Funding<span> </span><span>Request:</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>[Insert<span> </span>Business<span> </span>Name]<span> </span>is<span> </span>seeking<span>\r\n</span>[Insert<span> </span>Amount]<span> </span>in<span> </span>funding<span> </span>to<span> </span>[Insert<span> </span>Purpose,<span>\r\n</span>e.g., expand operations, develop new products, enter new markets].</span></p>\r\n\r\n</div>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Expected<span> </span>Benefits<span> </span>of<span> </span><span>Investment:</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>Investors<span> </span>can<span> </span>expect<span> </span>[Insert<span>\r\n</span>Expected<span> </span>Benefits,<span> </span>e.g.,<span> </span>a<span> </span>high<span> </span>return<span> </span>on<span> </span>investment,<span> </span>equity stake, strategic partnership] as we\r\n[Insert Growth Plans, e.g., scale operations, capture market share, increase\r\nprofitability].</span></p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Funds<span> </span><span>Repayment:</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>The funds will be repaid through [Insert Repayment\r\nMethod, e.g., revenue generation, equity<span> </span>sale,<span> </span>loan<span> </span>repayment<span> </span>schedule],<span> </span>ensuring<span> </span>a<span> </span>secure<span>\r\n</span>and<span> </span>timely<span> </span>return<span>\r\n</span>for<span> </span>investors.</span></p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Collateral:</b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>To<span> </span>secure<span> </span>the<span> </span>loan,<span> </span>we<span> </span>offer<span> </span>[Insert<span>\r\n</span>Collateral,<span> </span>e.g.,<span> </span>business<span>\r\n</span>assets,<span> </span>personal<span> </span>guarantees, intellectual property] as\r\ncollateral, providing additional security for lenders.</span></p>\r\n\r\n<p class=\"MsoHeading7\"><span><b>Business<span> </span>Financial<span> </span><span>Milestones:</span></b></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>Key<span> </span>financial<span> </span>milestones<span> </span>include<span> </span>[Insert<span>\r\n</span>Milestones,<span> </span>e.g.,<span> </span>achieving<span>\r\n</span>$500,000<span> </span>in<span> </span>revenue, breaking even, securing Series A\r\nfunding] within [Insert Timeframe, e.g., the next 12 months], demonstrating our\r\ncommitment to growth and financial stability.<br></span></p><p class=\"MsoBodyText\"><span></span></p><blockquote><b><i>For more, download the attached course material at the bottom of this post.</i></b></blockquote><br><p></p><p></p><p></p>\r\n                                    ', NULL, 16, 6, 25, 1, 0, 1, 1, 0, 1, 'published', '2025-08-29 10:25:48', '2025-08-28 13:29:47', '2025-09-01 10:43:57', 3);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `author_id`, `category_id`, `course_module_id`, `lesson_order`, `is_premium`, `is_featured`, `upvotes`, `downvotes`, `vote_score`, `status`, `published_at`, `created_at`, `updated_at`, `view_count`) VALUES
(36, 'Entrepreneurship - Organize a business - CCMBO502', 'entrepreneurship---organize-a-business---ccmbo502', 'This module covers the skills, knowledge and attitude to organize a business which is linked to \r\norganizational strategic outcomes and facilitates the achievement of service delivery. The \r\nmodule will allow the learner to Identify activities to be accomplished before real business \r\noperations, create a productive working environment, run real business operations, monitor and \r\nevaluate the business.  ', '<div class=\"WordSection1\"><h2><span><h1>Learning outcome 1: Perform business opening activities</h1><span></span></span></h2>\r\n\r\n<h3><span></span></h3><h2>I.C. 1.1. Verification of Business Start-up Requirements</h2><p>Starting a business involves several critical steps,\r\nand verifying the requirements is essential to ensure a smooth launch and sustainable\r\noperations</p><p><b>1. Meaning of Business Requirements</b><br></p><p class=\"MsoBodyText\"><span style=\"font-size: 1.75rem;\"></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>Business requirements refer to the essential elements\r\nneeded to establish and operate a business<span> </span>successfully.<span> </span>These<span> </span>include<span> </span>legal,<span> </span>financial,<span> </span>operational,<span> </span>and<span> </span>logistical<span> </span>prerequisites<span> </span>that vary depending on the type of business, industry, and\r\nlocation.</span></p>\r\n\r\n<p class=\"MsoBodyText\"><b>2. Steps of Business Requirements Estimation</b><span style=\"font-size: 1.75rem;\"></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>To<span> </span>estimate<span>\r\n</span>business<span> </span>requirements,<span> </span>follow<span>\r\n</span>these<span> </span><span>steps:</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>1.<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Define<span> </span>Business<span> </span>Objectives</span></b><span>:<span> </span>Clearly<span> </span>outline<span>\r\n</span>the<span> </span>purpose,<span> </span>goals, and<span> </span>scope<span> </span>of<span> </span>the <span>business.</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>2.<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Conduct<span> </span>Market<span> </span>Research</span></b><span>:<span> </span>Analyze<span> </span>the<span> </span>target<span> </span>market,<span>\r\n</span>competition,<span> </span>and<span> </span>customer <span>needs.</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>3.<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Identify<span> </span>Legal<span> </span>Requirements</span></b><span>:<span> </span>Determine<span> </span>licenses,<span> </span>permits,<span> </span>and<span> </span>registrations<span> </span><span>needed.</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>4.<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Estimate<span> </span>Financial<span> </span>Needs</span></b><span>:<span> </span>Calculate<span> </span>startup<span> </span>costs,<span> </span>operational<span> </span>expenses,<span> </span>and<span> </span>funding <span>sources.</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>5.<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Plan<span> </span>Operational<span> </span>Needs</span></b><span>:<span> </span>Identify<span>\r\n</span>resources<span> </span>like<span> </span>equipment,<span> </span>technology,<span> </span>and<span> </span>human <span>resources.</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>6.<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Assess<span> </span>Location<span> </span>Requirements</span></b><span>:<span> </span>Evaluate<span> </span>the<span> </span>best<span> </span>location<span>\r\n</span>for<span> </span>the<span> business.</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>7.<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><b><span>Create<span> </span>a<span> </span>Business<span>\r\n</span>Plan</span></b><span>:<span> </span>Document<span> </span>all<span> </span>requirements<span> </span>and<span> </span>strategies<span> </span>for<span> </span><span>execution.</span></span></p>\r\n\r\n<p class=\"MsoBodyText\">&nbsp;<b>3. Business Location Requirements</b></p>\r\n\r\n<p class=\"MsoBodyText\"><span>The<span> </span>location<span>\r\n</span>of<span> </span>a<span> </span>business<span> </span>plays a<span> </span>crucial<span>\r\n</span>role<span> </span>in<span> </span>its<span> </span>success.<span> </span>Below<span>\r\n</span>are<span> </span>the<span> </span>key<span> </span>aspects to <span>consider:</span></span></p><p class=\"MsoBodyText\"><span><span><b>A. Meaning of Business Location</b><br></span></span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>A business location<span> </span>is<span> </span>the<span> </span>physical<span> </span>place<span> </span>where<span> </span>a<span> </span>business\r\noperates.<span> </span>It can<span> </span>be<span> </span>an<span> </span>office,<span>\r\n</span>retail store, warehouse, or even a virtual space, depending on the\r\nnature of the business.</span></p><p class=\"MsoBodyText\"><span><b>B. Factors Influencing Choice of Business Location</b><br></span></p></div><div class=\"WordSection2\">\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><b><span>Target<span> </span>Market</span></b><span>:<span> </span>Proximity<span> </span>to<span> </span>customers<span> </span>or<span>\r\nclients.</span></span></li><li><b><span>Accessibility</span></b><span>:<span> </span>Ease<span> </span>of<span> </span>transportation<span> </span>and<span> </span><span>parking.</span></span></li><li><b><span>Cost</span></b><span>:<span> </span>Rent,<span>\r\n</span>utilities,<span> </span>and<span> </span>taxes<span> </span>associated<span> </span>with<span> </span>the<span> </span><span>location.</span></span></li><li><b><span>Competition</span></b><span>:<span> </span>Presence<span>\r\n</span>of<span> </span>competitors<span> </span>in<span> </span>the<span> </span><span>area.</span></span></li><li><b><span>Infrastructure</span></b><span>:<span> </span>Availability<span> </span>of<span> </span>utilities,<span> </span>internet,<span>\r\n</span>and<span> </span>other<span> </span><span>facilities.</span></span></li><li><b><span>Zoning<span> </span>Laws</span></b><span>:<span> </span>Legal<span> </span>restrictions<span> </span>on<span> </span>the<span> </span>type<span> </span>of<span> </span>business<span>\r\n</span>activities<span> </span><span>allowed.</span></span></li><li><b><span>Safety<span> </span>and<span> </span>Security</span></b><span>:<span> </span>Crime<span> </span>rates<span> </span>and<span> </span>overall<span>\r\n</span>safety<span> </span>of<span> </span>the<span> </span><span>area.</span></span></li><li><b><span>Growth<span> </span>Potential</span></b><span>:<span> </span>Room<span> </span>for<span> </span>expansion<span> </span>if<span> </span><span>needed.</span></span></li></ul><div><b>C. Working Place Layout</b><br></div><p></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><b><span>Efficiency</span></b><span>:<span> </span>Design<span>\r\n</span>the<span> </span>layout<span> </span>to<span> </span>optimize<span> </span>workflow<span>\r\n</span>and<span> productivity.</span></span></li><li><b><span>Space<span> </span>Utilization</span></b><span>:<span> </span>Ensure<span> </span>adequate<span>\r\n</span>space<span> </span>for<span> </span>employees,<span> </span>equipment,<span> </span>and<span> </span><span>inventory.</span></span></li><li><b><span>Comfort</span></b><span>:<span> </span>Create<span>\r\n</span>a<span> </span>comfortable<span> </span>and<span> </span>ergonomic<span> </span>environment<span> </span>for<span> employees.</span></span></li><li><b><span>Compliance</span></b><span>:<span> </span>Adhere<span> </span>to<span> </span>health,<span>\r\n</span>safety,<span> </span>and<span> </span>accessibility<span> </span><span>regulations.</span></span></li></ul><div><b>D.    Office Furniture</b><br></div><p></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><b><span>Functional</span></b><span>:<span> </span>Desks,<span>\r\n</span>chairs,<span> </span>and<span> </span>storage<span>\r\n</span>units<span> </span>that<span> </span>meet<span> </span>the<span> </span>needs<span> </span>of<span> employees.</span></span></li><li><b><span>Ergonomic</span></b><span>:<span> </span>Furniture<span>\r\n</span>designed<span> </span>to<span> </span>support<span>\r\n</span>health<span> </span>and<span> </span>reduce<span>\r\nstrain.</span></span></li><li><b><span>Aesthetic</span></b><span>:<span> </span>Furniture<span>\r\n</span>that<span> </span>aligns<span> </span>with<span> </span>the<span> </span>companyâ€™s<span> </span>branding<span> </span>and<span> </span><span>culture.</span></span></li><li><b><span>Cost-Effective</span></b><span>:<span> </span>Balance<span>\r\n</span>quality<span> </span>and<span> </span>budget<span>\r\n</span>when<span> </span>selecting<span> furniture.</span></span></li></ul><div><b>E.      Office Supplies</b><br></div><p></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><b><span>Essential<span> </span>Items</span></b><span>:<span> </span>Stationery,<span> </span>printers,<span> </span>computers,<span> </span>and<span> </span>other<span> </span>daily-use<span> </span><span>items.</span></span></li><li><b><span>Technology</span></b><span>:<span> </span>Software,<span>\r\n</span>hardware,<span> </span>and<span> </span>communication<span> </span><span>tools.</span></span></li><li><b><span>Inventory<span> </span>Management</span></b><span>:<span> </span>Keep<span> </span>track<span>\r\n</span>of<span> </span>supplies<span> </span>to<span> </span>avoid<span> </span>shortages<span> </span>or<span> </span><span>overstocking.</span></span></li><li><b><span>Sustainability</span></b><span>:<span> </span>Opt<span> </span>for<span> </span>eco-friendly<span> </span>supplies<span> </span>where<span> </span><span>possible.</span></span></li></ul><!--[if !supportLists]--><p></p>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><b><span><span>4.&nbsp;</span></span></b><b>Raw<span> </span>materials<span> </span>for<span> </span>initial<span> </span><span>storage</span></b></p>\r\n\r\n<p class=\"MsoBodyText\">When planning for initial storage\r\nand production setup,\r\nit\'s essential to categorize and organize the\r\nnecessary materials and equipment. Below is a breakdown of the key components:</p><p class=\"MsoBodyText\"><b>A.      Raw Materials for Initial Storage</b><br></p>\r\n\r\n<p class=\"MsoListParagraph\"><b><span>Purpose</span></b><span>:<span> </span>These<span> </span>are<span> </span>the<span> </span>basic<span> </span>materials<span>\r\n</span>required<span> </span>to<span> </span>start <span>production.</span></span></p><p class=\"MsoListParagraph\"><span><span><b>Examples:</b><br></span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><span>Metals<span> </span>(e.g.,<span> </span>steel,<span> </span>aluminum,<span>\r\n</span><span>copper)</span></span></li><li><span>Plastics<span> </span>or<span> </span><span>polymers</span></span></li><li><span>Chemicals<span> </span>(e.g.,<span>\r\n</span>solvents,<span> </span>adhesives,<span> coatings)</span></span></li><li><span>Fabrics<span> </span>or<span> </span>textiles<span> </span>(for<span> </span>apparel<span> </span>or<span> </span><span>upholstery)</span></span></li><li><span>Wood or<span> </span>composite<span>\r\n</span><span>materials</span></span></li><li><span>Electronic<span> </span>components<span> </span>(e.g.,<span> </span>resistors,<span> </span>capacitors,<span> </span><span>chips)</span></span></li><li><span>Packaging<span> </span>materials<span>\r\n</span>(e.g.,<span> </span>cardboard,<span> </span>bubble<span>\r\n</span>wrap,<span> </span><span>labels)</span></span></li></ul><div><b>Considerations:</b><br></div><p></p></div><div class=\"WordSection3\">\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><span>Ensure<span> </span>proper<span> </span>storage<span> </span>conditions<span> </span>(e.g.,<span> </span>temperature,<span> </span><span>humidity).</span></span></li><li><span>Plan<span> </span>for<span> </span>inventory<span> </span>management<span> </span>to<span> </span>avoid<span> </span>overstocking<span> </span>or<span> </span><span>shortages.</span></span></li></ul><!--[if !supportLists]--><p></p>\r\n\r\n\r\n\r\n<p class=\"MsoBodyText\">&nbsp;<b>B.       Production Equipment and Machinery</b></p>\r\n\r\n<p class=\"MsoListParagraph\"><b><span>Purpose</span></b><span>:<span> </span>These<span> </span>are<span> </span>the<span> </span>tools\r\nand<span> </span>machines required<span> </span>to<span> </span>transform\r\nraw<span> </span>materials<span> </span>into finished products.</span></p><p class=\"MsoListParagraph\"><span><b>Examples:</b><br></span></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><span>CNC<span> </span>machines,<span> </span>lathes,<span> </span>or<span> </span>milling<span>\r\n</span>machines<span> </span>(for<span> </span><span>metalworking)</span></span></li><li><span>Injection<span> </span>molding<span>\r\n</span>machines<span> </span>(for<span> </span><span>plastics)</span></span></li><li><span>3D<span> </span>printers<span>\r\n</span>or<span> </span>prototyping<span> </span><span>equipment</span></span></li><li><span>Assembly<span> </span>line<span> </span>conveyors<span> </span>and<span> </span>robotic<span> </span><span>arms</span></span></li><li><span>Cutting,<span> </span>sewing,<span>\r\n</span>or<span> </span>weaving<span> </span>machines (for<span> </span><span>textiles)</span></span></li><li><span>Mixers,<span> </span>reactors,<span>\r\n</span>or<span> </span>distillation<span> </span>units<span> </span>(for<span> </span>chemical<span>\r\n</span><span>processing)</span></span></li><li><span>Testing<span> </span>and<span> </span>quality<span> </span>control<span>\r\n</span>equipment<span> </span>(e.g.,<span> </span>spectrometers,<span> </span>stress<span> </span><span>testers)</span></span></li></ul><div><b>Considerations:</b><br></div><p></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><span>Ensure<span> </span>proper<span>\r\n</span>installation<span> </span>and<span> calibration.</span></span></li><li><span>Plan<span> </span>for<span> </span>maintenance<span> </span>schedules<span>\r\n</span>and<span> </span>spare<span> </span><span>parts.</span></span></li></ul><!--[if !supportLists]--><p></p>\r\n\r\n\r\n\r\n<p class=\"MsoBodyText\">&nbsp;<b>C.       Production Consumables</b></p>\r\n\r\n<p class=\"MsoListParagraph\"><b><span>Purpose</span></b><span>:<span> </span>These<span> </span>are<span> </span>items<span> </span>used<span> </span>during<span> </span>production<span> </span>that<span> </span>are<span> </span>regularly<span>\r\n</span>replaced<span> </span>or <span>replenished.</span></span></p><p class=\"MsoListParagraph\"><span><span><b>Examples:</b><br></span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><span>Lubricants<span> </span>and<span> </span>coolants<span> </span>(for<span> </span><span>machinery)</span></span></li><li><span>Cutting<span> </span>tools<span>\r\n</span>(e.g.,<span> </span>drill<span> </span>bits, <span>blades)</span></span></li><li><span>Adhesives,<span> </span>tapes, and<span> </span><span>fasteners</span></span></li><li><span>Filters,<span> </span>gaskets,<span>\r\n</span>and<span> </span><span>seals</span></span></li><li><span>Cleaning<span> </span>supplies<span>\r\n</span>(e.g.,<span> </span>solvents, <span>wipes)</span></span></li><li><span>Safety<span> </span>gear<span> </span>(e.g.,<span> </span>gloves,<span>\r\n</span>masks,<span> </span><span>goggles)</span></span></li><li><span>Ink,<span> </span>toner,<span>\r\n</span>or<span> </span>printing<span> </span>supplies<span>\r\n</span>(for<span> </span>labeling<span> </span>or<span> </span><span>packaging)</span></span></li></ul><div><b>Considerations:</b><br></div><p></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>o<span>&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><span>Monitor<span> </span>usage<span> </span>rates<span> </span>to<span> </span>avoid<span> </span>running<span>\r\n</span>out<span> </span>during<span> </span><span>production.</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"><!--[if !supportLists]--><span><span>o<span>&nbsp;&nbsp;\r\n</span></span></span><!--[endif]--><span>Ensure<span> </span>compliance<span> </span>with<span> </span>safety<span> </span>and<span> </span>environmental<span> </span><span>regulations.</span></span></p>\r\n\r\n</div>\r\n\r\n<div class=\"WordSection4\"><b>5. Start-up Finances&nbsp;</b></div><div class=\"WordSection4\">Start-up finances refer to the financial resources and management strategies that a new business employs to establish and grow its operations. A. Meaning of Financial Management Financial management involves planning, organizing, controlling, and monitoring financial resources to achieve the business\'s objectives.</div><div class=\"WordSection4\">It includes activities such as <b>budgeting, forecasting, cash flow management, financial reporting, and risk management.</b><p class=\"MsoBodyText\"><span>The<span> </span>goal<span> </span>is<span> </span>to<span> </span>maximize<span> </span>the<span> </span>value<span> </span>of<span> </span>the<span> </span>business<span>\r\n</span>while<span> </span>ensuring<span> </span>financial<span>\r\n</span>stability<span> </span>and<span> growth.</span></span></p><p class=\"MsoBodyText\"><span><span><b>B.     Importance of Financial Management</b><br></span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ol><li><b><span>Cash<span> </span>Flow<span> </span>Management</span></b><span>:<span> </span>Ensures<span> </span>that<span> </span>the<span> </span>business<span>\r\n</span>has enough<span> </span>liquidity<span> </span>to<span> </span>meet<span> </span>its short-term obligations.</span></li><li><b><span>Budgeting<span> </span>and<span> </span>Forecasting</span></b><span>:<span> </span>Helps<span> </span>in<span> </span>planning<span> </span>for<span> </span>future<span> </span>expenses and<span> </span>revenues, allowing for better decision-making.</span></li><li><b><span>Risk<span> </span>Management</span></b><span>:<span> </span>Identifies<span> </span>and<span> </span>mitigates<span> </span>financial<span> </span>risks<span> </span>that<span> </span>could<span> </span>impact<span> </span>the <span>business.</span></span></li><li><b><span>Investment<span> </span>Decisions</span></b><span>:<span> </span>Assists in<span> </span>evaluating<span> </span>and<span> </span>selecting<span> </span>the<span> </span>best<span> </span>investment opportunities to fuel growth.</span></li><li><b><span>Profit<span> </span>Maximization</span></b><span>:<span> </span>Focuses<span> </span>on<span> </span>optimizing<span> </span>profits<span>\r\n</span>while<span> </span>minimizing<span> </span><span>costs.</span></span></li><li><b><span>Compliance</span></b><span>:<span> </span>Ensures<span>\r\n</span>that<span> </span>the<span> </span>business<span>\r\n</span>adheres<span> </span>to<span> </span>financial<span> </span>regulations<span> </span>and<span> </span>tax <span>obligations.</span></span></li></ol><div><b>C.       Quantity of Financial Needs</b><br></div><p></p>\r\n\r\n<p class=\"MsoBodyText\"><span>The<span> </span>amount<span>\r\n</span>of<span> </span>financial<span> </span>resources required<span> </span>by<span> </span>a<span> </span>start-up<span>\r\n</span>depends<span> </span>on<span> </span>various factors,<span> </span><span>including:</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><b><span>Business<span> </span>Model</span></b><span>:<span> </span>Different<span> </span>models<span> </span>(e.g.,<span> </span>service-based,<span> </span>product-based)<span> </span>have<span> </span>varying financial needs.</span></li><li><b><span>Industry</span></b><span>:<span> </span>Capital-intensive<span> </span>industries<span> </span>(e.g.,<span> </span>manufacturing)<span> </span>require<span> </span>more<span> </span>funding<span>\r\n</span>than <span>others.</span></span></li><li><b><span>Scale<span> </span>of<span> </span>Operations</span></b><span>:<span> </span>Larger<span> </span>operations<span> </span>with<span> </span>more<span> </span>employees<span> </span>and<span> </span>higher<span> </span>production levels need more capital.</span></li><li><b><span>Growth<span> </span>Plans</span></b><span>:<span> </span>Aggressive<span> </span>growth<span>\r\n</span>strategies<span> </span>may<span> </span>require<span>\r\n</span>significant<span> </span>investment<span> </span>in marketing, R&amp;D, and expansion.</span></li></ul><div><b>D.    Sources of Finances</b><br></div><p></p>\r\n\r\n</div>\r\n\r\n<div class=\"WordSection5\"><p class=\"MsoBodyText\"><span>Start-ups<span> </span>can<span> </span>obtain<span> </span>funding<span>\r\n</span>from<span> </span>various<span> </span>sources,<span>\r\n</span><span>including:</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ol><li><b><span>Personal<span> </span>Savings</span></b><span>:<span> </span>Founders\' own<span> </span>money<span>\r\n</span>invested<span> </span>in<span> </span>the<span> </span><span>business.</span></span></li><li><b><span>Friends<span> </span>and<span> </span>Family</span></b><span>:<span> </span>Loans<span> </span>or<span> </span>investments<span> </span>from<span> </span>personal<span> </span><span>networks.</span></span></li><li><b><span>Angel<span> </span>Investors</span></b><span>:<span> </span>High-net-worth<span> </span>individuals<span> </span>who<span> </span>provide<span> </span>capital<span>\r\n</span>in<span> </span>exchange<span> </span>for<span> </span><span>equity.</span></span></li><li><b><span>Venture<span> </span>Capital</span></b><span>:<span> </span>Institutional<span> </span>investors<span> </span>who<span> </span>invest<span> </span>in<span> </span>high-growth<span> </span>potential<span> </span>start-<span>ups.</span></span></li><li><b><span>Bank<span> </span>Loans</span></b><span>:<span> </span>Traditional<span> </span>loans<span> </span>from<span> </span>financial<span>\r\n</span><span>institutions.</span></span></li><li><b><span>Crowdfunding</span></b><span>:<span> </span>Raising<span>\r\n</span>small<span> </span>amounts<span> </span>of<span> </span>money<span> </span>from a<span>\r\n</span>large<span> </span>number<span> </span>of<span> </span>people,\r\ntypically via online platforms.</span></li><li><b><span>Grants</span></b><span>:<span> </span>Non-repayable<span> </span>funds<span> </span>from<span> </span>government<span> </span>or<span> </span>private<span> </span><span>organizations.</span></span></li><li><b><span>Bootstrapping</span></b><span>:<span> </span>Self-funding<span> </span>the<span> </span>business<span> </span>through<span>\r\n</span>revenue<span> </span>generated<span> </span>from<span> </span><span>operations.</span></span></li></ol><div><b>E.      Evaluating Sources of Business Capital</b><br></div><p></p>\r\n\r\n<p class=\"MsoBodyText\"><span>When<span> </span>evaluating<span> </span>sources<span> </span>of<span> </span>capital,<span>\r\n</span>consider<span> </span>the<span> </span>following<span> </span><span>factors:</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ol><li><b><span>Cost<span> </span>of<span> </span>Capital</span></b><span>:<span> </span>Interest<span>\r\n</span>rates, equity<span> </span>dilution,<span> </span>and<span> </span>other<span> </span>costs<span>\r\n</span>associated<span> </span>with<span> </span>the <span>funding.</span></span></li><li><b><span>Control</span></b><span>:<span> </span>How<span> </span>much<span> </span>control<span>\r\n</span>you<span> </span>are<span> </span>willing<span>\r\n</span>to<span> </span>give<span> </span>up<span> </span>(e.g.,<span> </span>equity<span>\r\n</span>financing<span> </span>vs. debt <span>financing).</span></span></li><li><b><span>Repayment<span> </span>Terms</span></b><span>:<span> </span>The<span> </span>terms<span>\r\n</span>and<span> </span>conditions for<span> </span>repayment,<span> </span>including<span> </span>interest<span> </span>rates<span>\r\n</span>and maturity periods.</span></li><li><b><span>Risk</span></b><span>:<span> </span>The<span> </span>level<span> </span>of<span> </span>risk<span> </span>associated<span> </span>with<span> </span>the<span> </span>funding<span>\r\n</span><span>source.</span></span></li><li><b><span>Flexibility</span></b><span>:<span> </span>The<span> </span>flexibility<span> </span>of<span> </span>the<span> </span>funding<span>\r\n</span>terms<span> </span>and<span> </span>the<span> </span>ability<span> </span>to<span> </span>adapt<span> </span>to changing business needs.</span></li><li><b><span>Alignment<span> </span>with<span> </span>Business<span>\r\n</span>Goals</span></b><span>:<span> </span>How<span> </span>well<span> </span>the<span> </span>funding<span> </span>source<span> </span>aligns\r\nwith<span> </span>the<span> </span>long-term goals and vision of the business.</span></li></ol><div><b>F.  Allocation of Financial Resources</b><br></div><div><b><br></b></div><div><div class=\"WordSection1\">\r\n\r\n<p class=\"MsoBodyText\"><span>Effective<span> </span>allocation<span> </span>of<span> </span>financial<span> </span>resources<span> </span><span>involves:</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ol><li><b><span>Prioritizing<span> </span>Expenses</span></b><span>:<span> </span>Focus<span> </span>on<span> </span>essential<span> </span>expenses<span>\r\n</span>that<span> </span>drive<span> </span>growth<span>\r\n</span>and<span> </span><span>profitability.</span></span></li><li><b><span>Investment<span> </span>in<span> </span>Growth</span></b><span>:<span> </span>Allocate<span>\r\n</span>funds<span> </span>to<span> </span>areas<span> </span>that\r\nwill<span> </span>generate<span> </span>the<span> </span>highest return<span> </span>on investment (ROI), such as marketing,\r\nR&amp;D, and talent acquisition.</span></li><li><b><span>Cash<span> </span>Reserves</span></b><span>:<span> </span>Maintain<span> </span>a<span> </span>buffer<span> </span>of<span> </span>cash<span> </span>reserves<span>\r\n</span>to<span> </span>handle<span> </span>unexpected<span> </span>expenses<span> </span>or <span>downturns.</span></span></li><li><b><span>Cost<span> </span>Control</span></b><span>:<span> </span>Monitor<span> </span>and<span> </span>control<span> </span>costs<span> </span>to<span> </span>avoid<span>\r\n</span>overspending<span> </span>and<span> </span>ensure<span> </span>efficient\r\nuse of resources.</span></li><li><b><span>Performance<span> </span>Monitoring</span></b><span>:<span> </span>Regularly<span> </span>review<span>\r\n</span>financial<span> </span>performance<span> </span>and<span> </span>adjust<span> </span>resource allocation as needed to stay on\r\ntrack with business goals.</span></li></ol><!--[if !supportLists]--><p></p>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n</div>\r\n\r\n<span></span><h2>\r\n\r\n\r\n\r\n\r\nI.C 1.2. Recruitment of Business Employees</h2><div class=\"WordSection2\"><h3><span><span></span></span></h3>\r\n\r\n<p class=\"MsoBodyText\"><span>Recruitment<span>\r\n</span>is<span> </span>a<span> </span>critical<span> </span>function<span> </span>of<span> </span>human<span> </span>resource<span>\r\n</span>management<span> </span>that<span> </span>involves attracting, selecting, and\r\nappointing suitable candidates for jobs within an organization.&nbsp;</span>Effective recruitment ensures that the organization has the right\r\npeople in the right roles\r\nto achieve its goals.</p><p class=\"MsoBodyText\"><b> 1.Meaning of Employee Recruitment</b><br></p>\r\n\r\n<p class=\"MsoBodyText\"><b><u><span>Employee<span> </span>recruitment</span></u><span> </span></b><span>refers<span> </span>to<span> </span>the<span> </span>process\r\nof<span> </span>identifying,<span> </span>attracting,<span> </span>and<span> </span>hiring<span> </span>qualified individuals to fill job\r\nvacancies within an organization.</span></p>\r\n\r\n<p class=\"MsoBodyText\"><span>Key<span> </span>objectives<span> </span>of<span>\r\nrecruitment:</span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><span>Attract<span> </span>a<span> </span>pool<span> </span>of<span> </span>talented<span> </span><span>candidates.</span></span></li><li><span>Fill<span> </span>job<span> </span>vacancies<span> </span>with<span> </span>qualified<span> </span><span>individuals.</span></span></li><li><span>Align<span> </span>recruitment<span> </span>with<span> </span>the<span> </span>organization\'s<span> </span>strategic<span> </span><span>goals.</span></span></li><li><span>Enhance<span> </span>the<span> </span>organization\'s<span> </span>reputation<span> </span>as<span> </span>an<span> </span>employer<span>\r\n</span>of<span> </span><span>choice.</span></span></li></ul><!--[if !supportLists]--><p></p>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n<p class=\"MsoBodyText\"><b> 2. Process of Employee Recruitment</b><br></p>\r\n\r\n<p class=\"MsoBodyText\"><span>The<span> </span>recruitment<span> </span>process<span>\r\n</span>typically<span> </span>involves<span> </span>the<span> </span>following\r\n<span>steps:</span></span></p><p class=\"MsoBodyText\"><span><span><b>A. Identifying the Vacancy</b><br></span></span></p>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><span>Determine<span> </span>the<span> </span>need<span> </span>for\r\na<span> </span>new<span> </span>employee<span> </span>due<span> </span>to<span> </span>expansion,<span> </span>turnover,<span> </span>or new <span>projects.</span></span></li><li><span>Define<span> </span>the<span> </span>job<span> </span>role,<span> </span>responsibilities,<span> </span>and<span> </span>qualifications<span> </span><span>required.</span></span></li></ul><div><b>B. Job Analysis and Description</b><br></div>\r\n\r\n<p class=\"MsoListParagraph\"></p><ul><li><span>Conduct<span> </span>a<span> </span>job<span> </span>analysis<span> </span>to<span> </span>understand<span> </span>the<span> </span>skills,<span> </span>experience,<span> </span>and<span> </span>competencies <span>needed.</span></span></li><li><span>Create<span> </span>a<span> </span>detailed<span> </span>job<span> </span>description<span> </span>and<span> </span>person<span> </span><span>specification.</span></span></li></ul><div><blockquote><b><i>\r\n\r\nFor more, download the attached course material at the bottom of this post.\r\n\r\n</i></b></blockquote><br></div><!--[if !supportLists]--><p></p>\r\n\r\n</div></div><p></p></div>\r\n\r\n<!--EndFragment-->\r\n\r\n\r\n\r\n', NULL, 16, 6, 26, 1, 0, 1, 1, 0, 1, 'published', '2025-08-29 10:25:08', '2025-08-28 14:35:02', '2025-09-01 10:44:03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `post_affiliate_products`
--

CREATE TABLE `post_affiliate_products` (
  `post_id` int(11) NOT NULL,
  `affiliate_product_id` int(11) NOT NULL,
  `position` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `post_tags`
--

CREATE TABLE `post_tags` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `post_tags`
--

INSERT INTO `post_tags` (`post_id`, `tag_id`) VALUES
(34, 13),
(35, 13),
(36, 13);

-- --------------------------------------------------------

--
-- Table structure for table `post_views`
--

CREATE TABLE `post_views` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `viewed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `post_views`
--

INSERT INTO `post_views` (`id`, `post_id`, `ip_address`, `user_agent`, `viewed_at`) VALUES
(36, 34, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-28 08:38:42'),
(37, 35, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-28 13:30:14'),
(38, 36, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-28 14:36:15'),
(39, 34, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-29 09:37:57'),
(40, 35, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-29 13:38:37'),
(41, 34, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-01 06:44:24'),
(42, 35, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-01 10:42:06');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'TelieAcadem', '2025-08-05 17:40:11', '2025-08-05 18:52:35'),
(2, 'site_description', 'Learn modern web development with comprehensive tutorials', '2025-08-05 17:40:11', '2025-08-05 18:52:35'),
(3, 'site_url', 'http://localhost/TelieAcademy', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(4, 'admin_email', 'admin@telieacademy.com', '2025-08-05 17:40:11', '2025-08-05 18:52:35'),
(5, 'contact_email', 'contact@telieacademy.com', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(6, 'posts_per_page', '10', '2025-08-05 17:40:11', '2025-08-05 18:52:35'),
(7, 'comments_enabled', '1', '2025-08-05 17:40:11', '2025-08-05 18:52:35'),
(8, 'comments_moderation', '1', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(9, 'newsletter_enabled', '1', '2025-08-05 17:40:11', '2025-08-05 18:52:35'),
(10, 'premium_content_enabled', '1', '2025-08-05 17:40:11', '2025-08-05 18:52:35'),
(11, 'ads_enabled', '0', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(12, 'social_facebook', '', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(13, 'social_twitter', '', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(14, 'social_instagram', '', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(15, 'social_youtube', '', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(16, 'footer_text', '© 2024 TelieAcademy. All rights reserved.', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(17, 'theme_color', '#828282', '2025-08-05 17:40:11', '2025-08-05 18:52:35'),
(18, 'logo_text', 'TelieAcademy', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(19, 'meta_keywords', 'web development, javascript, react, python, tutorials', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(20, 'meta_description', 'Learn modern web development with comprehensive tutorials on JavaScript, React, Python, and more.', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(21, 'google_analytics', '', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(22, 'disqus_shortname', '', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(23, 'recaptcha_site_key', '', '2025-08-05 17:40:11', '2025-08-05 17:40:11'),
(24, 'recaptcha_secret_key', '', '2025-08-05 17:40:11', '2025-08-05 17:40:11');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('newsletter','premium') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `billing_cycle` enum('monthly','quarterly','yearly','lifetime') COLLATE utf8mb4_unicode_ci DEFAULT 'monthly',
  `features` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `description`, `type`, `price`, `billing_cycle`, `features`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Free Newsletter', 'Weekly newsletter with latest tutorials and updates', 'newsletter', '0.00', 'monthly', '[\"Weekly newsletter\", \"Latest tutorials\", \"Community updates\", \"Basic support\"]', 1, 1, '2025-08-06 20:06:26', '2025-08-06 20:06:26'),
(2, 'Premium Subscription', 'Full access to premium content, exclusive tutorials, and priority support', 'premium', '9.99', 'monthly', '[\"All newsletter benefits\", \"Premium tutorials\", \"Exclusive content\", \"Priority support\", \"Ad-free experience\", \"Early access to new features\"]', 1, 2, '2025-08-06 20:06:26', '2025-08-06 20:06:26'),
(3, 'Premium Yearly', 'Full premium access with yearly billing discount', 'premium', '99.99', 'yearly', '[\"All newsletter benefits\", \"Premium tutorials\", \"Exclusive content\", \"Priority support\", \"Ad-free experience\", \"Early access to new features\", \"2 months free\"]', 1, 3, '2025-08-06 20:06:26', '2025-08-06 20:06:26');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_transactions`
--

CREATE TABLE `subscription_transactions` (
  `id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','cancelled','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_gateway` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_response` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text,
  `color` varchar(7) DEFAULT '#007bff',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `slug`, `description`, `color`, `status`, `created_at`, `updated_at`) VALUES
(1, 'javascript', 'javascript', '', '#bca824', 'active', '2025-08-04 19:01:17', '2025-08-05 16:29:44'),
(2, 'es6', 'es6', NULL, '#007bff', 'active', '2025-08-04 19:01:17', NULL),
(3, 'modern-js', 'modern-js', NULL, '#007bff', 'active', '2025-08-04 19:01:17', NULL),
(4, 'react', 'react', NULL, '#007bff', 'active', '2025-08-04 19:01:17', NULL),
(5, 'components', 'components', NULL, '#007bff', 'active', '2025-08-04 19:01:17', NULL),
(6, 'hooks', 'hooks', NULL, '#007bff', 'active', '2025-08-04 19:01:17', NULL),
(7, 'python', 'python', NULL, '#007bff', 'active', '2025-08-04 19:01:17', NULL),
(8, 'data-structures', 'data-structures', '', '#19e155', 'active', '2025-08-04 19:01:17', '2025-08-05 16:29:15'),
(9, 'algorithms', 'algorithms', NULL, '#007bff', 'active', '2025-08-04 19:01:17', NULL),
(10, 'web-dev', 'web-dev', 'Web development', '#e21cfd', 'active', '2025-08-04 19:01:17', '2025-08-05 16:28:30'),
(11, 'frontend', 'frontend', NULL, '#007bff', 'active', '2025-08-04 19:01:17', NULL),
(12, 'backend', 'backend', 'backed application', '#007bff', 'active', '2025-08-04 19:01:17', '2025-08-22 08:06:08'),
(13, 'business', 'business', '', '#7800f0', 'active', '2025-08-28 08:33:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `unsubscribe_feedback`
--

CREATE TABLE `unsubscribe_feedback` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unsubscribe_reason` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribe_requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_viewed` tinyint(1) DEFAULT '0',
  `viewed_at` timestamp NULL DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `read_by_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unsubscribe_feedback`
--

INSERT INTO `unsubscribe_feedback` (`id`, `email`, `reason`, `unsubscribe_reason`, `feedback`, `created_at`, `unsubscribe_requested_at`, `is_viewed`, `viewed_at`, `is_read`, `read_at`, `read_by_admin`) VALUES
(1, 'musirimumoses2021@gmail.com', 'quality', 'quality', '', '2025-08-21 07:58:56', '2025-08-21 08:43:33', 1, '2025-08-21 10:20:02', 0, NULL, NULL),
(2, 'twizeyimana1elia@gmail.com', 'not_relevant', 'not_relevant', '', '2025-08-29 10:33:15', '2025-08-29 10:33:15', 0, NULL, 0, NULL, NULL),
(3, 'musirimumoses2021@gmail.com', 'quality', 'quality', '', '2025-09-01 11:24:54', '2025-09-01 11:24:54', 0, NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `oauth_provider` enum('email','linkedin','google','github') DEFAULT 'email',
  `oauth_id` varchar(255) DEFAULT NULL,
  `bio` text,
  `website` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `linkedin_profile` varchar(255) DEFAULT NULL,
  `github_profile` varchar(255) DEFAULT NULL,
  `twitter_profile` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_count` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_expires` timestamp NULL DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `is_premium` tinyint(1) DEFAULT '0',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `oauth_provider`, `oauth_id`, `bio`, `website`, `location`, `company`, `job_title`, `linkedin_profile`, `github_profile`, `twitter_profile`, `last_login`, `login_count`, `is_active`, `password_reset_token`, `password_reset_expires`, `password_hash`, `email_verified`, `email_verification_token`, `email_verification_expires`, `profile_picture`, `first_name`, `last_name`, `is_premium`, `is_admin`, `created_at`, `updated_at`) VALUES
(6, 'musirimumoses', 'musirimumoses2021@gmail.com', 'google', '107481552026431233595', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-01 11:39:49', 0, 1, NULL, NULL, '', 1, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocIvh8SnMik-sOXOdOc6ccrwJ2l-FEdJuTTq3I3M9I-Xhxdy6Q=s96-c', 'MUSIRIMU', 'Moses', 1, 0, '2025-08-09 11:59:17', '2025-09-01 11:40:08'),
(7, 'TelieChris', 'github_45622886@telieacademy.com', 'github', '45622886', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-15 08:59:20', 0, 1, NULL, NULL, '', 1, NULL, NULL, 'https://avatars.githubusercontent.com/u/45622886?v=4', 'TWIZEYIMANA', 'Elie', 0, 0, '2025-08-09 13:30:18', '2025-08-22 08:07:23'),
(8, 'Elie', 'twizeyimana12elia@gmail.com', 'google', '102116712434912023874', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-13 19:29:32', 0, 1, NULL, NULL, '', 1, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocLzZbDqEMpmpuSQaO8za_slqxAGg9IPJ_Qoyg8g1OzH8LwIswo=s96-c', 'TWIZEYIMANA', 'Elie', 0, 0, '2025-08-09 16:04:29', '2025-08-13 19:29:32'),
(12, 'Twizeyimana Elie', 'twizeyimana1elia@gmail.com', 'google', '101493482132280175519', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-29 14:59:43', 0, 1, NULL, NULL, '', 1, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocKCohfdeK0UiqBn8DtK-6lpgqD-hZI-jvD04ni6V0pC0kbuZQQO3g=s96-c', 'TWIZEYIMANA', 'Elie', 0, 0, '2025-08-09 16:28:30', '2025-08-29 14:59:43'),
(15, 'test', 'niyomuhozajeandedieu80@gmail.com', 'email', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, '$2y$10$gnx0ZApLXaOVxJ1R.Eq1xeHLSqOQB7Mw/4wF31dyV2.cBaa0EvqoC', 0, 'ed5161aebb10c53302a0fe76194166952954e47225f4a200514ae88a6f95d54f', '2025-08-15 11:41:14', NULL, 'test', 'test', 1, 0, '2025-08-14 11:41:14', '2025-08-14 11:42:35'),
(16, 'teliegroup', 'teliegroup.co@gmail.com', 'google', '111559129124899031489', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-01 06:44:11', 0, 1, NULL, NULL, '', 1, NULL, NULL, 'uploads/profile-pictures/689efb9ee6d80_1755249566.png', 'Telie', 'Group', 1, 1, '2025-08-15 09:01:32', '2025-09-01 06:44:11'),
(18, 'twizeyimanaelia', 'twizeyimanaelia@gmail.com', 'google', '103428653909698786589', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-23 13:18:39', 0, 1, NULL, NULL, '', 1, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocKaq-5ybbeGX8GLZwtPcmXjsxR4u42ZX5Tg2bSzCPwMH4gtjQ=s96-c', 'Twizeyimana', 'Elia', 1, 0, '2025-08-21 14:17:51', '2025-08-23 13:18:39');

-- --------------------------------------------------------

--
-- Table structure for table `user_material_access`
--

CREATE TABLE `user_material_access` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `accessed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `download_count` int(11) DEFAULT '0',
  `last_downloaded` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_material_access`
--

INSERT INTO `user_material_access` (`id`, `user_id`, `material_id`, `accessed_at`, `download_count`, `last_downloaded`) VALUES
(3, 18, 8, '2025-08-21 16:39:57', 1, '2025-08-21 16:39:57'),
(5, 16, 11, '2025-08-29 14:58:20', 3, '2025-08-29 14:58:20'),
(6, 6, 11, '2025-09-01 11:23:58', 4, '2025-09-01 11:23:58'),
(7, 6, 8, '2025-09-01 11:22:56', 1, '2025-09-01 11:22:56'),
(8, 6, 12, '2025-09-01 11:40:35', 1, '2025-09-01 11:40:35');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_type` enum('upvote','downvote') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `post_id`, `user_id`, `vote_type`, `created_at`, `updated_at`) VALUES
(16, 34, 12, 'upvote', '2025-08-29 10:37:08', NULL),
(17, 34, 6, 'upvote', '2025-09-01 10:43:53', NULL),
(18, 35, 6, 'upvote', '2025-09-01 10:43:57', NULL),
(19, 36, 6, 'upvote', '2025-09-01 10:44:03', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ad_placements`
--
ALTER TABLE `ad_placements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `affiliate_products`
--
ALTER TABLE `affiliate_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_post` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `comment_replies`
--
ALTER TABLE `comment_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_comment_replies_comment_id` (`comment_id`),
  ADD KEY `idx_comment_replies_status` (`status`),
  ADD KEY `idx_comment_replies_created_at` (`created_at`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_email` (`email`(191)),
  ADD KEY `idx_assigned_to` (`assigned_to`),
  ADD KEY `idx_contact_messages_status_priority` (`status`,`priority`),
  ADD KEY `idx_contact_messages_user_status` (`user_id`,`status`);

--
-- Indexes for table `contact_replies`
--
ALTER TABLE `contact_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_contact_replies_message_admin` (`message_id`,`admin_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_courses_slug` (`slug`),
  ADD KEY `idx_courses_active` (`is_active`);

--
-- Indexes for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_course` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_course_enrollments_user` (`user_id`);

--
-- Indexes for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_materials_module_id` (`module_id`),
  ADD KEY `idx_materials_active` (`is_active`),
  ADD KEY `idx_materials_file_type` (`file_type`),
  ADD KEY `idx_course_materials_cover` (`cover_image`),
  ADD KEY `idx_materials_downloads` (`download_count`),
  ADD KEY `idx_required_lesson` (`required_lesson_id`),
  ADD KEY `idx_related_lesson` (`related_lesson_id`),
  ADD KEY `idx_order` (`order_index`);

--
-- Indexes for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_module_slug` (`course_id`,`slug`),
  ADD KEY `idx_modules_course_id` (`course_id`),
  ADD KEY `idx_modules_slug` (`slug`),
  ADD KEY `idx_modules_active` (`is_active`);

--
-- Indexes for table `course_progress`
--
ALTER TABLE `course_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_post_progress` (`user_id`,`post_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `idx_course_progress_user_course` (`user_id`,`course_id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriber_id` (`subscriber_id`),
  ADD KEY `type` (`type`),
  ADD KEY `sent_at` (`sent_at`),
  ADD KEY `status` (`status`),
  ADD KEY `idx_email_logs_subscriber_type` (`subscriber_id`,`type`),
  ADD KEY `idx_email_logs_sent_status` (`sent_at`,`status`);

--
-- Indexes for table `email_verification_logs`
--
ALTER TABLE `email_verification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `media_files`
--
ALTER TABLE `media_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `file_type` (`file_type`),
  ADD KEY `uploaded_at` (`uploaded_at`);

--
-- Indexes for table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `status` (`status`),
  ADD KEY `scheduled_at` (`scheduled_at`);

--
-- Indexes for table `newsletter_sends`
--
ALTER TABLE `newsletter_sends`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_campaign_subscriber` (`campaign_id`,`subscriber_id`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `subscriber_id` (`subscriber_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_newsletter_subscribers_email` (`email`),
  ADD KEY `idx_newsletter_subscribers_active` (`is_active`),
  ADD KEY `idx_newsletter_subscribers_frequency` (`frequency`),
  ADD KEY `idx_newsletter_subscribers_verified` (`verified_at`),
  ADD KEY `idx_newsletter_user_id` (`user_id`),
  ADD KEY `idx_newsletter_subscription_type` (`subscription_type`),
  ADD KEY `idx_newsletter_payment_status` (`payment_status`),
  ADD KEY `idx_newsletter_premium_expires` (`premium_expires_at`),
  ADD KEY `idx_unsubscribe_confirmation_token` (`unsubscribe_confirmation_token`),
  ADD KEY `idx_unsubscribe_requested_at` (`unsubscribe_requested_at`);

--
-- Indexes for table `newsletter_templates`
--
ALTER TABLE `newsletter_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `oauth_tokens`
--
ALTER TABLE `oauth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_provider` (`user_id`,`provider`);

--
-- Indexes for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_posts_vote_score` (`vote_score`),
  ADD KEY `idx_posts_upvotes` (`upvotes`),
  ADD KEY `idx_posts_created_votes` (`created_at`,`vote_score`),
  ADD KEY `idx_posts_course_module` (`course_module_id`),
  ADD KEY `idx_posts_lesson_order` (`lesson_order`);

--
-- Indexes for table `post_affiliate_products`
--
ALTER TABLE `post_affiliate_products`
  ADD PRIMARY KEY (`post_id`,`affiliate_product_id`),
  ADD KEY `affiliate_product_id` (`affiliate_product_id`);

--
-- Indexes for table `post_tags`
--
ALTER TABLE `post_tags`
  ADD PRIMARY KEY (`post_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `post_views`
--
ALTER TABLE `post_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_views_post_id` (`post_id`),
  ADD KEY `idx_post_views_viewed_at` (`viewed_at`),
  ADD KEY `idx_post_views_ip_post` (`ip_address`,`post_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `subscription_transactions`
--
ALTER TABLE `subscription_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriber_id` (`subscriber_id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `payment_status` (`payment_status`),
  ADD KEY `transaction_id` (`transaction_id`(191));

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `unsubscribe_feedback`
--
ALTER TABLE `unsubscribe_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`(191)),
  ADD KEY `reason` (`reason`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_unsubscribe_feedback_email_reason` (`email`(191),`reason`),
  ADD KEY `idx_unsubscribe_feedback_created_reason` (`created_at`,`reason`),
  ADD KEY `idx_is_viewed` (`is_viewed`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_is_admin` (`is_admin`),
  ADD KEY `idx_users_oauth` (`oauth_provider`,`oauth_id`),
  ADD KEY `idx_users_email_verified` (`email_verified`),
  ADD KEY `idx_users_active` (`is_active`);

--
-- Indexes for table `user_material_access`
--
ALTER TABLE `user_material_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_material` (`user_id`,`material_id`),
  ADD KEY `idx_user_access_user_id` (`user_id`),
  ADD KEY `idx_user_access_material_id` (`material_id`),
  ADD KEY `idx_access_user_material` (`user_id`,`material_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_post_vote` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vote_type` (`vote_type`),
  ADD KEY `created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ad_placements`
--
ALTER TABLE `ad_placements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `affiliate_products`
--
ALTER TABLE `affiliate_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comment_replies`
--
ALTER TABLE `comment_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `contact_replies`
--
ALTER TABLE `contact_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_materials`
--
ALTER TABLE `course_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `course_modules`
--
ALTER TABLE `course_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `course_progress`
--
ALTER TABLE `course_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `email_verification_logs`
--
ALTER TABLE `email_verification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `media_files`
--
ALTER TABLE `media_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_sends`
--
ALTER TABLE `newsletter_sends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `newsletter_templates`
--
ALTER TABLE `newsletter_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_tokens`
--
ALTER TABLE `oauth_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `post_views`
--
ALTER TABLE `post_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscription_transactions`
--
ALTER TABLE `subscription_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `unsubscribe_feedback`
--
ALTER TABLE `unsubscribe_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_material_access`
--
ALTER TABLE `user_material_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `comment_replies`
--
ALTER TABLE `comment_replies`
  ADD CONSTRAINT `comment_replies_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contact_messages_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contact_replies`
--
ALTER TABLE `contact_replies`
  ADD CONSTRAINT `contact_replies_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `contact_messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contact_replies_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD CONSTRAINT `course_materials_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `course_modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_materials_related_lesson` FOREIGN KEY (`related_lesson_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_materials_required_lesson` FOREIGN KEY (`required_lesson_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD CONSTRAINT `course_modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_progress`
--
ALTER TABLE `course_progress`
  ADD CONSTRAINT `course_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_progress_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_progress_ibfk_3` FOREIGN KEY (`module_id`) REFERENCES `course_modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_progress_ibfk_4` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `newsletter_subscribers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verification_logs`
--
ALTER TABLE `email_verification_logs`
  ADD CONSTRAINT `email_verification_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_files`
--
ALTER TABLE `media_files`
  ADD CONSTRAINT `media_files_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  ADD CONSTRAINT `newsletter_campaigns_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `newsletter_sends`
--
ALTER TABLE `newsletter_sends`
  ADD CONSTRAINT `newsletter_sends_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `newsletter_campaigns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `newsletter_sends_ibfk_2` FOREIGN KEY (`subscriber_id`) REFERENCES `newsletter_subscribers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD CONSTRAINT `newsletter_subscribers_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `newsletter_templates`
--
ALTER TABLE `newsletter_templates`
  ADD CONSTRAINT `newsletter_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `oauth_tokens`
--
ALTER TABLE `oauth_tokens`
  ADD CONSTRAINT `oauth_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  ADD CONSTRAINT `password_reset_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_course_module` FOREIGN KEY (`course_module_id`) REFERENCES `course_modules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `post_affiliate_products`
--
ALTER TABLE `post_affiliate_products`
  ADD CONSTRAINT `post_affiliate_products_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_affiliate_products_ibfk_2` FOREIGN KEY (`affiliate_product_id`) REFERENCES `affiliate_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_tags`
--
ALTER TABLE `post_tags`
  ADD CONSTRAINT `post_tags_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_views`
--
ALTER TABLE `post_views`
  ADD CONSTRAINT `fk_post_views_post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_views_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_transactions`
--
ALTER TABLE `subscription_transactions`
  ADD CONSTRAINT `subscription_transactions_plan_fk` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_transactions_subscriber_fk` FOREIGN KEY (`subscriber_id`) REFERENCES `newsletter_subscribers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_material_access`
--
ALTER TABLE `user_material_access`
  ADD CONSTRAINT `user_material_access_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_material_access_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `course_materials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
