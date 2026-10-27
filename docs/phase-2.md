# Phase 2 — Public discovery

Implemented October 27, 2026. Awaiting user review. No Git actions, deployment, or Phase 3 work.

## Review URLs

- Real opportunities: http://jobpro.test/pages/seekJob.php
- Design preview: http://jobpro.test/pages/seekJob.php?demo=1
- Example real detail: http://jobpro.test/pages/job.php?id=3
- Existing application workflow: http://jobpro.test/pages/applications.php (login required)

The live database currently contains four job listings. The separate JSON preview has 14 fictional listings, allowing all three pagination pages and more filter combinations to be reviewed without adding database records. Demo details never link to application submission. Demo dates are fixed sample content, not a claim that live listings are recent.

## What changed

- Replaced the login-only mixed discovery/application page with public search and reusable job cards. Cards show the real title, employer, date, location, category, type, and salary information.
- Added keyword and location search, category and multi-select employment filters, posted-date filters, date/title sorting, removable filter chips, reset, and six-result pagination.
- Search state is stored in query parameters. Browser Back/Forward restores it; detail links preserve the listing context. Pagination restores keyboard focus to the results heading.
- Added public role details, skills, notice period, facilities, employer website, salary overview, and role-aware application entry links. Copy-link feedback appears when the browser supports clipboard access.
- Kept the supplied palette, responsive shared shell, appearance selector, reduced-motion rules, semantic fields, visible focus, native filter disclosure, and live result-count announcements. Filters start collapsed on mobile.
- Added skeleton loading, empty results/catalog states, retryable errors, and unavailable-job feedback. Dynamic job content uses text nodes, not HTML insertion. Employer links are restricted to HTTP(S).
- Preserved the original application listing/edit/delete/apply page at `pages/applications.php`; added navigation to it, moved its login guard before output, escaped displayed values, removed nonexistent ratings, and hid its apply control for company sessions. Its mutation endpoints were not redesigned.

## Files and data flow

`pages/seekJob.php` and `pages/job.php` provide accessible page shells. `includes/header.php` loads only foundation/discovery styles for these pages, avoiding legacy CSS and the icon CDN.

`assets/js/discovery.js` fetches `/api/discovery.php` by default and renders the view. That GET-only endpoint projects public fields from the existing `getJobListings()` query in `api/getJobs.php`. An `id` query selects one role and returns 404 when absent. No account email, password, phone number, or user ID is returned. Database configuration and schema were preserved.

`assets/js/discovery-core.js` contains dependency-free URL, filtering, sorting, pagination, and salary functions. `assets/data/discovery-demo.json` is loaded only with `demo=1`; sample data is not inserted into MySQL.

The default feed reads the current small catalog; filtering and pagination run in the browser. Salary currency/payment period do not exist in the original schema and are explicitly marked as unspecified. No remote/hybrid policy, verified-employer badge, application deadline, or rating is invented.

## Verification

- `node tests/discovery.js`: 17 checks covering combined filters, date boundaries, sort order, page coverage/clamping, URL round trips, empty ranges, and salary precision.
- `php tests/discovery-http.php`: 15 checks with the current four-job database, including public page/feed access, public-field projection, valid IDs, unknown IDs, malformed/array IDs, method rejection, and guest application redirects. This suite does not write database data.
- `php tests/phase1.php`: 38 shared-shell checks. `php tests/login.php`: 10 credential/session checks.
- `php tests/login-http.php --run`: 12 successful-login/session/logout checks including personal/company access to the preserved application page, authenticated discovery roles, and the employer job form. Disposable accounts were removed automatically; no application was submitted.
- Changed PHP files passed syntax checks; the discovery JavaScript passed syntax checks.
- Listing and detail geometry checked at 320, 390, 600, 768, 1024, and 1440 pixels in light/dark themes: no horizontal page overflow. These are browser checks, not a full accessibility certification.
- Browser checks passed for live location filtering, keyboard pagination and focus, browser Back, sorting, clear/reset, empty results, keyboard filter disclosure, mobile navigation, unavailable roles, demo application protection, and safe rendering of HTML-like search input.
- Temporarily withheld the demo JSON to simulate a failed request, observed the error state, restored the file, and confirmed Retry loaded six sample cards. No temporary fixture remains.

Screenshots: `screenshots/phase2-discovery-desktop-light.png`, `screenshots/phase2-discovery-mobile-light.png`, `screenshots/phase2-details-desktop-dark.png`, `screenshots/phase2-details-mobile-light.png`.

## Boundaries and remaining work

This milestone adds only read access needed by the frontend. Existing application endpoints still require a separate ownership/authorization/CSRF review. The original application UI is intentionally retained for continuity; it is not a new application-tracking dashboard. No real application mutation was exercised in these checks.

Discovery currently requires JavaScript; a no-script notice links to the authenticated legacy page. For larger catalogs, move filtering/pagination to indexed SQL queries and add server-rendered listing/detail content, job-specific metadata, and structured data. A syntactically valid but unknown detail ID has a 200 page shell followed by a client unavailable state; its data endpoint returns 404. Production SEO/status handling is later backend work.

After a guest logs in, the current login handler opens the account page. The details CTA explains returning to the role; a validated return-to flow is a later enhancement. Registration, password recovery, saved jobs, notifications, and employer/admin dashboard redesigns are outside this phase.

## Review gate

Review live and demo discovery on desktop/mobile, check both appearances, open a role, and confirm the existing applications page after login. Request any Phase 2 revisions before proceeding. The user performs branch/commit/push actions manually after approval. No later phase has started.
