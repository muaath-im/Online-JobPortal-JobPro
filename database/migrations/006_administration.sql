-- Phase 6: select the existing application database before running this file.
-- Idempotent additions; existing rows stay active and visible.
-- MySQL DDL implicitly commits. Preserve the private backup before running.

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `user` ADD COLUMN `account_status` ENUM(''active'',''suspended'') NOT NULL DEFAULT ''active''',
  'SELECT ''user.account_status already exists'' AS result')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='user' AND COLUMN_NAME='account_status');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `user` ADD COLUMN `account_status_reason` VARCHAR(500) NULL DEFAULT NULL',
  'SELECT ''user.account_status_reason already exists'' AS result')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='user' AND COLUMN_NAME='account_status_reason');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `user` ADD COLUMN `account_status_updated_at` TIMESTAMP NULL DEFAULT NULL',
  'SELECT ''user.account_status_updated_at already exists'' AS result')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='user' AND COLUMN_NAME='account_status_updated_at');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `jobs` ADD COLUMN `moderation_status` ENUM(''visible'',''hidden'') NOT NULL DEFAULT ''visible''',
  'SELECT ''jobs.moderation_status already exists'' AS result')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jobs' AND COLUMN_NAME='moderation_status');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `jobs` ADD COLUMN `moderation_reason` VARCHAR(500) NULL DEFAULT NULL',
  'SELECT ''jobs.moderation_reason already exists'' AS result')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jobs' AND COLUMN_NAME='moderation_reason');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `jobs` ADD COLUMN `moderated_at` TIMESTAMP NULL DEFAULT NULL',
  'SELECT ''jobs.moderated_at already exists'' AS result')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jobs' AND COLUMN_NAME='moderated_at');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

CREATE TABLE IF NOT EXISTS `admin_memberships` (
  `user_id` INT NOT NULL,
  `role` ENUM('owner','moderator') NOT NULL,
  `granted_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  KEY `admin_role` (`role`),
  CONSTRAINT `admin_member_user_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `user` ADD KEY `user_account_status` (`account_status`,`account_type`,`created_at`)',
  'SELECT ''user_account_status already exists'' AS result')
  FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='user' AND INDEX_NAME='user_account_status');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `jobs` ADD KEY `jobs_moderation` (`moderation_status`,`publication_status`,`created_at`)',
  'SELECT ''jobs_moderation already exists'' AS result')
  FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jobs' AND INDEX_NAME='jobs_moderation');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;
SET @jobpro_ddl = NULL;
