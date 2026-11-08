<?php
require_once dirname(__DIR__) . '/config.php';
require_once jobproPath('src/Core/login.php');

function jobproStartSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE)
        return;
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
    if (isset($_SESSION['user_id']) && (time() - ($_SESSION['last_seen'] ?? time()) > 1800 || time() - ($_SESSION['signed_in_at'] ?? time()) > 43200)) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['auth_flash'] = ['type' => 'error', 'message' => 'Your session expired. Please sign in again.'];
    }
    if (isset($_SESSION['user_id']))
        $_SESSION['last_seen'] = time();
}

function jobproDatabase(): mysqli
{
    static $connection;
    if (!$connection) {
        // Keep the private connector's variables out of every controller's scope.
        $connection = jobproOpenDatabase();
    }
    // Existing job helpers still use the global mysqli handle.
    $GLOBALS['mysqli'] = $connection;
    return $connection;
}

function jobproEscape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function jobproInput(string $key): string
{
    return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
}
function jobproSecret(string $key): string
{
    return is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
}
function jobproCsrf(): string
{
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
}
function jobproCsrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . jobproEscape(jobproCsrf()) . '">';
}
function jobproRedirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}
function jobproFlash(string $message, string $type = 'success'): void
{
    $_SESSION['auth_flash'] = ['message' => $message, 'type' => $type];
}
function jobproTakeFlash(): ?array
{
    $flash = $_SESSION['auth_flash'] ?? null;
    unset($_SESSION['auth_flash']);
    return $flash;
}
function jobproPost(string $back): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('This action requires a POST form. Return to the previous page and use its form.');
    }
    if (!is_string($_POST['csrf_token'] ?? null) || !hash_equals(jobproCsrf(), $_POST['csrf_token'])) {
        jobproFlash('Your form expired or could not be verified. Please try again.', 'error');
        jobproRedirect($back);
    }
}
function jobproFormFailure(string $form, array $errors, array $old, string $back): never
{
    // Passwords, tokens, and upload bodies never enter flash storage.
    $_SESSION['form_' . $form] = ['errors' => $errors, 'old' => $old];
    jobproRedirect($back);
}
function jobproFormState(string $form): array
{
    $state = $_SESSION['form_' . $form] ?? ['errors' => [], 'old' => []];
    unset($_SESSION['form_' . $form]);
    return $state;
}
function jobproPasswordError(string $password): ?string
{
    if (!mb_check_encoding($password, 'UTF-8') || str_contains($password, "\0") || mb_strlen($password, 'UTF-8') < 15 || mb_strlen($password, 'UTF-8') > 128 || strlen($password) > 512)
        return 'Use a passphrase of 15–128 characters (up to 512 bytes).';
    if (!defined('PASSWORD_ARGON2ID') && strlen($password) > 72)
        return 'This PHP build supports passwords up to 72 bytes. Choose a shorter passphrase.';
    if (preg_match('/^(.)\1+$/us', $password) || in_array(strtolower($password), ['passwordpassword', '123456789012345', 'qwertyuiopasdfgh'], true))
        return 'Choose a less predictable passphrase.';
    return null;
}
function jobproHash(string $password): string
{
    return password_hash($password, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT);
}
function jobproSafeNext($candidate): string
{
    if (!is_string($candidate))
        return '/pages/account.php';
    if (preg_match('~^/pages/job\.php\?id=([1-9][0-9]{0,9})$~D', $candidate))
        return $candidate;
    if (in_array($candidate, ['/pages/account.php', '/pages/applications.php', '/pages/jobs.php', '/pages/createJob.php'], true))
        return $candidate;
    return '/pages/account.php';
}
function jobproUrlError(string $value): ?string
{
    if ($value === '')
        return null;
    $parts = parse_url($value);
    return strlen($value) > 255 || !filter_var($value, FILTER_VALIDATE_URL) || !in_array(strtolower($parts['scheme'] ?? ''), ['https', 'http'], true) || isset($parts['user']) || isset($parts['pass']) ? 'Enter a complete http:// or https:// link.' : null;
}
function jobproAuthStorage(): string
{
    $directory = getenv('JOBPRO_AUTH_STORAGE') ?: jobproDefaultAuthStorage();
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory))
        throw new RuntimeException('Authentication storage is unavailable.');
    $resolved = str_replace('\\', '/', realpath($directory));
    if (!jobproStorageIsProtected($resolved))
        throw new RuntimeException('Authentication storage must be private or access-protected.');
    return $directory;
}
function jobproState(string $namespace, string $key, callable $callback)
{
    if (!in_array($namespace, ['rate', 'reset', 'mail'], true))
        throw new InvalidArgumentException('Unknown state namespace.');
    $path = jobproAuthStorage() . DIRECTORY_SEPARATOR . $namespace . '-' . hash('sha256', $key) . '.json';
    $file = fopen($path, 'c+');
    if (!$file || !flock($file, LOCK_EX))
        throw new RuntimeException('Could not lock authentication state.');
    try {
        $data = json_decode(stream_get_contents($file) ?: '{}', true) ?: [];
        $result = $callback($data);
        rewind($file);
        ftruncate($file, 0);
        $encoded = json_encode($data, JSON_THROW_ON_ERROR);
        if (fwrite($file, $encoded) !== strlen($encoded))
            throw new RuntimeException('Could not save authentication state.');
        fflush($file);
        return $result;
    } finally {
        flock($file, LOCK_UN);
        fclose($file);
    }
}
function jobproRate(string $key, int $limit, int $window): bool
{
    return jobproState('rate', $key, static function (&$state) use ($limit, $window) {
        if (($state['until'] ?? 0) <= time())
            $state = ['count' => 0, 'until' => time() + $window];
        if ($state['count'] >= $limit)
            return false;
        $state['count']++;
        return true;
    });
}
function jobproClientKey(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'cli';
}
function jobproUserById(int $id, bool $lock = false): ?array
{
    $stmt = jobproDatabase()->prepare('SELECT u.*, p.person_id, c.company_id FROM user u LEFT JOIN person p ON p.user_id=u.user_id LEFT JOIN company c ON c.user_id=u.user_id WHERE u.user_id=?' . ($lock ? ' FOR UPDATE' : ''));
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}
function jobproSignIn(array $user, string $password): bool
{
    $identity = jobproLoginIdentity($user, $password);
    if (!$identity || ($user['account_status'] ?? 'active') !== 'active')
        return false;
    session_regenerate_id(true);
    $_SESSION = $identity + ['auth_stamp' => hash('sha256', $user['password']), 'signed_in_at' => time(), 'last_seen' => time()];
    jobproCsrf();
    return true;
}
function jobproRequireUser(): array
{
    if (empty($_SESSION['user_id'])) {
        jobproFlash('Sign in to open your account.', 'error');
        jobproRedirect('/pages/login.php');
    }
    $user = jobproUserById((int) $_SESSION['user_id']);
    if (!$user || ($user['account_status'] ?? 'active') !== 'active' || !hash_equals(hash('sha256', $user['password']), $_SESSION['auth_stamp'] ?? '')) {
        $_SESSION = [];
        session_regenerate_id(true);
        jobproFlash('Please sign in again. Your session is no longer valid.', 'error');
        jobproRedirect('/pages/login.php');
    }
    $_SESSION['account_type'] = $user['account_type'];
    $_SESSION['username'] = $user['username'];
    foreach (['person_id', 'company_id'] as $key) {
        unset($_SESSION[$key]);
        if (!empty($user[$key]))
            $_SESSION[$key] = (int) $user[$key];
    }
    return $user;
}
function jobproSignOut(): void
{
    $_SESSION = [];
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $cookie['path'], 'secure' => $cookie['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    session_destroy();
}
function jobproJson(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function jobproId($value): ?int
{
    if (!is_string($value) && !is_int($value))
        return null;
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}
function jobproApiUser(string $role, bool $mutation = false): array
{
    if (empty($_SESSION['user_id']))
        jobproJson(['success' => false, 'message' => 'Sign in to continue.'], 401);
    $user = jobproUserById((int) $_SESSION['user_id']);
    if (!$user || ($user['account_status'] ?? 'active') !== 'active' || !hash_equals(hash('sha256', $user['password']), $_SESSION['auth_stamp'] ?? '')) {
        $_SESSION = [];
        session_regenerate_id(true);
        jobproJson(['success' => false, 'message' => 'Your session expired. Sign in again.'], 401);
    }
    if ($user['account_type'] !== $role)
        jobproJson(['success' => false, 'message' => 'This account type cannot perform that action.'], 403);
    if ($mutation) {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            jobproJson(['success' => false, 'message' => 'Use POST for this action.'], 405);
        }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if ($token === null && str_contains(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json) && isset($json['csrf_token']) && is_string($json['csrf_token'])) {
                $token = $json['csrf_token'];
            }
        }
        if (!is_string($token) || !hash_equals(jobproCsrf(), $token))
            jobproJson(['success' => false, 'message' => 'Your form expired. Refresh the page and try again.'], 403);
    }
    return $user;
}
function jobproAvatarPath(?string $stored): ?string
{
    if (!$stored || !preg_match('~^/uploads/avatars/[a-f0-9]{32}\.png$~D', $stored))
        return null;
    $path = jobproWebPath($stored);
    return is_file($path) ? $path : null;
}
function jobproUploadAvatar(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)
        return null;
    if (($file['error'] ?? -1) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '') || ($file['size'] ?? 0) > 2 * 1024 * 1024)
        throw new InvalidArgumentException('Choose a JPG, PNG, or WebP image no larger than 2 MB.');
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true) || $info[0] > 4096 || $info[1] > 4096)
        throw new InvalidArgumentException('Choose a JPG, PNG, or WebP image up to 4096 × 4096 pixels.');
    $source = @imagecreatefromstring(file_get_contents($file['tmp_name']));
    if (!$source)
        throw new InvalidArgumentException('This image could not be read.');
    $image = imagecreatetruecolor(192, 192);
    $background = imagecolorallocate($image, 239, 246, 255);
    imagefill($image, 0, 0, $background);
    $crop = min($info[0], $info[1]);
    imagecopyresampled($image, $source, 0, 0, (int) (($info[0] - $crop) / 2), (int) (($info[1] - $crop) / 2), 192, 192, $crop, $crop);
    $directory = jobproWebPath('uploads/avatars');
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory))
        throw new RuntimeException('Avatar storage is unavailable.');
    $name = '/uploads/avatars/' . bin2hex(random_bytes(16)) . '.png';
    if (!imagepng($image, jobproWebPath($name)))
        throw new RuntimeException('Could not save this image.');
    return $name;
}
function jobproResumePath(?string $stored): ?string
{
    if (!$stored || !preg_match('~^/storage/resumes/resume_[0-9]+_[a-f0-9]{32}\.(pdf|docx|doc)$~D', $stored))
        return null;
    $path = jobproPath(ltrim($stored, '/\\'));
    return is_file($path) ? $path : null;
}
function jobproUploadResume(array $file, int $userId): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)
        return null;
    if (($file['error'] ?? -1) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
        throw new InvalidArgumentException('Upload failed. Please choose a valid file.');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('Resume file must not exceed 5 MB.');
    }
    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowedExts = ['pdf', 'docx', 'doc'];
    if (!in_array($ext, $allowedExts, true)) {
        throw new InvalidArgumentException('Resume must be a PDF or Word document (.pdf, .docx, .doc).');
    }
    if (extension_loaded('fileinfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $validMimes = [
            'application/pdf',
            'application/x-pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
            'application/zip',
            'application/octet-stream'
        ];
        if (!in_array($mime, $validMimes, true)) {
            throw new InvalidArgumentException('Invalid file format. Please upload a genuine PDF or Word document.');
        }
    }
    if ($ext === 'pdf') {
        $handle = fopen($file['tmp_name'], 'rb');
        $header = fread($handle, 4);
        fclose($handle);
        if ($header !== '%PDF') {
            throw new InvalidArgumentException('Invalid PDF file content.');
        }
    }
    $directory = jobproPath('storage/resumes');
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Resume storage directory is unavailable.');
    }
    $name = '/storage/resumes/resume_' . $userId . '_' . bin2hex(random_bytes(16)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], jobproPath(ltrim($name, '/\\')))) {
        throw new RuntimeException('Could not save resume file.');
    }
    return $name;
}
