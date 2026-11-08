<?php
require_once jobproPath('src/Core/auth.php');
require_once jobproPath('src/Services/administration.php');
// Preserve existing PHP pages and account navigation while modernizing the shell.
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isLandingPage = $currentPage === 'landing.php';
$isDiscoveryPage = in_array($currentPage, ['seekJob.php', 'job.php'], true);
$isAuthPage = in_array($currentPage, ['login.php','register.php','comRegister.php','account.php','forgot-password.php','reset-password.php','logout.php'], true);
$isCandidatePage = in_array($currentPage, ['dashboard.php','applications.php','saved-jobs.php','profile.php','apply.php'], true);
$isEmployerPage = in_array($currentPage, ['employer.php','jobs.php','createJob.php','applicants.php','applicant.php','company-profile.php'], true);
$isCompanyPage = $currentPage === 'company.php';
$isAdminPage = in_array($currentPage,['admin.php','admin-users.php','admin-user.php','admin-companies.php','admin-jobs.php','admin-job.php','admin-applications.php','admin-application.php','admin-access.php','admin-audit.php'],true);
$isSignedIn = isset($_SESSION['user_id']);
$shellAdmin=null;
if($isSignedIn){try{$shellAdmin=jobproAdminMembership((int)$_SESSION['user_id']);}catch(Throwable$error){error_log('JobPro administration navigation unavailable.');}}
if ($isSignedIn && !headers_sent()) header('Cache-Control: private, no-store');
$shellEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$shellBasePath = preg_replace('~/pages$~', '', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/pages/landing.php')));
$shellBasePath = $shellBasePath === '/' || $shellBasePath === '.' ? '' : $shellBasePath;
$shellUrl = static function ($path) use ($shellBasePath) {
    return $shellBasePath . '/' . ltrim($path, '/');
};
$shellAsset = static function ($path) use ($shellUrl) {
    return $shellUrl($path) . '?v=' . filemtime(jobproWebPath($path));
};
$shellIcon = static function ($name) {
    $paths = [
        'arrow' => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="14" rx="3"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12a21 21 0 0 0 18 0M10 13h4"/>',
        'person' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'building' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1M10 21v-3h4v3"/>',
        'document' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Zm0 0v6h6M8 13h8m-8 4h6"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'layers' => '<path d="m12 3 10 6-10 6L2 9Zm-8 11 8 5 8-5M4 18l8 5 8-5"/>',
    ];
    return '<svg class="jp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['arrow']) . '</svg>';
};
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
    <script>
        // Apply the saved appearance before styles load; storage is optional.
        try {
            const savedAppearance = localStorage.getItem('jobpro.appearance');
            if (savedAppearance === 'light' || savedAppearance === 'dark') {
                document.documentElement.dataset.theme = savedAppearance;
            }
        } catch (error) { /* Follow the system preference when storage is unavailable. */ }
    </script>
    <link rel="icon" href="<?= $shellEscape($shellAsset('assets/images/jobpro-mark.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/foundation.css')) ?>">
    <?php if ($isLandingPage): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/landing.css')) ?>">
    <?php endif; ?>
    <?php if ($isDiscoveryPage): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/discovery.css')) ?>">
    <?php endif; ?>
    <?php if ($isAuthPage || $isCandidatePage || $isEmployerPage || $isCompanyPage || $isAdminPage): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/auth.css')) ?>">
    <?php endif; ?>
    <?php if ($isCandidatePage || $isDiscoveryPage): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/candidate.css')) ?>">
        <?php if (($_SESSION['account_type'] ?? '') === 'personal'): ?>
            <meta name="csrf-token" content="<?= jobproEscape(jobproCsrf()) ?>">
            <script src="<?= $shellEscape($shellAsset('assets/js/saved-jobs.js')) ?>" defer></script>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($isEmployerPage || $isCompanyPage): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/jobs.css')) ?>">
    <?php endif; ?>
    <?php if($isAdminPage): ?><link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/admin.css')) ?>"><?php endif; ?>
</head>
<body class="jp-page <?= $isLandingPage ? 'jp-page--landing' : ($isDiscoveryPage ? 'jp-page--discovery' : (($isAuthPage || $isCandidatePage || $isEmployerPage || $isCompanyPage || $isAdminPage) ? 'jp-page--auth' : 'jp-page--content')) ?>">
    <a class="jp-skip-link" href="#main-content">Skip to content</a>
    <header class="jp-header" data-site-header>
        <div class="jp-header__inner jp-container">
            <a class="jp-brand" href="<?= $shellEscape($shellUrl('index.php')) ?>" aria-label="JobPro home">
                <img src="<?= $shellEscape($shellAsset('assets/images/jobpro-mark.svg')) ?>" width="38" height="38" alt="">
                <span>JobPro</span>
            </a>
            <button class="jp-menu-toggle" type="button" aria-controls="primary-navigation" aria-expanded="false" hidden>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                <span>Menu</span>
            </button>
            <nav id="primary-navigation" class="jp-navigation" aria-label="Primary">
                <ul class="jp-nav-links">
                    <li><a href="<?= $shellEscape($shellUrl('index.php')) ?>"<?= $isLandingPage ? ' aria-current="page"' : '' ?>>Home</a></li>
                    <li><a href="<?= $shellEscape($shellUrl('pages/seekJob.php')) ?>"<?= $isDiscoveryPage || $currentPage === 'applications.php' ? ' aria-current="page"' : '' ?>>Find a job</a></li>
                    <li><a href="<?= $shellEscape($shellUrl('pages/employer.php')) ?>"<?= $isEmployerPage ? ' aria-current="page"' : '' ?>>For employers</a></li>
                    <li><a href="<?= $shellEscape($shellUrl('pages/about.php')) ?>"<?= $currentPage === 'about.php' ? ' aria-current="page"' : '' ?>>About us</a></li>
                </ul>
                <div class="jp-nav-actions">
                    <?php if($shellAdmin): ?><a class="jp-login-link" href="<?= $shellEscape($shellUrl('pages/admin.php')) ?>"<?= $isAdminPage?' aria-current="page"':'' ?>>Administration</a><?php endif; ?>
                    <?php if (($_SESSION['account_type'] ?? '') === 'personal'): ?><a class="jp-login-link" href="<?= $shellEscape($shellUrl('pages/dashboard.php')) ?>">My workspace</a><?php endif; ?>
                    <?php if (($_SESSION['account_type'] ?? '') === 'company'): ?><a class="jp-login-link" href="<?= $shellEscape($shellUrl('pages/employer.php')) ?>">My workspace</a><?php endif; ?>
                    <select class="jp-appearance" aria-label="Appearance" data-appearance hidden>
                        <option value="system">System</option>
                        <option value="light">Light</option>
                        <option value="dark">Dark</option>
                    </select>
                    <?php if ($isSignedIn): ?>
                        <a class="jp-account-link" href="<?= $shellEscape($shellUrl('pages/account.php')) ?>"<?= $currentPage === 'account.php' ? ' aria-current="page"' : '' ?> title="Your account"><span class="jp-account-link__icon" aria-hidden="true">↗</span><span><?= $shellEscape($_SESSION['username'] ?? 'My account') ?></span></a>
                        <form class="jp-signout-form" action="<?= $shellEscape($shellUrl('api/logout.php')) ?>" method="post"><?= jobproCsrfField() ?><button class="jp-button jp-button--small jp-button--outline" type="submit">Log out</button></form>
                    <?php else: ?>
                        <a class="jp-login-link" href="<?= $shellEscape($shellUrl('pages/login.php')) ?>"<?= $currentPage === 'login.php' ? ' aria-current="page"' : '' ?>>Log in</a>
                        <a class="jp-button jp-button--small" href="<?= $shellEscape($shellUrl('pages/register.php')) ?>">Create account <?= $shellIcon('arrow') ?></a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <main id="main-content" class="jp-main" tabindex="-1">
