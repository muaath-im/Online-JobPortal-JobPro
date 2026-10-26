<?php
// Preserve existing PHP pages and account navigation while modernizing the shell.
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isLandingPage = $currentPage === 'landing.php';
$isSignedIn = isset($_SESSION['user_id']);
$shellEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$shellAsset = static function ($path) {
    return '/jobpro/' . $path . '?v=' . filemtime(__DIR__ . '/../' . $path);
};
$pageStylesheet = isset($pagename) && preg_match('/^[a-zA-Z0-9_-]+$/', $pagename)
    && is_file(__DIR__ . '/../assets/css/' . $pagename . '.css') ? $pagename : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $shellEscape($pageDescription ?? 'Discover your next opportunity with JobPro. Connect your ambition with a new role, or find people to grow your team.') ?>">
    <meta name="theme-color" content="#F8FAFC" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0B1220" media="(prefers-color-scheme: dark)">
    <title><?= $shellEscape($pageTitle ?? 'JobPro') ?></title>
    <link rel="icon" href="/jobpro/assets/images/jobpro-mark.svg" type="image/svg+xml">
    <?php if (!$isLandingPage): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/styles.css')) ?>">
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/style.css')) ?>">
        <?php if ($pageStylesheet): ?>
            <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/' . $pageStylesheet . '.css')) ?>">
        <?php endif; ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/foundation.css')) ?>">
    <?php if (!$isLandingPage): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/legacy-theme.css')) ?>">
    <?php endif; ?>
    <?php if ($isLandingPage): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/landing.css')) ?>">
    <?php endif; ?>
</head>
<body class="jp-page <?= $isLandingPage ? 'jp-page--landing' : 'jp-page--legacy' ?>">
    <a class="jp-skip-link" href="#main-content">Skip to content</a>
    <header class="jp-header" data-site-header>
        <div class="jp-header__inner jp-container">
            <a class="jp-brand" href="/jobpro/index.php" aria-label="JobPro home">
                <img src="/jobpro/assets/images/jobpro-mark.svg" width="38" height="38" alt="">
                <span>JobPro<span class="jp-brand__dot">.</span></span>
            </a>
            <button class="jp-menu-toggle" type="button" aria-controls="primary-navigation" aria-expanded="false" hidden>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                <span>Menu</span>
            </button>
            <nav id="primary-navigation" class="jp-navigation" aria-label="Primary">
                <ul class="jp-nav-links">
                    <li><a href="/jobpro/index.php"<?= $isLandingPage ? ' aria-current="page"' : '' ?>>Home</a></li>
                    <li><a href="/jobpro/pages/seekJob.php"<?= $currentPage === 'seekJob.php' ? ' aria-current="page"' : '' ?>>Find a job</a></li>
                    <li><a href="/jobpro/pages/createJob.php"<?= in_array($currentPage, ['createJob.php', 'jobs.php'], true) ? ' aria-current="page"' : '' ?>>For employers</a></li>
                    <li><a href="/jobpro/pages/about.php"<?= $currentPage === 'about.php' ? ' aria-current="page"' : '' ?>>About us</a></li>
                </ul>
                <div class="jp-nav-actions">
                    <?php if ($isSignedIn): ?>
                        <a class="jp-account-link" href="/jobpro/pages/account.php"<?= $currentPage === 'account.php' ? ' aria-current="page"' : '' ?> title="Your account"><span class="jp-account-link__icon" aria-hidden="true">↗</span><span><?= $shellEscape($_SESSION['username'] ?? 'My account') ?></span></a>
                        <a class="jp-button jp-button--small jp-button--outline" href="/jobpro/api/logout.php?logout=true">Log out</a>
                    <?php else: ?>
                        <a class="jp-login-link" href="/jobpro/pages/login.php"<?= $currentPage === 'login.php' ? ' aria-current="page"' : '' ?>>Log in</a>
                        <a class="jp-button jp-button--small" href="/jobpro/pages/register.php">Get started <span aria-hidden="true">↗</span></a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <main id="main-content" class="jp-main" tabindex="-1">
