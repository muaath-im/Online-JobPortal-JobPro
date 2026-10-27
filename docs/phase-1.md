# Phase 1 — Revised design foundation and landing page

## Current review

The first design was rejected and replaced on October 27, 2026. This document records Phase 1 and its startup/login repairs. The subsequently authorized Phase 2 is documented in [phase-2.md](phase-2.md). External deployment and Git actions remain deferred.

Review the current PHP application at **http://jobpro.test/**. Herd must link **D:\Projects\JobPro** directly to jobpro. The canonical landing page is **/pages/landing.php**. Use this one setup for the application.

## What changed

- Replaced the serif editorial layout and abstract hero illustration with a centered, product-focused layout using system sans-serif typography.
- Rebuilt the compact header, active navigation treatments, account actions, briefcase brand mark, footer, and responsive spacing.
- Kept the exact supplied blue/amber palette and its dark values.
- Added an accessible System/Light/Dark appearance selector. An explicit choice persists in local storage; blocked storage falls back gracefully. No Windows preference is changed.
- Added an onboarding switch for job seekers and employers. It changes explanatory content only, not authentication or the user's account type.
- Replaced the old sections with three career-entry cards, an onboarding guide, FAQ disclosures, and a compact closing action.
- Preserved the existing PHP account navigation, escaped output, skip link, keyboard focus states, mobile disclosure behavior, and reduced-motion support.
- New components remain isolated behind jp- class names. Existing pages retain their layouts and receive the shared shell and palette bridge.

Database configuration, credentials, and schema were preserved. The startup cleanup changes URL strings in forms, browser requests, and redirects to the domain root. Query and authentication logic are otherwise unchanged; old stored job-image prefixes are normalized when read.

## Design foundation

Canonical palette variables remain --primary, --primary-hover, --primary-tint, --accent, --bg, --surface, --border, --text, --text-muted, --success, --warning, and --error.

Light primary buttons use white text; dark primary buttons use dark navy text. Amber elements use dark text. The closing brand panel uses the supplied #2563EB with white text in either appearance.

Reusable classes: jp-button and its small/outline/light variants, jp-text-link, jp-card, jp-field, jp-input, jp-field__hint, jp-badge, jp-alert, and their status variants.

The mobile menu breakpoint is 1024px. It is a disclosure rather than a modal, so it does not trap focus. Without JavaScript, all navigation links and both onboarding explanations remain available; inactive interactive controls stay hidden.

## Verification

- php tests/phase1.php: **37 checks passed**, including guest/member navigation, escaped session values/title, landmarks, current-page state, missing stylesheet handling, local targets, and both root/subdirectory shell URLs. Synthetic sessions are used; these are not live authentication tests.
- PHP syntax checks passed for the modified templates and landing page.
- The shared JavaScript and inline appearance initializer passed syntax checks.
- Browser geometry was checked at seven widths spanning 320–1440px in both light and dark appearance, with no horizontal page overflow.
- Both appearances were visually reviewed. The chosen theme survived a reload.
- Candidate/employer explanations switched using keyboard activation; exactly one panel remained visible with JavaScript enabled.
- The mobile menu opened; Escape closed it and restored focus. The appearance selector remained accessible inside the expanded mobile navigation.
- FAQ disclosures opened/closed with Enter; the skip link focused the single main landmark. The page has one h1 and no broken brand images.
- No browser error logs were captured on the redesigned landing page.
- Startup cleanup: all 36 PHP files passed syntax checks. Live Herd requests to the homepage, the previously looping `/jobpro/pages/pages/pages/landing.php` address, the login page, and the login API returned HTTP 200 without PHP/database error output. The homepage and malformed address both resolved to `/pages/landing.php`. MySQL80 was running. The landing page's stylesheets and images loaded in the browser with no horizontal overflow.

These checks do not claim complete WCAG conformance, a security audit of the backend, or measured loading-speed improvements.

## Screenshots

- screenshots/phase1-redesign-desktop-light.png — revised desktop hero and onboarding guide.
- screenshots/phase1-redesign-desktop-dark.png — revised dark appearance.
- screenshots/phase1-redesign-desktop-full.png — full revised desktop page.
- screenshots/phase1-redesign-mobile-light.png — revised mobile light appearance.
- screenshots/phase1-redesign-mobile-dark.png — revised mobile dark appearance.
- screenshots/phase1-redesign-mobile-full.png — full revised mobile page.

The original baseline screenshot and current redesign screenshots are retained. Superseded rejected-design screenshots and the deployment-preview screenshot were removed.

## Outstanding work and boundaries

### Focused login repair

Login failures now return to the form with an escaped inline alert and preserve only the username. Empty or malformed fields are rejected; username whitespace is trimmed while password whitespace is preserved. Successful credential checks regenerate the session ID and replace stale role/profile values. Guest account requests redirect before rendering or querying account data. Google login and persistent-login controls were removed because they were unimplemented. The login form now uses the palette surface in dark appearance.

Verification: `php tests/login.php` passed 10 credential/session mapping checks with synthetic hashes; the 37 shared-shell checks and syntax checks for changed PHP files also passed. Live Herd requests verified guest redirects, missing credentials, and nonexistent usernames, including session-backed inline error rendering. A read-only database check confirmed connectivity and recognized stored hash formats. No real account password was entered or changed, and successful sign-in with a real account remains unverified. Registration and password recovery need separate work.

Follow-up root cause: the private database configuration defines `$username` and `$password` in the caller's scope. Loading it after collecting the form values overwrote the submitted credentials. The handler now uses `$submittedUsername` and `$submittedPassword`, leaving database credentials separate. The opt-in `php tests/login-http.php --run` integration test reproduced the failure before the fix and passed all 8 checks afterward: guest redirect, personal/company sign-in, session persistence, logout, and company access to the job form. It creates disposable local accounts and deletes their records afterward; existing accounts and passwords are unchanged. The user's own credentials have not been entered by the agent.

Existing registration defects, raw duplicate-email errors, missing backend ownership checks, unsafe uploads, and the reported signed-in company access problem remain tracked. The account logout form now targets the existing logout endpoint; authenticated logout and other account workflows still need verification. The new shell does not solve those backend workflows.

The application now consistently uses /pages/, /api/, and /assets/ under a project-root Herd mapping. The former relative index redirect could append pages/ repeatedly when Herd fell back to index.php for an invalid path; it now redirects to /pages/landing.php and exits.

Public job discovery pages, account interfaces, and dashboards are later frontend milestones. No real search, applicant tracking, or notification functionality is implied by this landing redesign.

Deployment remains deferred. The generated preview directory, exporter, deployment guide, preview-only stylesheet/test, and obsolete screenshot were removed. No application or database data was deleted.

## Review gate

Review the revised PHP page on desktop/mobile, test both onboarding choices and all three appearance settings, and approve or request changes. All branch creation, commits, merges, and pushes remain manual. A suggested commit after approval is: feat(ui): redesign landing experience and shared navigation.

Frontend Phase 2 was subsequently authorized explicitly; see [its implementation and review gate](phase-2.md). No later milestone starts without approval.
