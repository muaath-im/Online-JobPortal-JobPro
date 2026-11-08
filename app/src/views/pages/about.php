<?php
require_once jobproPath('src/Core/auth.php');
jobproStartSession();
$pageTitle = "About Us — JobPro Platform";
$pageDescription = "Discover JobPro's mission, values, leadership team, and recruitment ecosystem engineered for transparent, high-speed career growth.";
include jobproPath('src/views/header.php');
?>

<div class="jp-container jp-about-page">
    <nav class="jp-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= $shellEscape($shellUrl('index.php')) ?>">Home</a>
        <span class="jp-breadcrumb__separator" aria-hidden="true">/</span>
        <span aria-current="page">About Us</span>
    </nav>

    <header class="jp-about-hero">
        <span class="jp-about-hero__badge">WHO WE ARE &amp; WHAT WE STAND FOR</span>
        <h1>Connecting Human Ambition with World-Class Opportunity</h1>
        <p class="jp-about-hero__lead">
            JobPro is a modern career and recruitment platform engineered to replace legacy hiring friction with
            transparent, high-speed, and dignified connections between extraordinary talent and forward-thinking
            companies.
        </p>
    </header>

    <!-- Key Platform Highlights / Stats Grid -->
    <section class="jp-about-stats" aria-label="Platform Impact Metrics">
        <div class="jp-stat-card">
            <div class="jp-stat-card__number">10,000+</div>
            <div class="jp-stat-card__label">Active Job Seekers</div>
            <p class="jp-stat-card__desc">Candidates building careers across engineering, product, operations, and
                creative disciplines.</p>
        </div>
        <div class="jp-stat-card">
            <div class="jp-stat-card__number">1,200+</div>
            <div class="jp-stat-card__label">Verified Employers</div>
            <p class="jp-stat-card__desc">From high-velocity seed startups to global enterprise leaders posting verified
                opportunities.</p>
        </div>
        <div class="jp-stat-card">
            <div class="jp-stat-card__number">98%</div>
            <div class="jp-stat-card__label">Delivery Reliability</div>
            <p class="jp-stat-card__desc">Direct application pipeline delivery with zero lost resumes or unread
                submission black holes.</p>
        </div>
        <div class="jp-stat-card">
            <div class="jp-stat-card__number">&lt; 24h</div>
            <div class="jp-stat-card__label">Fast Review Cycles</div>
            <p class="jp-stat-card__desc">Average turnaround from submission to initial recruiter status update or
                review confirmation.</p>
        </div>
    </section>

    <!-- The JobPro Story Narrative -->
    <section class="jp-about-story" aria-labelledby="story-heading">
        <h2 id="story-heading">The JobPro Story</h2>
        <p>
            Three years ago, JobPro was founded on a simple observation: modern employment platforms were falling short
            of their promise. Job seekers were submitting applications into opaque algorithmic "black holes," enduring
            weeks of silence without feedback, while hiring managers were drowning in clunky, bloated enterprise
            software laden with friction and unnecessary complexity.
        </p>
        <p>
            We set out to build a clean alternative grounded in rigorous software craft. We rejected heavy framework
            runtimes and third-party tracking scripts, choosing instead to build directly upon native web standards—PHP
            8.4, Vanilla JavaScript, and semantic CSS3. The result is a platform that loads instantaneously, respects
            battery and memory resources, and treats every user interaction with crisp responsiveness.
        </p>
        <p>
            Today, JobPro serves as an end-to-end recruitment hub bridging candidates, hiring teams, and platform
            administrators. With features ranging from reactive job discovery and verified resume vaults to immutable
            administrative audit trails, JobPro proves that modern software can be extraordinarily performant, robustly
            secure, and accessible to everyone.
        </p>
    </section>

    <!-- Dual Mission & Vision Grid -->
    <section class="jp-about-duo" aria-label="Our Mission and Vision">
        <div class="jp-duo-card">
            <div class="jp-duo-card__header">
                <div class="jp-duo-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76" />
                    </svg>
                </div>
                <h2>Our Mission</h2>
            </div>
            <p>
                To democratize career advancement and talent acquisition by providing an intuitive, transparent, and
                high-performance recruitment portal. We empower job seekers to discover fulfilling work and equip hiring
                teams with the tools to build world-class, diverse organizations without administrative hurdles.
            </p>
        </div>
        <div class="jp-duo-card">
            <div class="jp-duo-card__header">
                <div class="jp-duo-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        <circle cx="12" cy="12" r="10" />
                    </svg>
                </div>
                <h2>Our Vision</h2>
            </div>
            <p>
                To establish the benchmark for modern, friction-free employment connectivity—where hiring is
                merit-based, career transitions are celebrated, and technology amplifies human potential rather than
                standing in its way.
            </p>
        </div>
    </section>

    <!-- Core Values Section -->
    <section class="jp-about-values" aria-labelledby="values-heading">
        <h2 id="values-heading" class="jp-about-section-heading">Our Core Values</h2>
        <p class="jp-section-subtitle">The guiding architectural and cultural tenets behind every feature and line of
            code we craft.</p>
        <div class="jp-values-grid">
            <div class="jp-value-card">
                <div class="jp-value-card__header">
                    <div class="jp-value-card__icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="16" x2="12" y2="12" />
                            <line x1="12" y1="8" x2="12.01" y2="8" />
                        </svg>
                    </div>
                    <h3>Transparency First</h3>
                </div>
                <p>We advocate for upfront compensation ranges, distinct application stages, and transparent role
                    requirements. No hidden paywalls, misleading titles, or ghosted interviews.</p>
            </div>
            <div class="jp-value-card">
                <div class="jp-value-card__header">
                    <div class="jp-value-card__icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                        </svg>
                    </div>
                    <h3>Engineering Craftsmanship</h3>
                </div>
                <p>We reject heavyweight, bloated software. By building directly on native web standards (PHP 8.4,
                    Vanilla JS, CSS3), JobPro ensures instantaneous response times on all devices.</p>
            </div>
            <div class="jp-value-card">
                <div class="jp-value-card__header">
                    <div class="jp-value-card__icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <h3>Dignity &amp; Accessibility</h3>
                </div>
                <p>Every applicant deserves respect and equal access. JobPro is built from the ground up to support
                    keyboard navigation, assistive screen readers, and adaptive color schemes.</p>
            </div>
            <div class="jp-value-card">
                <div class="jp-value-card__header">
                    <div class="jp-value-card__icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                    </div>
                    <h3>Integrity &amp; Trust</h3>
                </div>
                <p>With verified company profiles, proactive listing moderation, and tamper-evident administrative audit
                    logging, we maintain a safe, spam-free marketplace for all parties.</p>
            </div>
        </div>
    </section>

    <!-- Platform Ecosystem / Dual Workflow -->
    <section class="jp-ecosystem-section" aria-labelledby="ecosystem-heading">
        <h2 id="ecosystem-heading">The JobPro Ecosystem</h2>
        <p class="jp-section-subtitle">Tailored, purpose-built workspaces designed to serve both sides of the hiring
            equation seamlessly.</p>
        <div class="jp-ecosystem-grid">
            <div class="jp-ecosystem-card">
                <h3>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 21v-2a8 8 0 0 1 16 0v2" />
                    </svg>
                    For Job Seekers
                </h3>
                <ul class="jp-ecosystem-list">
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span><strong>Reactive Job Search:</strong> Instant category and keyword filtering with
                            real-time URL state synchronization.</span>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span><strong>Secure Resume Vault:</strong> Upload PDF, DOC, or DOCX files with magic-byte
                            validation and private storage isolation.</span>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span><strong>Application Tracking:</strong> Follow applications across all 7 hiring stages with
                            clear, transparent status updates.</span>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span><strong>Bookmarking Engine:</strong> Save opportunities and receive reactive toast
                            confirmation alerts without page reloads.</span>
                    </li>
                </ul>
            </div>
            <div class="jp-ecosystem-card">
                <h3>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <rect x="3" y="7" width="18" height="14" rx="3" />
                        <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
                    For Hiring Teams
                </h3>
                <ul class="jp-ecosystem-list">
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span><strong>Lifecycle Post Management:</strong> Create drafts, publish open roles, and archive
                            closed listings with ease.</span>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span><strong>Candidate Pipeline:</strong> Filter, review cover letters, and transition
                            applicant statuses in real time.</span>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span><strong>Guarded Resume Streaming:</strong> Download candidate resumes with strict IDOR
                            authorization controls.</span>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span><strong>Company Branding:</strong> Showcase your culture, mission, and current openings on
                            dedicated company profiles.</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Leadership & Advisory Team Section -->
    <section class="jp-team-section" aria-labelledby="leadership-heading">
        <h2 id="leadership-heading">Leadership &amp; Advisory Team</h2>
        <p class="jp-section-subtitle">Dedicated engineers, recruiters, and product leaders committed to shaping the
            future of work.</p>
        <div class="jp-team-grid--about">
            <div class="jp-about-team-card">
                <div class="jp-team-avatar" aria-hidden="true">JD</div>
                <h3>John Doe</h3>
                <span class="jp-team-role">Chief Executive Officer</span>
                <p>Over 15 years scaling recruitment tech and talent infrastructure across North America and Europe.</p>
                <div class="jp-team-badge">Co-Founder</div>
            </div>
            <div class="jp-about-team-card">
                <div class="jp-team-avatar" aria-hidden="true">JS</div>
                <h3>Jane Smith</h3>
                <span class="jp-team-role">Head of Product</span>
                <p>Former talent operations lead with a relentless focus on intuitive design, accessibility, and user
                    delight.</p>
                <div class="jp-team-badge">Product Design</div>
            </div>
            <div class="jp-about-team-card">
                <div class="jp-team-avatar" aria-hidden="true">RB</div>
                <h3>Robert Brown</h3>
                <span class="jp-team-role">Technical Director</span>
                <p>Systems architect specializing in high-throughput backend performance, database optimization, and web
                    security.</p>
                <div class="jp-team-badge">Infrastructure</div>
            </div>
            <div class="jp-about-team-card">
                <div class="jp-team-avatar" aria-hidden="true">ER</div>
                <h3>Elena Rostova</h3>
                <span class="jp-team-role">Head of Trust &amp; Safety</span>
                <p>Ensures marketplace integrity, oversees listing compliance, and manages verified employer onboarding.
                </p>
                <div class="jp-team-badge">Trust &amp; Safety</div>
            </div>
        </div>
    </section>

    <!-- Security & Trust Assurance Box -->
    <section class="jp-trust-box" aria-label="Security and Privacy Commitment">
        <div class="jp-trust-icon" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
        </div>
        <div class="jp-trust-content">
            <h3>Enterprise-Grade Privacy &amp; Data Security</h3>
            <p>Your professional information is always protected. JobPro isolates candidate resumes outside the public
                document root, enforces strict IDOR authorization, mandates CSRF verification on every transaction, and
                never sells user telemetry to third parties.</p>
            <div class="jp-trust-links">
                <a href="<?= $shellEscape($shellUrl('pages/privacy.php')) ?>">Review Privacy Policy &rarr;</a>
                <a href="<?= $shellEscape($shellUrl('pages/terms.php')) ?>">Terms &amp; Conditions &rarr;</a>
            </div>
        </div>
    </section>

    <!-- High-Impact Call to Action -->
    <section class="jp-about-cta-card" aria-labelledby="cta-heading">
        <h2 id="cta-heading">Ready to Accelerate Your Career Journey?</h2>
        <p>Whether you're searching for your next career breakthrough or seeking exceptional talent to grow your
            organization, JobPro gives you the tools to succeed.</p>
        <div class="jp-about-cta-actions">
            <a class="jp-button" href="<?= $shellEscape($shellUrl('pages/seekJob.php')) ?>">Explore Opportunities
                <?= $shellIcon('arrow') ?></a>
            <a class="jp-button jp-button--outline" href="<?= $shellEscape($shellUrl('pages/contact.php')) ?>">Speak
                With Our Team</a>
        </div>
    </section>
</div>

<?php include jobproPath('src/views/footer.php'); ?>
