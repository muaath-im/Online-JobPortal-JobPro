<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/login.php', true, 302);
    exit;
}

// dbconnect.php defines $username and $password for MySQL. Keep submitted
// credentials separate so including that configuration cannot overwrite them.
$submittedUsername = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
$submittedPassword = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$_SESSION['login_username'] = $submittedUsername;

if ($submittedUsername === '' || $submittedPassword === '') {
    $_SESSION['login_error'] = 'Enter your username and JobPro account password.';
    header('Location: /pages/login.php', true, 303);
    exit;
}

require_once __DIR__ . '/../includes/login.php';

try {
    require_once __DIR__ . '/../includes/dbconnect.php';
    $stmt = $mysqli->prepare('SELECT u.user_id, u.username, u.password, u.account_type,
        p.person_id, c.company_id FROM user u
        LEFT JOIN person p ON p.user_id = u.user_id
        LEFT JOIN company c ON c.user_id = u.user_id
        WHERE u.username = ? LIMIT 1');
    $stmt->bind_param('s', $submittedUsername);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $identity = $user ? jobproLoginIdentity($user, $submittedPassword) : null;

    if ($identity !== null) {
        session_regenerate_id(true);
        $_SESSION = $identity;
        session_write_close();
        header('Location: /pages/account.php', true, 303);
        exit;
    }

    $_SESSION['login_error'] = 'The username or password is incorrect. Use your registered username, not your email or MySQL password.';
} catch (mysqli_sql_exception $exception) {
    error_log('JobPro login database operation failed (code ' . $exception->getCode() . ').');
    $_SESSION['login_error'] = 'Login is temporarily unavailable. Please try again later.';
}

header('Location: /pages/login.php', true, 303);
exit;
