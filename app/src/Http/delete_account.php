<?php
require_once jobproPath('src/Core/auth.php');
require_once jobproPath('src/Services/administration.php');
jobproStartSession();
$user = jobproRequireUser();
$back = '/pages/account.php?section=security';
jobproPost($back);
if (!jobproRate('delete:' . $user['user_id'], 10, 900)) jobproFormFailure('delete', ['form' => 'Too many confirmation attempts. Try again in 15 minutes.'], [], $back);
if (jobproInput('delete_confirmation') !== 'DELETE' || !password_verify(jobproSecret('delete_password'), $user['password'])) jobproFormFailure('delete', ['form' => 'Enter your current password and type DELETE to confirm.'], [], $back);

$db = jobproDatabase();
try {
    $db->begin_transaction();
    $fresh = jobproUserById((int)$user['user_id'], true);
    if (!$fresh || !password_verify(jobproSecret('delete_password'), $fresh['password'])) throw new InvalidArgumentException('Your password changed. Sign in again before deleting the account.');
    if(jobproAdministrationReady()&&jobproAdminRows('SELECT user_id FROM admin_memberships WHERE user_id=?','i',[(int)$fresh['user_id']]))throw new InvalidArgumentException('Another owner must revoke your administrator access before you can delete this account. At least one owner must remain.');
    if ($fresh['account_type'] === 'company') {
        $stmt = $db->prepare('SELECT j.id FROM jobs j JOIN company c ON j.company_id=c.company_id WHERE c.user_id=? LIMIT 1');
        $stmt->bind_param('i', $user['user_id']);
        $stmt->execute();
        $hasJobs = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if ($hasJobs) throw new InvalidArgumentException('Remove your posted jobs before deleting your company account. No jobs or applications have been deleted.');
    }
    foreach (['applications','terms_acceptance','person','company','user'] as $table) {
        $stmt = $db->prepare('DELETE FROM ' . $table . ' WHERE user_id=?');
        $stmt->bind_param('i', $user['user_id']);
        $stmt->execute();
        $stmt->close();
    }
    $db->commit();
    if ($path = jobproAvatarPath($fresh['profile_image'])) unlink($path);
    jobproSignOut();
    jobproRedirect('/pages/login.php?deleted=1');
} catch (Throwable $error) {
    $db->rollback();
    error_log('JobPro account deletion failed (code ' . $error->getCode() . ').');
    $message = $error instanceof InvalidArgumentException ? $error->getMessage() : 'The account could not be deleted. Associated records may still need attention.';
    jobproFormFailure('delete', ['form' => $message], [], $back);
}
