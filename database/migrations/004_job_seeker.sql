-- Phase 4: additive candidate features. Select the existing database first.
-- Expands existing notes without deleting rows. Do not reimport schema.sql over data.
ALTER TABLE `applications` MODIFY `cover_letter` TEXT DEFAULT NULL;

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
