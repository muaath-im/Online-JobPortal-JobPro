<?php
session_start();
$pageTitle = 'Find a job — JobPro';
$pageDescription = 'Explore job opportunities by role, location, category, and employment type.';
include __DIR__ . '/../includes/header.php';
?>
<div class="jp-discovery" data-discovery data-view="list" data-account-role="<?= $shellEscape($_SESSION['account_type'] ?? 'guest') ?>">
    <section class="jp-discovery-hero">
        <div class="jp-container">
            <div class="jp-discovery-topline"><p class="jp-eyebrow">YOUR NEXT CHAPTER</p><a class="jp-text-link" href="/pages/applications.php">Your applications <?= $shellIcon('arrow') ?></a></div>
            <h1>Good work starts with<br><span>the right opportunity.</span></h1>
            <p class="jp-discovery-intro">Explore roles, find your fit, and take your next step with confidence.</p>
            <form class="jp-search-bar" action="/pages/seekJob.php" method="get" data-search-form role="search" aria-label="Search jobs">
                <div class="jp-search-field"><label for="job-keyword">What are you looking for?</label><input id="job-keyword" name="q" type="search" placeholder="Job title, company, or skill" maxlength="150" autocomplete="off"></div>
                <div class="jp-search-field"><label for="job-location">Where?</label><input id="job-location" name="location" type="search" placeholder="City or location" maxlength="100" autocomplete="off"></div>
                <button class="jp-button" type="submit">Find opportunities <?= $shellIcon('arrow') ?></button>
            </form>
            <div class="jp-popular"><span>Explore:</span><a href="?q=software" data-search-suggestion="software">Software</a><a href="?q=design" data-search-suggestion="design">Design</a><a href="?q=data" data-search-suggestion="data">Data</a><span class="jp-catalog-label" data-source-label>Existing job listings</span></div>
        </div>
    </section>
    <div class="jp-container jp-discovery-body">
        <div class="jp-demo-note jp-alert jp-alert--warning" data-demo-note hidden><strong>Design preview</strong> — These are fictional jobs for reviewing the interface. Applications are disabled. <a href="/pages/seekJob.php">Browse real listings</a></div>
        <noscript><div class="jp-alert">JavaScript is required for search and filters in this frontend milestone. You can still <a href="/pages/applications.php">open the existing authenticated jobs and applications page</a>.</div></noscript>
        <div class="jp-discovery-grid">
            <aside class="jp-filter-sidebar" aria-label="Job filters">
                <details class="jp-filter-panel" open>
                    <summary>Refine your search <span aria-hidden="true">⌄</span></summary>
                    <div class="jp-filter-content">
                        <div class="jp-filter-heading"><span>Find your fit</span><button class="jp-reset" type="button" data-clear>Reset all</button></div>
                        <label class="jp-filter-label" for="job-category">Job category</label>
                        <select class="jp-input" id="job-category" data-filter="category"><option value="">All categories</option></select>
                        <fieldset><legend>Employment type</legend><div data-type-options></div></fieldset>
                        <label class="jp-filter-label" for="posted-within">Date posted</label>
                        <select class="jp-input" id="posted-within" data-filter="posted"><option value="any">Any time</option><option value="7">Last 7 days</option><option value="30">Last 30 days</option></select>
                        <p class="jp-filter-help">Filters reflect the information employers have provided.</p>
                    </div>
                </details>
                <div class="jp-filter-callout"><span class="jp-badge">YOUR NEXT MOVE</span><h2>Bring your story<br>to the opportunity.</h2><p>Create a personal account to introduce your experience and apply for a role.</p><a class="jp-text-link" href="/pages/register.php">Create your profile <?= $shellIcon('arrow') ?></a></div>
            </aside>
            <section class="jp-results" aria-labelledby="results-heading" aria-busy="true" data-results>
                <div class="jp-results-toolbar"><div><h2 id="results-heading" tabindex="-1">Explore opportunities</h2><p class="jp-result-count" role="status" aria-live="polite" data-count>Loading job listings…</p></div><div class="jp-sort"><label for="job-sort">Sort by</label><select class="jp-input" id="job-sort" data-sort><option value="newest">Newest first</option><option value="oldest">Oldest first</option><option value="title">Job title A–Z</option></select></div></div>
                <div class="jp-active-filters" aria-label="Active filters" data-active-filters></div>
                <div class="jp-loading" data-loading aria-hidden="true"><div class="jp-skeleton"></div><div class="jp-skeleton"></div><div class="jp-skeleton"></div></div>
                <div class="jp-state jp-alert--error" data-error hidden><span class="jp-state-symbol" aria-hidden="true">!</span><h3>We couldn’t load the opportunities.</h3><p>Check your connection and try again. Your search is kept here.</p><button class="jp-button jp-button--outline" type="button" data-retry>Try again</button></div>
                <div class="jp-state" data-empty hidden><span class="jp-state-symbol" aria-hidden="true">⌕</span><h3>No matches this time.</h3><p>Try a broader keyword or remove a filter to see more opportunities.</p><button class="jp-button jp-button--outline" type="button" data-clear>Clear search &amp; filters</button></div>
                <div class="jp-job-list" data-jobs></div>
                <nav class="jp-pagination" aria-label="Job results pages" data-pagination></nav>
                <div class="jp-results-footnote"><p>Make your next step count. Read the role details before applying.</p><a href="?demo=1">Preview the design with sample jobs</a></div>
            </section>
        </div>
    </div>
</div>
<script src="<?= $shellEscape($shellAsset('assets/js/discovery-core.js')) ?>" defer></script>
<script src="<?= $shellEscape($shellAsset('assets/js/discovery.js')) ?>" defer></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
