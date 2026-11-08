-- Phase 5: run after selecting the existing application database with USE jobpro.
-- Idempotent on MySQL 8: only missing columns and indexes are added.
-- Existing rows are retained. ALTER TABLE implicitly commits in MySQL.

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `jobs` ADD COLUMN `publication_status` ENUM(''draft'',''open'',''closed'') NOT NULL DEFAULT ''open''',
  'SELECT ''jobs.publication_status already exists'' AS result')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jobs' AND COLUMN_NAME='publication_status');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `jobs` ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
  'SELECT ''jobs.updated_at already exists'' AS result')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jobs' AND COLUMN_NAME='updated_at');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `jobs` ADD KEY `jobs_company_status` (`company_id`,`publication_status`,`created_at`)',
  'SELECT ''jobs_company_status already exists'' AS result')
  FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jobs' AND INDEX_NAME='jobs_company_status');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `applications` ADD COLUMN `status` ENUM(''submitted'',''reviewing'',''shortlisted'',''interview'',''offered'',''hired'',''rejected'') NOT NULL DEFAULT ''submitted''',
  'SELECT ''applications.status already exists'' AS result')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='applications' AND COLUMN_NAME='status');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `applications` ADD COLUMN `status_updated_at` TIMESTAMP NULL DEFAULT NULL',
  'SELECT ''applications.status_updated_at already exists'' AS result')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='applications' AND COLUMN_NAME='status_updated_at');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;

SET @jobpro_ddl = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `applications` ADD KEY `applications_job_status` (`job_id`,`status`,`applied_at`)',
  'SELECT ''applications_job_status already exists'' AS result')
  FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='applications' AND INDEX_NAME='applications_job_status');
PREPARE jobpro_migration FROM @jobpro_ddl;
EXECUTE jobpro_migration;
DEALLOCATE PREPARE jobpro_migration;
SET @jobpro_ddl = NULL;