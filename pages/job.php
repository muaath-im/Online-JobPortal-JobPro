<?php
session_start();
$validJobId = isset($_GET['id']) && is_string($_GET['id']) && ctype_digit($_GET['id']) && (int) $_GET['id'] > 0;
if (!$validJobId) {
    http_response_code(404);
}
$pageTitle = 'Job details — JobPro';
$pageDescription = 'Read the role, skills, location, and employer information before your next career move.';
include __DIR__ . '/../includes/header.php';
?>
<div class="jp-discovery jp-detail-page jp-container" data-discovery data-view="detail" data-account-role="<?= $shellEscape($_SESSION['account_type'] ?? 'guest') ?>">
    <nav class="jp-detail-breadcrumb" aria-label="Breadcrumb"><a href="/pages/seekJob.php" data-back>All opportunities</a><span aria-hidden="true">/</span><span>Job details</span></nav>
    <div class="jp-demo-note jp-alert jp-alert--warning" data-demo-note hidden><strong>Design preview</strong> — This fictional role is for interface review. Applications are disabled. <a href="/pages/seekJob.php">Browse real listings</a></div>
    <noscript><div class="jp-alert">Enable JavaScript to view these role details, or <a href="/pages/applications.php">open the existing jobs and applications page</a>.</div></noscript>
    <div class="jp-detail-status" role="status" aria-live="polite" data-detail-status>Loading role details…</div>
    <div class="jp-loading" data-loading aria-hidden="true"><div class="jp-skeleton"></div><div class="jp-skeleton"></div></div>
    <div class="jp-state" data-error hidden><span class="jp-state-symbol" aria-hidden="true">!</span><h1>We couldn’t load this opportunity.</h1><p>Please try again in a moment.</p><button class="jp-button jp-button--outline" type="button" data-retry>Try again</button></div>
    <div class="jp-state" data-not-found hidden><span class="jp-state-symbol" aria-hidden="true">⌕</span><h1>This opportunity isn’t available.</h1><p>The link may be incorrect, or the employer may have removed the listing.</p><a class="jp-button" href="/pages/seekJob.php" data-back>Explore other opportunities</a></div>
    <article data-detail hidden aria-busy="true">
        <header class="jp-detail-header"><div class="jp-employer-mark" data-company-mark aria-hidden="true"></div><div><p class="jp-eyebrow" data-company></p><h1 data-title></h1><div class="jp-job-meta" data-meta></div></div></header>
        <div class="jp-detail-grid">
            <div class="jp-detail-content">
                <section class="jp-content-panel"><p class="jp-eyebrow">THE OPPORTUNITY</p><h2>About this role</h2><div class="jp-description" data-description></div></section>
                <section class="jp-content-panel"><h2>Skills &amp; practical details</h2><dl class="jp-job-facts" data-facts></dl></section>
                <section class="jp-content-panel"><h2>About the employer</h2><h3 data-employer-name></h3><p>This role was listed by the employer named above. Review the opportunity and confirm the details before sharing personal information.</p><a class="jp-text-link" data-website hidden target="_blank" rel="noopener noreferrer">Visit company website <span aria-hidden="true">↗</span></a></section>
            </div>
            <aside class="jp-detail-sidebar" aria-label="Application information">
                <div class="jp-apply-panel"><span class="jp-badge">YOUR NEXT STEP</span><h2>See yourself in this role?</h2><p data-apply-copy></p><a class="jp-button" data-apply></a><p class="jp-small" data-apply-hint></p><hr><dl class="jp-job-facts" data-overview></dl><button class="jp-share-button" type="button" data-share hidden>Copy job link <span aria-hidden="true">↗</span></button><p class="jp-small" role="status" aria-live="polite" data-share-status></p></div>
                <p class="jp-safety-note"><strong>Make a considered next move.</strong><br>Review the role, location, and salary information before you apply.</p>
            </aside>
        </div>
    </article>
</div>
<script src="<?= $shellEscape($shellAsset('assets/js/discovery-core.js')) ?>" defer></script>
<script src="<?= $shellEscape($shellAsset('assets/js/discovery.js')) ?>" defer></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
