<?php
require_once jobproPath('src/Core/auth.php');
jobproStartSession();
$user = jobproRequireUser();
$back = '/pages/account.php?section=security';
jobproPost($back);
if (!jobproRate('password:' . $user['user_id'], 10, 900)) jobproFormFailure('security', ['form' => 'Too many attempts. Try again in 15 minutes.'], [], $back);

$current = jobproSecret('current_password');
$new = jobproSecret('new_password');
$confirm = jobproSecret('confirm_password');
$errors = [];
if (!password_verify($current, $user['password'])) $errors['current_password'] = 'Your current password is incorrect.';
if ($message = jobproPasswordError($new)) $errors['new_password'] = $message;
if ($new !== $confirm) $errors['confirm_password'] = 'The new passwords do not match.';
if ($new === $current) $errors['new_password'] = 'Choose a password different from your current one.';
if ($errors) jobproFormFailure('security', $errors, [], $back);

$db = jobproDatabase();
try {
    $db->begin_transaction();
    $fresh = jobproUserById((int)$user['user_id'], true);
    if (!$fresh || !password_verify($current, $fresh['password'])) {
        $db->rollback();
        jobproFormFailure('security', ['current_password' => 'Your current password has changed. Please sign in again.'], [], $back);
    }
    $hash = jobproHash($new);
    $stmt = $db->prepare('UPDATE user SET password=? WHERE user_id=?');
    $stmt->bind_param('si', $hash, $user['user_id']);
    $stmt->execute();
    $stmt->close();
    $db->commit();

    session_regenerate_id(true);
    $_SESSION['auth_stamp'] = hash('sha256', $hash);
    unset($_SESSION['csrf_token']);
    jobproCsrf();
    jobproFlash('Password updated. Other account sessions will need to sign in again.');
    jobproRedirect($back);
} catch (Throwable $error) {
    $db->rollback();
    error_log('JobPro password change failed.');
    jobproFormFailure('security', ['form' => 'Your password could not be updated. Please try again.'], [], $back);
}
