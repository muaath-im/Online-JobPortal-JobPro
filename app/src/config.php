<?php
// The only application filesystem root. Supports app/{htdocs,src,config,storage}
// and a shared host with {src,config,storage} inside its document root.
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));

function jobproPath(string $path = ''): string
{
    return APP_ROOT . ($path === '' ? '' : '/' . ltrim($path, '/\\'));
}

function jobproWebRoot(): string
{
    return is_dir(jobproPath('htdocs')) ? jobproPath('htdocs') : APP_ROOT;
}

function jobproWebPath(string $path = ''): string
{
    return jobproWebRoot() . ($path === '' ? '' : '/' . ltrim($path, '/\\'));
}

function jobproCheckoutRoot(): ?string
{
    foreach ([dirname(APP_ROOT), dirname(APP_ROOT, 2)] as $candidate) {
        // Identify the checkout from application files, never optional build/test tools.
        if (realpath($candidate . '/app/src/config.php') === __FILE__
            && is_dir($candidate . '/includes')) return $candidate;
    }
    return null;
}

function jobproPrivateConfigPath(string $filename, string $environment): ?string
{
    $explicit = getenv($environment);
    if ($explicit !== false && $explicit !== '') {
        if (!is_file($explicit)) throw new RuntimeException('Private application configuration is unavailable.');
        return $explicit;
    }
    if (is_file(jobproPath('config/' . $filename))) return jobproPath('config/' . $filename);
    // Preserve the owner's existing ignored files without moving or rewriting them.
    $checkout = jobproCheckoutRoot();
    if ($checkout !== null && is_file($checkout . '/includes/' . $filename)) return $checkout . '/includes/' . $filename;
    return null;
}

function jobproDefaultAuthStorage(): string
{
    $checkout = jobproCheckoutRoot();
    if ($checkout !== null) {
        // Same namespace as includes/auth.php used before this directory move.
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jobpro-auth-' . substr(hash('sha256', $checkout), 0, 16);
    }
    return jobproPath('storage');
}

function jobproStorageIsProtected(string $resolved): bool
{
    $web = str_replace('\\', '/', realpath(jobproWebRoot()) ?: jobproWebRoot());
    if (!str_starts_with(strtolower($resolved . '/'), strtolower($web . '/'))) return true;
    // The only permitted inside-webroot store is the explicitly protected storage/.
    $expected = str_replace('\\', '/', realpath(jobproPath('storage')) ?: jobproPath('storage'));
    $rules = is_file(jobproPath('storage/.htaccess')) ? file_get_contents(jobproPath('storage/.htaccess')) : '';
    return strtolower($resolved) === strtolower($expected)
        && preg_match('/^\s*Require\s+all\s+denied\s*$/mi', $rules) === 1;
}

function jobproOpenDatabase(): mysqli
{
    $private = jobproPrivateConfigPath('dbconnect.php', 'JOBPRO_DB_CONFIG');
    if ($private !== null) {
        return (static function (string $file): mysqli {
            require $file;
            if (!isset($mysqli) || !$mysqli instanceof mysqli) throw new RuntimeException('Invalid database configuration.');
            return $mysqli;
        })($private);
    }
    // Hosting may use environment variables instead of an ignored PHP connector.
    $host = getenv('DB_HOST'); $name = getenv('DB_NAME'); $user = getenv('DB_USER'); $pass = getenv('DB_PASS');
    $port = filter_var(getenv('DB_PORT') ?: '3306', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
    if (!$host || !$name || !$user || $pass === false || $port === false) throw new RuntimeException('Database configuration is unavailable.');
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $connection = new mysqli($host, $user, $pass, $name, $port);
        $connection->set_charset('utf8mb4');
        return $connection;
    } catch (mysqli_sql_exception $error) {
        error_log('JobPro database connection failed (code ' . $error->getCode() . ').');
        throw new RuntimeException('Database connection is unavailable.');
    }
}
