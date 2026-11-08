-- JobPro High-Fidelity Recruiter Seed Data for TiDB Serverless & MySQL 8.0
-- Pre-seeds realistic companies, jobs, candidate profiles, and demo accounts.
-- All demo accounts use the standard password: DemoPass2026!

USE jobpro;

-- Clean existing demo data safely
DELETE FROM `saved_jobs`;
DELETE FROM `applications`;
DELETE FROM `jobs`;
DELETE FROM `company`;
DELETE FROM `person`;
DELETE FROM `terms_acceptance`;
DELETE FROM `admin_memberships`;
DELETE FROM `admin_audit_log`;
DELETE FROM `user`;

-- 1. Insert Demo Users (All with password: DemoPass2026!)
INSERT INTO `user` (`user_id`, `username`, `email`, `password`, `phone_number`, `account_type`, `account_status`) VALUES
(1, 'Admin', 'admin@jobpro.demo', '$2y$10$jak7MKXPcW12i/QkCZkj5uDlefpR/AfdUGyiwBjHUi3nW4ztg0IM6', '+1 555-0100', 'company', 'active'),
(2, 'CloudScale Solutions', 'employer@jobpro.demo', '$2y$10$jak7MKXPcW12i/QkCZkj5uDlefpR/AfdUGyiwBjHUi3nW4ztg0IM6', '+1 555-0101', 'company', 'active'),
(3, 'Nexus FinTech', 'fintech@jobpro.demo', '$2y$10$jak7MKXPcW12i/QkCZkj5uDlefpR/AfdUGyiwBjHUi3nW4ztg0IM6', '+1 555-0102', 'company', 'active'),
(4, 'Apex Creative Media', 'apex@jobpro.demo', '$2y$10$jak7MKXPcW12i/QkCZkj5uDlefpR/AfdUGyiwBjHUi3nW4ztg0IM6', '+1 555-0103', 'company', 'active'),
(5, 'Alex Morgan', 'candidate@jobpro.demo', '$2y$10$jak7MKXPcW12i/QkCZkj5uDlefpR/AfdUGyiwBjHUi3nW4ztg0IM6', '+1 555-0104', 'personal', 'active'),
(6, 'Sarah Chen', 'sarah.chen@example.com', '$2y$10$jak7MKXPcW12i/QkCZkj5uDlefpR/AfdUGyiwBjHUi3nW4ztg0IM6', '+1 555-0105', 'personal', 'active');

-- 2. Insert Admin Role
INSERT INTO `admin_memberships` (`user_id`, `role`, `granted_by`) VALUES
(1, 'owner', 1);

-- 3. Insert Terms Acceptance
INSERT INTO `terms_acceptance` (`acceptance_id`, `user_id`, `terms_accepted`, `details_correct`) VALUES
(1, 1, 1, 1),
(2, 2, 1, 1),
(3, 3, 1, 1),
(4, 4, 1, 1),
(5, 5, 1, 1),
(6, 6, 1, 1);

-- 4. Insert Company Profiles
INSERT INTO `company` (`company_id`, `user_id`, `company_name`, `website_link`, `address`) VALUES
(1, 2, 'CloudScale Solutions', 'https://cloudscale.example.com', 'Seattle, WA, USA'),
(2, 3, 'Nexus FinTech', 'https://nexusfintech.example.com', 'New York, NY, USA'),
(3, 4, 'Apex Creative Media', 'https://apexmedia.example.com', 'San Francisco, CA, USA');

-- 5. Insert Candidate Profiles
INSERT INTO `person` (`person_id`, `user_id`, `full_name`, `qualification`, `year_of_experience`, `location`, `city`, `zip_code`, `country`, `portfolio_link`, `cv_link`) VALUES
(1, 5, 'Alex Morgan', 'B.S. in Computer Science', 3, 'Colombo', 'Western Province', '00100', 'Sri Lanka', 'https://alexmorgan.dev', 'Uploaded Resume'),
(2, 6, 'Sarah Chen', 'M.S. in Software Engineering', 2, 'Singapore', 'Singapore', '048581', 'Singapore', 'https://sarahchen.dev', 'Uploaded Resume');

