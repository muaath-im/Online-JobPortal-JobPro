# Phase 1 — Design foundation, shell, and landing page

## Scope

Implemented using the existing PHP/HTML/CSS/JavaScript stack. No database schema, authentication endpoint, or application API was changed. No Git commands were run.

- Centralized the supplied blue/amber palette in `assets/css/foundation.css`.
- Implemented the supplied `prefers-color-scheme: dark` palette, native control color scheme, and matching logo artwork.
- Added reusable button, link, field, input, card, badge, and alert styles.
- Rebuilt the shared header/footer, preserving guest and signed-in PHP navigation.
- Added current-page indicators, a skip link, visible focus states, a mobile disclosure menu, Escape-to-close with focus restoration, and reduced-motion support.
- Rebuilt the landing page with a hero, candidate/employer entry points, explanatory steps, native FAQ disclosures, and closing call to action.
- Replaced the old carousel, placeholder claims, unverified testimonials, dead social links, and large landing-page photos with an SVG illustration and working page links.
- Added a palette bridge for existing page content; their layouts are not redesigned in Phase 1.
- Avoided missing page-stylesheet requests and added modification-time versioning to stylesheet/script URLs.

## Design system

The canonical variables are `--primary`, `--primary-hover`, `--primary-tint`, `--accent`, `--bg`, `--surface`, `--border`, `--text`, `--text-muted`, `--success`, `--warning`, and `--error`. Their light/dark values match the supplied palette. Internal `--jp-*` aliases support the shared components.

`--on-primary` is white in light mode and dark navy in dark mode so button text remains readable. Amber surfaces use dark text. Employer panels use a subtle mixture of the accent and surface colors.

System UI fonts are used for body content; Georgia is used for editorial landing headings. No web-font download is needed.

| Component | Classes / behavior |
| --- | --- |
| Container | `jp-container` — maximum 1200px with responsive gutters |
| Button | `jp-button`; `--small`, `--outline`, and `--light` variants |
| Secondary action | `jp-text-link` |
| Form field | `jp-field`, `jp-input`, `jp-field__hint`; invalid state uses `aria-invalid` |
| Surface | `jp-card` |
| Badge | `jp-badge`, `jp-badge--accent` |
| Feedback | `jp-alert` with success/warning/error variants; add appropriate live-region semantics when a real workflow uses them |
| Navigation | Disclosure below or at 900px; desktop links above 900px |

The mobile links remain visible if JavaScript is unavailable. The disclosure button becomes available only after its handlers initialize. This is a navigation disclosure, not a modal; it does not trap focus.

## Verification

Performed on the local Herd application:

- `php tests/phase1.php`: **34 checks passed**. Checks cover guest/member rendering, title and username escaping, current-page state, landmarks, local link/asset targets, missing stylesheet handling, and asset versioning. Signed-in checks use a synthetic session array, not real authentication.
- PHP syntax checks passed for the changed templates, landing page, and test script.
- `node --check assets/js/script.js` passed.
- Browser geometry checks at **320, 390, 768, 900, 902, 1024, and 1440px** found no landing-page horizontal overflow.
- Mobile navigation opened and revealed all six guest links; Escape closed it and restored button focus.
- FAQ disclosures opened/closed with Enter; the skip link moved focus to the main landmark.
- About, Terms, Privacy, and Login pages loaded with the new shell; the login form retained its existing endpoint. Anonymous employer navigation continued to redirect to Login.
- The landing page has one main landmark, one h1, one current-page link, and no broken logo images.
- Eighteen light/dark token contrast pairs were calculated and passed 4.5:1, including primary buttons, links, muted text on base surfaces, and amber badges.
- Candidate-panel descriptions use primary text: muted text on the supplied dark primary tint calculates to approximately 4.49:1 and is unsuitable for normal-sized copy.
- Dark appearance was visually verified using the system's current preference. Light token contrast was checked; a separate light-preference visual check remains part of manual review.

These checks do not constitute a full WCAG conformance audit, a backend security audit, or a complete browser/device compatibility claim. No Lighthouse score or loading-speed improvement is claimed.

## Review artifacts

- `screenshots/phase1-before.png`: original landing capture.
- `screenshots/phase1-desktop-dark.png`: final desktop hero at 1440px.
- `screenshots/phase1-desktop-full.png`: final full desktop page.
- `screenshots/phase1-mobile-dark.png`: mobile hero at 390px.
- `screenshots/phase1-mobile-full.png`: full mobile page.

The original and final captures use different viewports; they are visual history, not a controlled performance comparison.

## Outstanding work

Existing registration JavaScript, raw duplicate-email errors, access-control vulnerabilities, account logout form issues, and the reported signed-in company-access problem remain tracked from the audit. Phase 1 does not resolve those workflows. The header logout link still uses the existing endpoint.

Public discovery pages, authentication forms, and dashboards retain their existing layouts until their approved frontend milestones. Landing copy describes existing code paths, not completed end-to-end verification of those paths.

Backend validation, CSRF protection, protected uploads, configuration extraction, database changes, and deployment security must be completed before public deployment. The existing `/jobpro/` base prefix remains intentional for the current local setup.

## Manual review and GitHub actions

1. Open the local landing page. Review desktop/mobile, the menu, FAQ, footer links, and both system appearance preferences.
2. In VS Code, click the current branch name. Create `codex/frontend-phase-1` if you do not already have a suitable feature branch. Existing uncommitted changes should remain available; do not discard them.
3. In Source Control, review and stage only this phase's files:
   - `README.md`
   - `includes/header.php` and `includes/footer.php`
   - `pages/landing.php`
   - `assets/css/foundation.css`, `assets/css/landing.css`, and `assets/css/legacy-theme.css`
   - `assets/js/script.js` and `assets/images/jobpro-mark.svg`
   - `tests/phase1.php`
   - `docs/phase-1.md` and the reviewed screenshots
4. Keep local database credential edits and unrelated changes out of this commit. Review existing history and populated SQL data before the first public push: pushing a branch also publishes its earlier commits.
5. After approving the diff, commit with `feat(ui): add responsive foundation and redesigned landing page`.
6. Verify the intended GitHub remote, then use **Publish Branch / Push**. Open a pull request into `main` for a final review, even as a solo developer. Never force-push to resolve a history mismatch without examining it.
7. After approving the pull request, merge it manually and update local `main` through VS Code.

The next implementation milestone is **Frontend Phase 2: public discovery pages**. It requires explicit approval; it has not started.
