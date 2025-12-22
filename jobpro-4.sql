-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 11, 2024 at 07:12 AM
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
-- Database: `jobpro`
--
CREATE DATABASE IF NOT EXISTS `jobpro` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `jobpro`;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `applied_at` datetime DEFAULT current_timestamp(),
  `cover_letter` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `user_id`, `applied_at`, `cover_letter`) VALUES
(1, 3, 1, '2024-10-05 21:33:44', 'something'),
(2, 7, 1, '2024-10-05 21:33:52', ' '),
(3, 5, 1, '2024-10-05 21:34:04', NULL),
(9, 4, 1, '2024-10-05 21:51:06', 'cover letter'),
(10, 7, 9, '2024-10-05 23:41:20', NULL),
(12, 5, 9, '2024-10-06 00:22:28', NULL),
(13, 4, 9, '2024-10-07 00:30:38', NULL),
(15, 7, 12, '2024-10-07 15:55:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `company_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `website_link` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`company_id`, `user_id`, `company_name`, `website_link`, `address`) VALUES
(1, 1, 'Majestta', 'majestta.com', 'colombo'),
(2, 2, 'BlitzVerse', 'blitzverse.com', 'colombo'),
(3, 4, 'WSO2', 'wso2.com', 'colombo'),
(4, 6, 'Inspirex', 'web.com', 'colombo'),
(5, 8, 'Shammu', 'shammu.com', 'colombo');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `job_category` varchar(255) NOT NULL,
  `job_location` varchar(255) NOT NULL,
  `job_type` varchar(100) NOT NULL,
  `min_salary` decimal(10,2) NOT NULL,
  `max_salary` decimal(10,2) NOT NULL,
  `skill_required` varchar(255) NOT NULL,
  `notice_period` varchar(100) NOT NULL,
  `joining_facility` varchar(255) NOT NULL,
  `job_description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `company_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT '/jobpro/assets/images/jobPost1.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `job_title`, `job_category`, `job_location`, `job_type`, `min_salary`, `max_salary`, `skill_required`, `notice_period`, `joining_facility`, `job_description`, `created_at`, `company_id`, `image`) VALUES
(3, 'UI', 'Software Development', 'Tokyo', 'Full-time', '700000.00', '800000.00', 'Figma', '1 month', 'Accomodation', 'design ui elements to give users great ux', '2024-10-05 07:09:33', 1, '/jobpro/assets/images/jobPost1.jpg'),
(4, 'Software Engineer Intern', 'Software Development', 'New York', 'Part-time', '79998.00', '200000.00', 'MERN', '1 month', 'Fair Compensation', 'An intern position with great compensation', '2024-10-05 09:39:25', 1, '/jobpro/assets/images/jobPost2.jpg'),
(5, 'Data Scientist', 'Data Science', 'Colombo', 'Part-time', '89000.00', '120000.00', 'Data Science, Python', '1 month', 'Facilities', 'Description', '2024-10-05 09:50:11', 5, '/jobpro/assets/images/jobPost3.jpg'),
(7, 'Data Analyst', 'Data Science', 'Colombo', 'Full-time', '70006.00', '100034.00', 'Data Analysis, Python', '1 month', 'Great Facility', 'Job Description\\r\\n', '2024-10-05 14:33:09', 1, '/jobpro/assets/images/jobPost4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `person`
--

CREATE TABLE `person` (
  `person_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `year_of_experience` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `portfolio_link` varchar(255) DEFAULT NULL,
  `cv_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `person`
--

INSERT INTO `person` (`person_id`, `user_id`, `full_name`, `qualification`, `year_of_experience`, `location`, `city`, `zip_code`, `country`, `portfolio_link`, `cv_link`) VALUES
(1, 9, 'Hanafe Mira', 'B.Sc.', 3, 'colombo', 'colombo', '00600', '', 'portfolio.com', 'cv.com'),
(3, 12, 'Hanafe Mira', 'B.Sc', 3, 'colombo', 'colombo', '00600', '', 'self1am.com', 'hanafe_cv.com');

-- --------------------------------------------------------

--
-- Table structure for table `terms_acceptance`
--

CREATE TABLE `terms_acceptance` (
  `acceptance_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `terms_accepted` tinyint(1) DEFAULT 0,
  `details_correct` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `account_type` enum('personal','company') NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password`, `phone_number`, `account_type`, `profile_image`, `created_at`) VALUES
(1, 'Majestta', 'majestta@gmail.com', '$2y$10$oxqaTofiUck2jXVMnY7yRufdwSHsbxniKMfQWn/QxsNUGnyD/pkcG', '0771234567', 'company', '', '2024-10-05 02:09:38'),
(2, 'BlitzVerse', 'blitz.inc@gmail.com', '$2y$10$l/wDFJvBGctoWGKpZ/WwR.rEBwzF.KQYbVpnh/Em5Rl.q8rCB5Nya', '0789123456', 'company', '', '2024-10-05 02:46:55'),
(4, 'wso2', 'wso@mail.com', '$2y$10$Gw0BlxOMv/IYnzg7k4Ojk.RcrQzxZ3hq4qDS2oN.OxM.mHsCMV6Jy', '0771234567', 'company', '', '2024-10-05 03:05:44'),
(6, 'Inspirex', 'insp.rex@gmail.com', '$2y$10$i4QU3XE40crsffe5oCvDAOrTNX8nBRFRUmYhzLO.8kJ0WrftOfjYq', '0771234567', 'company', '', '2024-10-05 05:33:46'),
(7, 'Shamah', 'shamah@gmail.com', '$2y$10$/gVMO8jHAvhx7.5f3ifC0eB1muCbslIAdPx5iHiSwxoJqkDa5BDuO', '0771234567', 'personal', '', '2024-10-05 05:49:00'),
(8, 'Shammu', 'sham@gmail.com', '$2y$10$cZc/1py0mY1LHmqIxei36eUZYpcHmnEChv/fo0IjHoG/gDmTgROBW', '0777123456', 'company', '', '2024-10-05 09:49:08'),
(9, 'Hanafe', 'miraahanafee@gmail.com', '$2y$10$D2n3RZ6AqC3wDW4zA2yri.RZAyp9NCE4f/f30L6LiwFDRuxUVDnpq', '0763811834', 'personal', '', '2024-10-05 11:54:23'),
(12, 'self1am', 'hanafee@gmail.com', '$2y$10$TBPi9H5xK5vfOvwv30bYRex1rSQgh7evxrgWG6xkc7/upPmP6qVoa', '0771234567', 'personal', '', '2024-10-07 09:30:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `applications_ibfk_2` (`user_id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`person_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `terms_acceptance`
--
ALTER TABLE `terms_acceptance`
  ADD PRIMARY KEY (`acceptance_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `person`
--
ALTER TABLE `person`
  MODIFY `person_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `terms_acceptance`
--
ALTER TABLE `terms_acceptance`
  MODIFY `acceptance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `company`
--
ALTER TABLE `company`
  ADD CONSTRAINT `company_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`);

--
-- Constraints for table `person`
--
ALTER TABLE `person`
  ADD CONSTRAINT `person_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `terms_acceptance`
--
ALTER TABLE `terms_acceptance`
  ADD CONSTRAINT `terms_acceptance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
