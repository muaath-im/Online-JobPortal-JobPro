<?php
require_once jobproPath('src/Core/auth.php');
jobproStartSession();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') jobproRedirect('/pages/login.php');
jobproPost('/pages/login.php');
$submittedUsername = jobproInput('username');
$submittedPassword = jobproSecret('password');
$next = jobproSafeNext($_POST['next'] ?? null);
$old = ['username' => mb_substr($submittedUsername,0,255), 'next' => $next];
if ($submittedUsername === '' || $submittedPassword === '') jobproFormFailure('login', ['form' => 'Enter your username and JobPro password.'], $old, '/pages/login.php');
try {
    if (!jobproRate('login-ip:' . jobproClientKey(), 40, 900) || !jobproRate('login:' . jobproClientKey() . ':' . mb_strtolower($submittedUsername), 8, 900)) jobproFormFailure('login', ['form' => 'Too many sign-in attempts. Wait 15 minutes before trying again.'], $old, '/pages/login.php');
    $stmt = jobproDatabase()->prepare('SELECT u.*,p.person_id,c.company_id FROM user u LEFT JOIN person p ON p.user_id=u.user_id LEFT JOIN company c ON c.user_id=u.user_id WHERE u.username=? LIMIT 2');
    $stmt->bind_param('s',$submittedUsername); $stmt->execute(); $result=$stmt->get_result();
    $user=$result->num_rows === 1 ? $result->fetch_assoc() : null; $stmt->close();
    if ($user && jobproSignIn($user,$submittedPassword)) {
        jobproState('rate', 'login:' . jobproClientKey() . ':' . mb_strtolower($submittedUsername), static function (&$state) { $state['count'] = 0; });
        jobproRedirect($next);
    }
    // Unknown accounts perform password work too, without identifying users.
    if (!$user) password_verify($submittedPassword, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
    jobproFormFailure('login', ['form' => 'The username or password is incorrect. Check your details and try again.'], $old, '/pages/login.php');
} catch (Throwable $error) {
    error_log('JobPro sign-in failed (code ' . $error->getCode() . ').');
    jobproFormFailure('login', ['form' => 'Sign-in is temporarily unavailable. Please try again later.'], $old, '/pages/login.php');
}