-- 6. Insert Realistic Job Postings
INSERT INTO `jobs` (`id`, `job_title`, `job_category`, `job_location`, `job_type`, `min_salary`, `max_salary`, `skill_required`, `notice_period`, `joining_facility`, `job_description`, `company_id`, `image`, `publication_status`, `moderation_status`) VALUES
(1, 'Senior Full-Stack Engineer', 'Technology', 'Remote (US / EU / Asia)', 'Full-Time', 120000.00, 155000.00, 'PHP 8, MySQL, JavaScript, Systems Architecture', '1 Month', 'Home office stipend, Health insurance, 401(k)', 'We are looking for a Senior Full-Stack Engineer to lead the architectural evolution of our core platform. You will build high-throughput REST APIs, optimize relational database queries, and design responsive user interfaces.', 1, '/jobpro/assets/images/jobPost1.jpg', 'open', 'visible'),
(2, 'DevOps & Cloud Systems Engineer', 'Technology', 'Remote', 'Full-Time', 110000.00, 145000.00, 'Linux, Docker, Nginx, CI/CD, MySQL', 'Immediately', 'Flexible working hours, Annual learning budget', 'Join our infrastructure team to manage high-availability web services, automated deployment pipelines, and zero-downtime server migrations.', 1, '/jobpro/assets/images/jobPost2.jpg', 'open', 'visible'),
(3, 'Product Designer (UI/UX)', 'Design', 'San Francisco, CA', 'Full-Time', 95000.00, 130000.00, 'Figma, Design Systems, Prototyping, Accessibility', '2 Weeks', 'Commuter benefits, Latest hardware equipment', 'Design intuitive, accessible user journeys and data-dense dashboards for hiring managers and candidate workspaces.', 3, '/jobpro/assets/images/jobPost3.jpg', 'open', 'visible'),
(4, 'Backend Engineer (PHP / Database)', 'Technology', 'Remote', 'Full-Time', 105000.00, 135000.00, 'PHP 8.4, MySQL Optimization, REST APIs, Security', '1 Month', 'Remote setup allowance, Stock options', 'Responsible for core business services, transaction integrity, database indexing strategies, and defensive security engineering.', 2, '/jobpro/assets/images/jobPost4.jpg', 'open', 'visible'),
(5, 'Associate Software Engineer', 'Technology', 'Remote', 'Full-Time', 70000.00, 90000.00, 'JavaScript, HTML5/CSS3, SQL, Problem Solving', 'Immediately', 'Dedicated mentorship, Certification budget', 'Exciting opportunity for an early-career software engineer to work alongside senior architects and build scalable web platforms.', 1, '/jobpro/assets/images/jobPost1.jpg', 'open', 'visible'),
(6, 'Growth Marketing & Talent Partner', 'Marketing', 'New York, NY', 'Full-Time', 80000.00, 110000.00, 'Content Strategy, SEO, Employer Branding', 'Immediately', 'Performance bonuses, Comprehensive health coverage', 'Lead marketing initiatives to connect visionary employers with top-tier technical and creative candidates across North America.', 2, '/jobpro/assets/images/jobPost2.jpg', 'open', 'visible');

-- 7. Insert Sample Applications
INSERT INTO `applications` (`id`, `job_id`, `user_id`, `status`, `cover_letter`, `applied_at`) VALUES
(1, 1, 5, 'interview', 'I am excited to submit my application for the Senior Full-Stack Engineer role. With extensive hands-on experience in native PHP and MySQL architectures, I look forward to discussing how I can add immediate value to the team.', NOW() - INTERVAL 3 DAY),
(2, 4, 5, 'shortlisted', 'With hands-on experience in database indexing and defensive web security, I would love to contribute to Nexus FinTech as a Backend Engineer.', NOW() - INTERVAL 1 DAY),
(3, 5, 6, 'reviewing', 'I am very interested in the Associate Software Engineer opportunity. I have a strong foundation in computer science and web fundamentals.', NOW() - INTERVAL 4 HOUR);

-- 8. Insert Bookmarked Jobs
INSERT INTO `saved_jobs` (`user_id`, `job_id`, `saved_at`) VALUES
(5, 2, NOW() - INTERVAL 2 DAY),
(5, 3, NOW() - INTERVAL 1 DAY);
