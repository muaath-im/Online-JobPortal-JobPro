<?php
// Preserve existing PHP pages and account navigation while modernizing the shell.
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isLandingPage = $currentPage === 'landing.php';
$isDiscoveryPage = in_array($currentPage, ['seekJob.php', 'job.php'], true);
$usesModernStyles = $isLandingPage || $isDiscoveryPage;
$isSignedIn = isset($_SESSION['user_id']);
$shellEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$shellBasePath = preg_replace('~/pages$~', '', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/pages/landing.php')));
$shellBasePath = $shellBasePath === '/' || $shellBasePath === '.' ? '' : $shellBasePath;
$shellUrl = static function ($path) use ($shellBasePath) {
    return $shellBasePath . '/' . ltrim($path, '/');
};
$shellAsset = static function ($path) use ($shellUrl) {
    return $shellUrl($path) . '?v=' . filemtime(__DIR__ . '/../' . $path);
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
    <?php if (!$usesModernStyles): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/styles.css')) ?>">
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/style.css')) ?>">
        <?php if ($pageStylesheet): ?>
            <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/' . $pageStylesheet . '.css')) ?>">
        <?php endif; ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/foundation.css')) ?>">
    <?php if (!$usesModernStyles): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/legacy-theme.css')) ?>">
    <?php endif; ?>
    <?php if ($isLandingPage): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/landing.css')) ?>">
    <?php endif; ?>
    <?php if ($isDiscoveryPage): ?>
        <link rel="stylesheet" href="<?= $shellEscape($shellAsset('assets/css/discovery.css')) ?>">
    <?php endif; ?>
</head>
<body class="jp-page <?= $isLandingPage ? 'jp-page--landing' : ($isDiscoveryPage ? 'jp-page--discovery' : 'jp-page--legacy') ?>">
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
                    <li><a href="<?= $shellEscape($shellUrl('pages/createJob.php')) ?>"<?= in_array($currentPage, ['createJob.php', 'jobs.php'], true) ? ' aria-current="page"' : '' ?>>For employers</a></li>
                    <li><a href="<?= $shellEscape($shellUrl('pages/about.php')) ?>"<?= $currentPage === 'about.php' ? ' aria-current="page"' : '' ?>>About us</a></li>
                </ul>
                <div class="jp-nav-actions">
                    <select class="jp-appearance" aria-label="Appearance" data-appearance hidden>
                        <option value="system">System</option>
                        <option value="light">Light</option>
                        <option value="dark">Dark</option>
                    </select>
                    <?php if ($isSignedIn): ?>
                        <a class="jp-account-link" href="<?= $shellEscape($shellUrl('pages/account.php')) ?>"<?= $currentPage === 'account.php' ? ' aria-current="page"' : '' ?> title="Your account"><span class="jp-account-link__icon" aria-hidden="true">↗</span><span><?= $shellEscape($_SESSION['username'] ?? 'My account') ?></span></a>
                        <a class="jp-button jp-button--small jp-button--outline" href="<?= $shellEscape($shellUrl('api/logout.php?logout=true')) ?>">Log out</a>
                    <?php else: ?>
                        <a class="jp-login-link" href="<?= $shellEscape($shellUrl('pages/login.php')) ?>"<?= $currentPage === 'login.php' ? ' aria-current="page"' : '' ?>>Log in</a>
                        <a class="jp-button jp-button--small" href="<?= $shellEscape($shellUrl('pages/register.php')) ?>">Create account <?= $shellIcon('arrow') ?></a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <main id="main-content" class="jp-main" tabindex="-1">
