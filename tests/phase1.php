<?php
// CLI-only checks for the shared shell. No database connection or real login required.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function renderShell(string $page, array $session = [], ?string $stylesheet = null): string
{
    $_SESSION = $session;
    $_SERVER['SCRIPT_NAME'] = '/jobpro/pages/' . $page;
    $pageTitle = 'JobPro <test>';
    if ($stylesheet !== null) {
        $pagename = $stylesheet;
    }
    ob_start();
    include __DIR__ . '/../includes/header.php';
    echo '<h1>Shell test</h1>';
    include __DIR__ . '/../includes/footer.php';
    return ob_get_clean();
}

function documentFor(string $html): DOMXPath
{
    $previous = libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    return new DOMXPath($document);
}

$results = [];
$check = static function (bool $condition, string $label) use (&$results) {
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $label);
    }
    $results[] = 'PASS: ' . $label;
};

$guest = renderShell('landing.php', [], 'landing');
$guestDocument = documentFor($guest);
$check($guestDocument->query('//main')->length === 1, 'One main landmark');
$check($guestDocument->query('//main[@id="main-content" and @tabindex="-1"]')->length === 1, 'Focusable skip-link destination');
$check($guestDocument->query('//nav[@aria-label="Primary"]')->length === 1, 'Named primary navigation');
$check($guestDocument->query('//a[@aria-current="page"]')->length === 1, 'One current-page link');
$check(str_contains($guest, 'JobPro &lt;test&gt;'), 'Page title is escaped');
$check(str_contains($guest, '>Log in</a>') && !str_contains($guest, '>Log out</a>'), 'Guest account navigation');
$check(!str_contains($guest, 'cdnjs.cloudflare.com'), 'Landing page has no third-party icon stylesheet');
$check(str_contains($guest, 'script.js?v='), 'Shared script has a file-versioned URL');

$member = renderShell('account.php', ['user_id' => 7, 'username' => '<img src=x onerror=alert(1)>'], 'account');
$memberDocument = documentFor($member);
$check(str_contains($member, '>Log out</a>') && !str_contains($member, '>Log in</a>'), 'Signed-in account navigation');
$check(str_contains($member, '&lt;img src=x onerror=alert(1)&gt;') && $memberDocument->query('//img[@src="x"]')->length === 0, 'Session username is rendered as text');
$check($memberDocument->query('//a[@href="/jobpro/pages/account.php" and @aria-current="page"]')->length === 1, 'Account navigation current state');

$missingPageName = renderShell('createJob.php');
$check(!str_contains($missingPageName, '/css/.css'), 'Pages without a stylesheet name render without warnings');
$check(!str_contains(renderShell('login.php', [], 'login'), '/css/login.css'), 'Missing legacy page stylesheets are not requested');
$check(!str_contains(renderShell('login.php', [], '../../config'), '/css/../../'), 'Stylesheet names are restricted to local page identifiers');

$root = dirname(__DIR__);
$localTargets = [];
foreach ([$guest, $member, $missingPageName] as $html) {
    $document = documentFor($html);
    foreach ($document->query('//*[@href or @src]') as $node) {
        $url = $node->getAttribute('href') ?: $node->getAttribute('src');
        $path = parse_url($url, PHP_URL_PATH);
        if ($path !== null && str_starts_with($path, '/jobpro/')) {
            $localTargets[$path] = true;
        }
    }
}
foreach (array_keys($localTargets) as $path) {
    $check(is_file($root . '/' . substr($path, strlen('/jobpro/'))), 'Local target exists: ' . $path);
}

echo implode(PHP_EOL, $results), PHP_EOL;
echo count($results), ' checks passed.', PHP_EOL;
