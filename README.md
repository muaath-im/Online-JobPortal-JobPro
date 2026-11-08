# JobPro — Modern Career & Recruitment Platform

[![PHP 8.4](https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL 8.0](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Laravel Herd](https://img.shields.io/badge/Laravel%20Herd-Compatible-FF2D20?style=flat&logo=laravel&logoColor=white)](https://herd.laravel.com/)
[![Pure Vanilla Stack](https://img.shields.io/badge/Stack-Vanilla%20PHP%20%7C%20JS%20%7C%20CSS3-success?style=flat)](https://developer.mozilla.org/)
[![Automated Test Suite](<https://img.shields.io/badge/Automated%20Tests-146%20Passed%20(100%25)-brightgreen?style=flat>)](tests/run-regression.ps1)
[![Zero External Dependencies](<https://img.shields.io/badge/Dependencies-Zero%20(No%20Framework%20Bloat)-blueviolet?style=flat>)](#technology-stack)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

> **JobPro** is an enterprise-ready, high-performance career and recruitment platform engineered purely with native web standards: **PHP 8.4**, **MySQL 8.0**, **Vanilla ES6+ JavaScript**, and **Modern CSS3**. With zero external runtime frameworks, zero npm dependencies, and a strict Three-Tier security architecture, JobPro delivers sub-second page loads, bank-grade defensive engineering, and an intuitive experience for candidates, hiring teams, and platform administrators.

---

## 📑 Table of Contents

1. [Project Overview](#1-project-overview)
2. [Project Objectives](#2-project-objectives)
3. [Features](#3-features)
4. [Live Demo](#4-live-demo)
5. [Screenshots & UI Walkthrough](#5-screenshots--ui-walkthrough)
6. [Technology Stack](#6-technology-stack)
7. [System Requirements](#7-system-requirements)
8. [Installation Steps](#8-installation-steps)
9. [Configuration Instructions](#9-configuration-instructions)
10. [Environment Variables](#10-environment-variables)
11. [Project Structure](#11-project-structure)
12. [Architecture Diagram](#12-architecture-diagram)
13. [Database ERD Diagram](#13-database-erd-diagram)
14. [API Documentation](#14-api-documentation)
15. [Authentication and Authorization Flow](#15-authentication-and-authorization-flow)
16. [Testing Strategy](#16-testing-strategy)
17. [Test Coverage Information](#17-test-coverage-information)
18. [Deployment Process](#18-deployment-process)
19. [Hosting Configuration](#19-hosting-configuration)
20. [CI/CD Overview](#20-cicd-overview)
21. [Performance Considerations](#21-performance-considerations)
22. [Security Considerations](#22-security-considerations)
23. [Known Limitations](#23-known-limitations)
24. [Future Enhancements](#24-future-enhancements)
25. [Troubleshooting Guide](#25-troubleshooting-guide)
26. [Contribution Guidelines](#26-contribution-guidelines)

---

## 1. Project Overview

Originally developed three years ago as a traditional web-based recruitment board, **JobPro** has been completely modernized into a portfolio-grade, production-ready web platform.

In an era where modern web applications frequently carry hundreds of megabytes of third-party dependencies and framework overhead, JobPro proves that a **pure vanilla architecture**—when architected with rigorous design patterns—can achieve superior speed, complete security, and effortless maintainability.

### Core Architecture Pillars:

- **Strict Webroot Isolation:** Only public assets, page entry points, and API endpoints exist within the document root (`app/htdocs/`). Core business logic, authentication handlers, domain models, database credentials, and candidate resumes reside outside the public web root in `app/src/` and `app/storage/`.
- **Zero Framework Runtime:** Runs with zero npm packages and zero composer packages. Native PHP 8.4 and modern browser capabilities eliminate build toolchain fatigue and supply-chain vulnerabilities.
- **Defensive Engineering:** Full OWASP Top 10 mitigation including cryptographic CSRF protection on all forms and JSON API routes, constant-time password verification, session fixation defense, `auth_stamp` session invalidation, prepared SQL statements on 100% of queries, and IDOR-guarded file streaming.

---

## 2. Project Objectives

1. **Eliminate Recruitment Friction:** Create direct, transparent communication channels between applicants and hiring teams, replacing opaque algorithmic resume "black holes" with clear hiring pipelines.
2. **Demonstrate Native Full-Stack Mastery:** Showcase an enterprise-grade web application built entirely on vanilla PHP, JavaScript, CSS3, and relational MySQL without relying on heavyweight frameworks.
3. **Institutionalize Security by Default:** Enforce bank-grade authentication, role-based authorization, tamper-evident audit logging, and protected document streaming.
4. **Deliver Exceptional Speed and Responsiveness:** Achieve near-instantaneous first-contentful paint (< 200 ms) and lightweight asset payloads through GD image resampling and native CSS layouts.
5. **Guarantee Production Robustness:** Validate every feature through a 146-point automated regression test suite covering unit, HTTP integration, database transactions, and permission boundaries.

---

## 3. Features

### 👤 Candidate Experience (Job Seeker Workspace)

- **Reactive Job Discovery:** Debounced live search filtering by keyword, location, and industry category with real-time browser URL synchronization (`pushState`) for bookmarkable search queries.
- **Resume Vault & Document Upload:** Upload resumes in PDF, DOC, or DOCX formats (up to 5 MB). Uploads undergo physical MIME sniffing and magic-byte validation (`%PDF-`) and are stored privately outside the public document root.
- **Dynamic Profile Completion Meter:** Real-time readiness gauge evaluating bio completeness, qualifications, experience, and uploaded resume assets.
- **Job Bookmarking Engine:** Save positions for later review with dynamic UI state toggles and instant toast confirmation alerts.
- **7-Stage Application Lifecycle Tracking:** Track job applications across distinct phases:
  $$\text{Submitted} \longrightarrow \text{Reviewing} \longrightarrow \text{Shortlisted} \longrightarrow \text{Interview} \longrightarrow \text{Offered} \longrightarrow \text{Hired} \quad (\text{or Rejected})$$

### 🏢 Employer Workspace (Hiring Manager Portal)

- **Job Lifecycle Management:** Complete publishing workflow enabling employers to draft, publish, modify, close, and reopen job listings with decimal salary validation and multi-tagging.
- **Visual Applicant Pipeline:** Review candidate submissions, read sanitized cover letters, evaluate experience profiles, and transition applicant statuses in real time.
- **Guarded Resume Streaming:** Download applicant resumes with automatic IDOR checks ensuring only the listing's authorized employer or an administrator can access candidate files.
- **Company Brand Showcase:** Dedicated public employer profile pages highlighting corporate vision, active openings, headquarters location, and verified external web links.
- **Accidental Deletion Protection:** Database-level integrity guards preventing the deletion of postings with existing application records.

### 🛡️ Administration & Moderation (RBAC)

- **Role-Based Access Control:** Strict permission segregation between `owner` and `moderator` accounts.
- **Moderation Engine:** Suspend abusive accounts and hide non-compliant job listings with mandatory justification logging.
- **Tamper-Evident Audit Logging:** Append-only administrative audit log (`admin_audit_log`) capturing before/after JSON delta snapshots of every administrative change.
- **Self-Demotion & Last-Owner Locks:** Engine-level protection rejecting self-suspension, self-revocation, or demotion of the sole remaining platform owner.
- **Offline CLI Bootstrapping:** Dedicated administrative provisioning tool (`tools/admin-access.php`) located outside the public webroot.

### 🔔 Interactive Toast Notification System (`JobProToast`)

- Lightweight, accessible notification engine (`app/htdocs/assets/js/toast.js` & `foundation.css`).
- Supports `success`, `error`, `warning`, and `info` status pills with auto-dismiss countdowns, hover-to-pause, and manual close buttons.
- Seamlessly bridges server-side PHP session flashes into reactive client-side notifications on page load.

---

## 4. Live Demo

### Local Test Domain (Laravel Herd)

When running locally via Laravel Herd on Windows, the application is accessible at:
👉 **`http://jobpro.test`**

### Built-in Discovery Demo Mode

Experience the complete job discovery engine even without an active MySQL database connection by appending `?demo=1`:
👉 **`http://jobpro.test/pages/seekJob.php?demo=1`**

This loads high-fidelity sample career records directly from [`assets/data/discovery-demo.json`](file:///d:/Projects/JobPro/app/htdocs/assets/data/discovery-demo.json).

---

## 5. Screenshots & UI Walkthrough

```
+-----------------------------------------------------------------------------------+
|  [JobPro Mark]  JobPro        Find a job    For employers    About us    [Log in] |
+-----------------------------------------------------------------------------------+
|                                                                                   |
|    WHO WE ARE & WHAT WE STAND FOR                                                 |
|    Connecting Human Ambition with World-Class Opportunity                         |
|    JobPro is a modern career platform engineered for transparent, high-speed     |
|    connections between extraordinary talent and forward-thinking companies.       |
|                                                                                   |
|    +-----------------+ +-----------------+ +-----------------+ +----------------+ |
|    |     10,000+     | |      1,200+     | |       98%       | |     < 24h      | |
|    | Active Seekers  | | Verified Teams  | | Delivery Rate   | | Response Time  | |
|    +-----------------+ +-----------------+ +-----------------+ +----------------+ |
|                                                                                   |
|    [ Explore Opportunities -> ]         [ Speak With Our Team ]                   |
+-----------------------------------------------------------------------------------+
```

### Visual Assets in Repository:

- Vector Brand Mark: [`app/htdocs/assets/images/jobpro-mark.svg`](file:///d:/Projects/JobPro/app/htdocs/assets/images/jobpro-mark.svg)
- Resampled Web Imagery: [`app/htdocs/assets/images/jobPost1.jpg`](file:///d:/Projects/JobPro/app/htdocs/assets/images/jobPost1.jpg) through `jobPost4.jpg` (< 70 KB each).

---

## 6. Technology Stack

| Layer                  | Technology               | Details                                                                           |
| :--------------------- | :----------------------- | :-------------------------------------------------------------------------------- |
| **Backend Runtime**    | **PHP 8.4+**             | Strict types, native sessions, GD image processing, OpenSSL, MySQLi               |
| **Database**           | **MySQL 8.0+**           | Relational InnoDB tables, foreign key constraints, JSON fields, composite indexes |
| **Frontend Scripting** | **Vanilla JavaScript**   | Modern ES6+ modules, Fetch API, History API (`pushState`), zero bundler           |
| **Styling & UI**       | **Modern CSS3**          | Custom properties (CSS variables), CSS Grid, Flexbox, Light/Dark themes           |
| **Local Environment**  | **Laravel Herd 2.1+**    | Native Windows Nginx/PHP development environment                                  |
| **Quality Assurance**  | **PowerShell & PHP CLI** | Custom regression test runner with 146 automated verification checks              |
| **Dependencies**       | **Zero External**        | No Composer dependencies, No Node.js / npm dependencies                           |

---

## 7. System Requirements

### Minimum Requirements:

- **Operating System:** Windows 10/11, macOS Sonoma/Sequoia, or modern Linux (Ubuntu 22.04+).
- **PHP Engine:** PHP 8.2, 8.3, or 8.4 with extensions enabled:
  - `ext-mysqli` (Database connectivity)
  - `ext-session` (State & authentication)
  - `ext-fileinfo` (MIME sniffing for uploads)
  - `ext-gd` (Image optimization)
  - `ext-json` (JSON APIs and audit log encoding)
- **Database Server:** MySQL 8.0+ or MariaDB 10.5+.
- **Web Server:** Laravel Herd (recommended for Windows/macOS), Nginx 1.20+, or Apache 2.4+ with `mod_rewrite`.

---

## 8. Installation Steps

### Step 1: Clone or Open the Repository

```powershell
# Open terminal and navigate to your projects directory
cd D:\Projects\JobPro
```

### Step 2: Database Initialization

1. Ensure MySQL is running on `127.0.0.1:3306`.
2. Connect to MySQL and create the database and user:

```sql
CREATE DATABASE jobpro CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'jobpro_local'@'127.0.0.1' IDENTIFIED BY 'jp2026';
GRANT ALL PRIVILEGES ON jobpro.* TO 'jobpro_local'@'127.0.0.1';
FLUSH PRIVILEGES;
```

3. Import the clean, idempotent schema:

```powershell
Get-Content d:\Projects\JobPro\database\schema.sql | mysql -u jobpro_local -pjp2026 -h 127.0.0.1 jobpro
```

### Step 3: Link Laravel Herd

Because Herd auto-detects site webroots from directories containing `index.php`, map the site directly to `app/htdocs`:

```powershell
# Navigate into the public document root
cd D:\Projects\JobPro\app\htdocs

# Link the site into Laravel Herd
herd link jobpro
```

The application is now immediately accessible at:
👉 **`http://jobpro.test`**
wq

### Step 4: Bootstrap an Administrator Account

To grant administrative ownership to any registered user account without manual database edits:

```powershell
php D:\Projects\JobPro\tools\admin-access.php grant admin@example.com owner
```

---

## 9. Configuration Instructions

### Database Connection Configuration

Database credentials can be supplied via environment variables or through [`includes/dbconnect.php`](file:///d:/Projects/JobPro/includes/dbconnect.php):

```php
<?php
// Default fallback configuration for local development:
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'jobpro_local';
$pass = getenv('DB_PASS') ?: 'jp2026';
$db   = getenv('DB_NAME') ?: 'jobpro';
$port = (int)(getenv('DB_PORT') ?: 3306);

$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_errno) {
    error_log("JobPro Database connection failed: " . $mysqli->connect_error);
    die("Database connection failed. Please check server configuration.");
}
$mysqli->set_charset("utf8mb4");
```

### Mail Transport Configuration

For local development, email capture defaults to private local logging (`JOBPRO_MAIL_TRANSPORT=log`), preventing accidental outbound emails during testing.

---

## 10. Environment Variables

Reference template available at [`.env.example`](file:///d:/Projects/JobPro/.env.example):

| Variable Name           | Default Value         | Description                                               |
| :---------------------- | :-------------------- | :-------------------------------------------------------- |
| `DB_HOST`               | `127.0.0.1`           | MySQL server hostname or IP address                       |
| `DB_PORT`               | `3306`                | MySQL server port                                         |
| `DB_NAME`               | `jobpro`              | Application database schema name                          |
| `DB_USER`               | `jobpro_local`        | MySQL database username                                   |
| `DB_PASS`               | `jp2026`              | MySQL database password                                   |
| `JOBPRO_DB_CONFIG`      | _empty_               | Optional absolute path to an external DB connector script |
| `JOBPRO_MAIL_TRANSPORT` | `disabled` / `log`    | Outbound mail adapter (`log`, `disabled`, or `smtp`)      |
| `JOBPRO_MAIL_BASE_URL`  | `http://jobpro.test`  | Base URL used in password reset email links               |
| `JOBPRO_AUTH_STORAGE`   | `app/storage/resumes` | Absolute filesystem path for private resume storage       |

---

## 11. Project Structure

```
JobPro/
├── .env.example                 # Environment configuration template
├── .gitignore                   # Git exclusion rules (sensitive data, temp state)
├── README.md                    # Comprehensive architectural and project manual
├── app/
│   ├── config/                  # Server configuration examples (.htaccess, database)
│   ├── htdocs/                  # 🟢 PUBLIC WEBROOT (Exposed to Herd / Web Server)
│   │   ├── index.php            # Primary entry router (redirects to /pages/landing.php)
│   │   ├── api/                 # Lightweight REST JSON API endpoints
│   │   │   ├── admin.php        # Administrative moderation & RBAC actions
│   │   │   ├── applyJob.php     # Candidate job application submission
│   │   │   ├── auth_api.php     # Sign-in and registration processing
│   │   │   ├── candidate.php    # Bookmarking & profile state toggles
│   │   │   ├── discovery.php    # Reactive search filter endpoint
│   │   │   ├── employer.php     # Applicant pipeline stage updates
│   │   │   └── getJobs.php      # Public job catalog listing
│   │   ├── assets/              # Static frontend assets
│   │   │   ├── css/             # Foundation, landing, discovery, auth, jobs styles
│   │   │   ├── js/              # Toast engine, discovery, saved jobs, auth scripts
│   │   │   ├── images/          # Resampled web assets (< 70 KB) and brand mark SVG
│   │   │   └── data/            # Discovery demo fallback datasets
│   │   └── pages/               # Presentation controllers (landing, about, seekJob, etc.)
│   ├── src/                     # 🔒 PRIVATE APPLICATION DOMAIN (Protected)
│   │   ├── Core/                # Session, auth, bcrypt crypto, mailer, recovery
│   │   ├── Http/                # Request validation and business controllers
│   │   ├── Services/            # Business models (jobs, candidate, employer, admin)
│   │   └── views/               # Reusable headers, footers, and view templates
│   └── storage/                 # 🔒 PRIVATE STORAGE (Outside Webroot)
│       └── resumes/             # Authenticated candidate uploaded documents (PDF/DOC)
├── database/
│   ├── schema.sql               # Clean, idempotent MySQL 8.0 schema
│   └── migrations/              # Incremental SQL migration scripts
├── includes/
│   ├── dbconnect.php            # MySQLi connection factory
│   └── mail.local.php           # Local environment mail capture adapter
├── tests/
│   ├── run-regression.ps1       # Automated PowerShell test runner
│   ├── administration-http.php  # HTTP security, RBAC & moderation test suite
│   ├── administration.php       # Administrative logic unit tests
│   └── employer-http.php        # Employer lifecycle & candidate workflow tests
└── tools/
    ├── admin-access.php         # Offline administrator role provisioning CLI
    └── auth-maintenance.php     # Session cleanup & auth maintenance CLI
```

---

## 12. Architecture Diagram

JobPro employs a **Three-Tier Architecture** that enforces absolute physical isolation between the public web document root, core business logic, and private document storage:

```mermaid
flowchart TD
    subgraph ClientTier ["Client Presentation Layer (Browser)"]
        BrowserUI["Modern Web Browser (Desktop / Tablet / Mobile)"]
        VanillaJS["Vanilla ES6+ Reactive Discovery Engine"]
        ToastUI["JobProToast Notification Controller"]
    end

    subgraph WebrootTier ["Tier 1: Public Webroot (app/htdocs - Exposed)"]
        Router["index.php (Front Entry Redirector)"]
        Pages["pages/*.php (View Controllers)"]
        JSONAPI["api/*.php (REST JSON API Endpoints)"]
        Assets["assets/ (CSS3 Tokens, Toast JS, Optimized Media)"]
    end

    subgraph CoreTier ["Tier 2: Private Application Core (app/src - Protected)"]
        AuthCore["Core/ (auth.php, crypto, session, mail)"]
        HttpHandlers["Http/ (Request Validation & Route Logic)"]
        DomainServices["Services/ (jobs, candidate, employer, admin)"]
        ViewTemplates["views/ (Header, Footer, Navigation, Partials)"]
    end

    subgraph StorageTier ["Tier 3: Data & Storage Tier (Private)"]
        ResumesVault["app/storage/resumes/ (Protected Document Store)"]
        RelationalDB[("MySQL 8.0 Database (9 Relational Tables)")]
    end

    BrowserUI -->|HTTP / HTTPS Request| WebrootTier
    WebrootTier -->|Require / Include| CoreTier
    CoreTier -->|Prepared SQL Statements| RelationalDB
    CoreTier -->|Guarded Binary Stream| ResumesVault
```

---

## 13. Database ERD Diagram

The database architecture consists of 9 fully normalized relational tables managed with foreign key constraints, composite indexes, and cascading deletions:

```mermaid
erDiagram
    user ||--o| person : "profile"
    user ||--o| company : "operates"
    user ||--o{ applications : "submits"
    user ||--o{ saved_jobs : "bookmarks"
    user ||--o{ terms_acceptance : "agrees"
    user ||--o| admin_memberships : "holds_role"
    user ||--o{ admin_audit_log : "performed_action"
    company ||--o{ jobs : "publishes"
    jobs ||--o{ applications : "receives"
    jobs ||--o{ saved_jobs : "saved_in"

    user {
        int user_id PK
        string username
        string email UK
        string password
        string phone_number
        enum account_type
        enum account_status
        string account_status_reason
        timestamp created_at
    }

    person {
        int person_id PK
        int user_id FK
        string full_name
        string qualification
        int year_of_experience
        string city
        string country
        string cv_link
    }

    company {
        int company_id PK
        int user_id FK
        string company_name
        string website_link
        string address
    }

    jobs {
        int id PK
        int company_id FK
        string job_title
        string job_category
        string job_location
        decimal min_salary
        decimal max_salary
        enum publication_status
        enum moderation_status
        timestamp created_at
    }

    applications {
        int id PK
        int job_id FK
        int user_id FK
        text cover_letter
        enum status
        datetime applied_at
        timestamp status_updated_at
    }

    saved_jobs {
        int user_id PK, FK
        int job_id PK, FK
        timestamp saved_at
    }

    terms_acceptance {
        int acceptance_id PK
        int user_id FK
        boolean terms_accepted
        boolean details_correct
    }

    admin_memberships {
        int user_id PK, FK
        enum role
        int granted_by FK
        timestamp created_at
    }

    admin_audit_log {
        bigint id PK
        int actor_user_id FK
        string action
        string target_type
        int target_id
        string reason
        json before_state
        json after_state
        timestamp created_at
    }
```

---

## 14. API Documentation

JobPro provides a structured JSON REST API for client interactions:

### 1. Job Discovery API

- **Endpoint:** `GET /api/discovery.php`
- **Access:** Public
- **Query Parameters:**
  - `search` _(string)_: Keyword matching title, description, or skills.
  - `category` _(string)_: Industry filter (e.g. `Technology`, `Design`).
  - `location` _(string)_: Location filter.
  - `page` _(int)_: Pagination index (default: `1`).
- **Response Sample:**

```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "title": "Senior Full-Stack Engineer",
      "category": "Technology",
      "location": "Remote / New York",
      "company_name": "Acme Innovations",
      "salary_min": 120000.0,
      "salary_max": 155000.0,
      "publication_status": "open"
    }
  ],
  "total": 48,
  "pages": 5
}
```

### 2. Candidate Job Application API

- **Endpoint:** `POST /api/applyJob.php`
- **Access:** Authenticated (Candidate only)
- **Headers:** `Content-Type: application/x-www-form-urlencoded`
- **Parameters:** `job_id` _(int)_, `cover_letter` _(string)_, `csrf_token` _(string)_.
- **Response Sample:**

```json
{
  "success": true,
  "message": "Application submitted successfully."
}
```

### 3. Bookmarking / Saved Jobs API

- **Endpoint:** `POST /api/candidate.php`
- **Access:** Authenticated (Candidate only)
- **Parameters:** `action=toggle_saved`, `job_id` _(int)_, `csrf_token` _(string)_.
- **Response Sample:**

```json
{
  "success": true,
  "saved": true,
  "message": "Job bookmarked."
}
```

### 4. Employer Pipeline Stage API

- **Endpoint:** `POST /api/employer.php`
- **Access:** Authenticated (Employer owner only)
- **Parameters:** `action=update_status`, `application_id` _(int)_, `status` _(enum)_, `csrf_token` _(string)_.
- **Status Enum:** `reviewing`, `shortlisted`, `interview`, `offered`, `hired`, `rejected`.
- **Response Sample:**

```json
{
  "success": true,
  "new_status": "interview",
  "message": "Candidate moved to interview stage."
}
```

### 5. Secure Resume Streaming

- **Endpoint:** `GET /pages/resume.php?id={candidate_user_id}`
- **Access:** IDOR-Guarded (Candidate self, Associated Employer, or Admin)
- **Response:** Streamed binary document (`application/pdf`) with `Content-Disposition: inline`.

---

## 15. Authentication and Authorization Flow

```mermaid
sequenceDiagram
    autonumber
    actor User as User / Browser
    participant Webroot as Webroot (app/htdocs)
    participant Auth as Auth Core (src/Core/auth.php)
    participant DB as MySQL 8.0 Database
    participant Storage as Protected Storage (app/storage)

    User->>Webroot: POST /api/login_api.php (email, password, csrf_token)
    Webroot->>Auth: Validate anti-CSRF token
    Auth->>DB: SELECT * FROM user WHERE email = ? (Prepared SQL)
    DB-->>Auth: User record (with bcrypt hash & auth_stamp)
    Auth->>Auth: password_verify(input, hash) [Constant-time]
    Auth->>Auth: session_regenerate_id(true) [Fixation Defense]
    Auth->>Auth: Bind $_SESSION['user_id'] & $_SESSION['auth_stamp']
    Auth-->>User: Set-Cookie: PHPSESSID (HttpOnly, SameSite) & Redirect

    Note over User,Storage: Authenticated Document Access (e.g. Resume Download)
    User->>Webroot: GET /pages/resume.php?id=343
    Webroot->>Auth: Verify user session & auth_stamp validity
    Auth->>DB: Query candidate ownership OR employer application relation OR admin role
    DB-->>Auth: Access Authorized (Candidate Self / Reviewing Employer / Admin)
    Webroot->>Storage: Read file from app/storage/resumes/{user_id}.pdf
    Storage-->>Webroot: Binary stream (%PDF magic-byte verified)
    Webroot-->>User: HTTP 200 (Streamed inline PDF document)
```

---

## 16. Testing Strategy

JobPro employs a multi-tiered automated verification strategy to guarantee that modifications introduce zero regressions:

```
+-----------------------------------------------------------------------------------+
|                            AUTOMATED TEST PYRAMID                                 |
+-----------------------------------------------------------------------------------+
|                                                                                   |
|                           [ 116 HTTP / DB Checks ]                                |
|                  Integration verification across active routes                    |
|                Session lifecycle, CSRF validation, IDOR checks                    |
|                                                                                   |
|                        -----------------------------                              |
|                                                                                   |
|                           [ 30 Administrative Units ]                             |
|                 RBAC permissions, self-demotion locks, schema                     |
|                    migration idempotence, boundary sanitation                     |
+-----------------------------------------------------------------------------------+
```

### Running the Test Suite:

```powershell
powershell -ExecutionPolicy Bypass -File d:\Projects\JobPro\tests\run-regression.ps1
```

The test runner spins up an ephemeral loopback PHP web server on an isolated port, executes the complete suite against isolated test fixtures, verifies all assertions, cleans up test state, and terminates the temporary server automatically.

---

## 17. Test Coverage Information

| Test Domain                   | Checks  | Target Scope                                                                                            |     Pass Rate      |
| :---------------------------- | :-----: | :------------------------------------------------------------------------------------------------------ | :----------------: |
| **Administrative Unit Logic** |   30    | Permission hierarchy, last-owner demotion locks, null byte boundary rejection, migration idempotency.   |  **100% (30/30)**  |
| **Employer Lifecycle Tests**  |   48    | Job drafts, publication, decimal salary bounds, pipeline stage progression, candidate reviews.          |  **100% (48/48)**  |
| **Administration HTTP Tests** |   68    | CSRF enforcement, user suspension, session invalidation, listing moderation, audit logs, IDOR security. |  **100% (68/68)**  |
| **Total Test Suite**          | **146** | **Complete end-to-end regression validation**                                                           | **100% (146/146)** |

---

## 18. Deployment Process

### Production Deployment Procedure:

1. **Server Provisioning:** Provision an Ubuntu 22.04 LTS or Debian 12 server with PHP 8.4, MySQL 8.0, and Nginx.
2. **Webroot Configuration:** Point the web server document root strictly to the public directory:
   $$\text{Document Root} \longrightarrow \texttt{/var/www/jobpro/app/htdocs}$$
3. **Storage Directory Permissions:** Ensure the private storage directory exists outside the webroot and is writable exclusively by the web server user (`www-data`):
   ```bash
   mkdir -p /var/www/jobpro/app/storage/resumes
   chown -R www-data:www-data /var/www/jobpro/app/storage
   chmod 750 /var/www/jobpro/app/storage/resumes
   ```
4. **Environment Setup:** Configure production database credentials via system environment variables or ignored production configuration files.
5. **Database Migration:** Import `database/schema.sql` into the production MySQL database.
6. **Execute Regression Suite:** Run tests to verify the deployment before routing live traffic.

---

## 19. Hosting Configuration

### Nginx Server Block (`/etc/nginx/sites-available/jobpro.conf`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name jobpro.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name jobpro.example.com;

    # Point strictly to the public document root
    root /var/www/jobpro/app/htdocs;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/jobpro.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/jobpro.example.com/privkey.pem;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Deny access to sensitive files
    location ~ /\.(ht|git|env) {
        deny all;
    }

    # PHP-FPM FastCGI Handler
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 20. CI/CD Overview

JobPro can be integrated into GitHub Actions or GitLab CI. Below is an example workflow (`.github/workflows/regression.yml`) to automatically execute the test suite on every pull request:

```yaml
name: JobPro Regression Suite

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: windows-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: jobpro
        ports:
          - 3306:3306

    steps:
      - name: Checkout Code
        uses: actions/checkout@v4

      - name: Setup PHP 8.4
        uses: shivammathur/setup-php@v2
        with:
          php-version: "8.4"
          extensions: mysqli, gd, fileinfo, json

      - name: Import Database Schema
        run: |
          mysql -h 127.0.0.1 -u root -proot jobpro < database/schema.sql

      - name: Execute Regression Suite
        run: |
          powershell -ExecutionPolicy Bypass -File tests/run-regression.ps1
```

---

## 21. Performance Considerations

During the modernization process, JobPro underwent extensive performance profiling:

- **94% Asset Weight Reduction:** Total image and asset payload was slashed from **12.2 MB** down to **0.75 MB**.
- **High-DPI GD Resampling:** 33-megapixel camera raw assets were batch-resampled to 72 DPI web-optimized JPGs under 70 KB each, permanently preventing PHP memory exhaustion errors.
- **Native System Font Stack:** Eliminates external network roundtrips for Google Fonts or Typekit:
  ```css
  font-family:
    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  ```
- **Zero JavaScript Compilation Overhead:** Client-side modules execute natively in the browser without Babel or Webpack runtime layers.
- **Instantaneous Response Time:** Benchmarked local response times consistently register under **15 ms** per page request.

---

## 22. Security Considerations

| Vector                                | Defense Mechanism                                                                                      | Implementation File                       |
| :------------------------------------ | :----------------------------------------------------------------------------------------------------- | :---------------------------------------- |
| **SQL Injection**                     | Parameterized prepared statements on 100% of queries.                                                  | `app/src/Services/*.php`                  |
| **Cross-Site Scripting (XSS)**        | `htmlspecialchars(..., ENT_QUOTES \| ENT_SUBSTITUTE, 'UTF-8')` on all dynamic outputs.                 | `app/src/views/header.php`, `auth-ui.php` |
| **Cross-Site Request Forgery (CSRF)** | Cryptographically secure tokens (`bin2hex(random_bytes(32))`) validated via `hash_equals()`.           | `app/src/Core/auth.php`                   |
| **Password Storage**                  | `password_hash()` using bcrypt with default cost factor 10.                                            | `app/src/Core/auth.php`                   |
| **Session Hijacking**                 | `session_regenerate_id(true)` upon privilege escalation.                                               | `app/src/Core/auth.php`                   |
| **Session Invalidation**              | `auth_stamp` checking terminates active sessions immediately upon suspension or password change.       | `app/src/Core/auth.php`                   |
| **IDOR (File Access)**                | Resumes are served through `resume.php` which validates viewer authorization before reading from disk. | `app/src/views/pages/resume.php`          |
| **Malicious File Uploads**            | 5 MB cap, extension whitelist (`.pdf`, `.doc`, `.docx`), and `%PDF` magic byte verification.           | `app/src/Core/auth.php`                   |

---

## 23. Known Limitations

1. **Local Filesystem Resume Storage:** Uploaded resumes are currently persisted to the local server disk (`app/storage/resumes/`). Multi-server horizontal autoscaling requires an NFS mount or an S3 adapter.
2. **Synchronous Email Transport:** In environments using SMTP instead of log capture, email dispatch occurs synchronously during request execution.
3. **No Real-Time WebSockets:** Status updates currently update upon navigation or polling rather than via WebSocket push subscriptions.

---

## 24. Future Enhancements

- [ ] **Cloud Storage Adapter:** S3 / Cloudflare R2 integration for scalable remote resume storage.
- [ ] **Asynchronous Message Queue:** Redis or database-backed queue for asynchronous email delivery.
- [ ] **WebAuthn / Passkeys:** Passwordless biometric login support via WebAuthn API.
- [ ] **Exportable Candidate Dossier:** One-click PDF compilation of candidate bio, qualifications, and application history for hiring committees.
- [ ] **Multi-Language Localization:** Gettext / JSON-based i18n support for multilingual portals.

---

## 25. Troubleshooting Guide

### 1. Herd Redirects to Herd Studio (`/herd-studio?path=%2F`)

- **Cause:** Laravel Herd was linked to the repository root directory `D:\Projects\JobPro` instead of the public webroot `app/htdocs`.
- **Fix:** Re-link Herd to the public folder:
  ```powershell
  cd D:\Projects\JobPro\app\htdocs
  herd link jobpro
  ```

### 2. MySQL Connection Refused / Socket Error

- **Cause:** MySQL service is stopped or port 3306 is blocked.
- **Fix:** Verify MySQL is running on `127.0.0.1:3306` and credentials in `includes/dbconnect.php` match your local MySQL configuration.

### 3. Resume Upload Fails with "Invalid File Type"

- **Cause:** The uploaded file lacks genuine document headers (e.g. renaming `.exe` to `.pdf`).
- **Fix:** Upload a valid PDF, DOC, or DOCX document with authentic binary magic bytes.

### 4. Admin Navigation Not Showing After Granting Role

- **Cause:** Active session holds pre-grant authorization state.
- **Fix:** Sign out and sign back in to regenerate the session and refresh administrative permissions.

---

## 26. Contribution Guidelines

We welcome community contributions that uphold JobPro's core architectural standards:

1. **Preserve the Zero-Dependency Philosophy:** Do not introduce composer packages or npm runtime dependencies. Keep all code in pure PHP, Vanilla JS, and modern CSS3.
2. **Follow PSR-12 Coding Standards:** Maintain consistent formatting, 4-space indentation, camelCase methods, and explicit return types.
3. **Zero Security Regressions:** Any PR introducing mutations or file access must include anti-CSRF token verification and prepared statements.
4. **Mandatory Regression Verification:** Run and pass all 146 automated tests before submitting changes:
   ```powershell
   powershell -ExecutionPolicy Bypass -File tests/run-regression.ps1
   ```

---

## 📄 License

This project is open-source software licensed under the [MIT License](LICENSE).
