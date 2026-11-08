-- JobPro Idempotent Relational Schema (MySQL 8.0 & TiDB Serverless Compatible)
-- Character set: utf8mb4, Collation: utf8mb4_general_ci

USE jobpro;
SET NAMES utf8mb4;

-- 1. Users Table (Authentication & Accounts)
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `account_type` enum('personal','company') NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `account_status` ENUM('active','suspended') NOT NULL DEFAULT 'active',
  `account_status_reason` VARCHAR(500) NULL DEFAULT NULL,
  `account_status_updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `user_account_status` (`account_status`,`account_type`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Company Table (Employer Profiles)
CREATE TABLE IF NOT EXISTS `company` (
  `company_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `website_link` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`company_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `company_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Jobs Table (Job Postings & Moderation)
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `image` varchar(255) DEFAULT '/jobpro/assets/images/jobPost1.jpg',
  `publication_status` ENUM('draft','open','closed') NOT NULL DEFAULT 'open',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `moderation_status` ENUM('visible','hidden') NOT NULL DEFAULT 'visible',
  `moderation_reason` VARCHAR(500) NULL DEFAULT NULL,
  `moderated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `jobs_company_status` (`company_id`,`publication_status`,`created_at`),
  KEY `jobs_moderation` (`moderation_status`,`publication_status`,`created_at`),
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Applications Table (Candidate Applications & Stage Pipeline)
CREATE TABLE IF NOT EXISTS `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `applied_at` datetime DEFAULT current_timestamp(),
  `cover_letter` TEXT DEFAULT NULL,
  `status` ENUM('submitted','reviewing','shortlisted','interview','offered','hired','rejected') NOT NULL DEFAULT 'submitted',
  `status_updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `applications_ibfk_2` (`user_id`),
  KEY `applications_job_status` (`job_id`,`status`,`applied_at`),
  CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`),
  CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Person Table (Candidate Profiles)
CREATE TABLE IF NOT EXISTS `person` (
  `person_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `year_of_experience` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `portfolio_link` varchar(255) DEFAULT NULL,
  `cv_link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`person_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `person_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Terms Acceptance Table
CREATE TABLE IF NOT EXISTS `terms_acceptance` (
  `acceptance_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `terms_accepted` tinyint(1) DEFAULT 0,
  `details_correct` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`acceptance_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `terms_acceptance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Saved Jobs Table (Bookmarks)
CREATE TABLE IF NOT EXISTS `saved_jobs` (
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`, `job_id`),
  KEY `saved_jobs_date` (`user_id`, `saved_at`),
  KEY `saved_jobs_job` (`job_id`),
  CONSTRAINT `saved_jobs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `saved_jobs_job_fk` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Admin Memberships Table (RBAC)
CREATE TABLE IF NOT EXISTS `admin_memberships` (
  `user_id` INT NOT NULL,
  `role` ENUM('owner','moderator') NOT NULL,
  `granted_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  KEY `admin_role` (`role`),
  CONSTRAINT `admin_member_user_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. Admin Audit Log Table (Immutable Audit Trail)
CREATE TABLE IF NOT EXISTS `admin_audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_user_id` INT NULL,
  `action` VARCHAR(50) NOT NULL,
  `target_type` VARCHAR(24) NOT NULL,
  `target_id` INT NOT NULL,
  `reason` VARCHAR(500) NOT NULL,
  `before_state` JSON NULL,
  `after_state` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_created` (`created_at`,`id`),
  KEY `audit_actor` (`actor_user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
