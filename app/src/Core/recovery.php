<?php
require_once jobproPath('src/Core/mail.php');
function jobproSendRecovery(string $email, string $url): void
{
    $body = "A password reset was requested for your JobPro account.\n\nOpen this link within 30 minutes:\n" . $url . "\n\nIf you did not request this, you can ignore this message. Your password has not changed.\n";
    jobproSendMail($email, 'Reset your JobPro password', $body);
}
function jobproIssueReset(array $user): void
{
    $config = jobproMailConfig();
    $base = parse_url($config['base_url']);
    if (!is_array($base) || !isset($base['host']) || !in_array($base['scheme'] ?? '', ['http','https'], true) || isset($base['user']) || isset($base['pass']) || isset($base['query']) || isset($base['fragment']) || !in_array($base['path'] ?? '', ['', '/'], true)) throw new RuntimeException('Invalid recovery origin.');
    if ($base['scheme'] !== 'https' && !in_array($base['host'], ['jobpro.test','localhost','127.0.0.1'], true)) throw new RuntimeException('Recovery requires HTTPS outside local development.');
    $selector = bin2hex(random_bytes(16)); $secret = bin2hex(random_bytes(32));
    jobproState('reset', $selector, static function (&$state) use ($user, $secret) {
        $state = ['user_id' => (int)$user['user_id'], 'hash' => hash('sha256',$secret), 'stamp' => hash('sha256',$user['password']), 'expires' => time()+1800, 'used' => false];
    });
    jobproSendRecovery($user['email'], rtrim($config['base_url'],'/') . '/pages/reset-password.php?token=' . $selector . '.' . $secret);
}
function jobproResetParts(string $token): ?array
{
    return preg_match('/^([a-f0-9]{32})\.([a-f0-9]{64})$/D', $token, $match) ? [$match[1],$match[2]] : null;
}
function jobproResetValid(string $token): bool
{
    $parts = jobproResetParts($token);
    if (!$parts) return false;
    if (!is_file(jobproAuthStorage().DIRECTORY_SEPARATOR.'reset-'.hash('sha256',$parts[0]).'.json')) return false;
    return jobproState('reset', $parts[0], static function (&$state) use ($parts) {
        if (($state['used'] ?? true) || ($state['expires'] ?? 0) <= time() || !hash_equals($state['hash'] ?? '',hash('sha256',$parts[1]))) return false;
        $user = jobproUserById((int)$state['user_id']);
        return $user && hash_equals($state['stamp'], hash('sha256',$user['password']));
    });
}
function jobproConsumeReset(string $token, string $password): bool
{
    $parts = jobproResetParts($token);
    if (!$parts) return false;
    if (!is_file(jobproAuthStorage().DIRECTORY_SEPARATOR.'reset-'.hash('sha256',$parts[0]).'.json')) return false;
    return jobproState('reset', $parts[0], static function (&$state) use ($parts,$password) {
        if (($state['used'] ?? true) || ($state['expires'] ?? 0) <= time() || !hash_equals($state['hash'] ?? '',hash('sha256',$parts[1]))) return false;
        $db = jobproDatabase(); $db->begin_transaction();
        try {
            $user = jobproUserById((int)$state['user_id'], true);
            if (!$user || !hash_equals($state['stamp'], hash('sha256',$user['password']))) { $db->rollback(); return false; }
            $hash = jobproHash($password); $id = (int)$user['user_id'];
            $stmt = $db->prepare('UPDATE user SET password=? WHERE user_id=?'); $stmt->bind_param('si',$hash,$id); $stmt->execute(); $stmt->close();
            $db->commit(); $state['used'] = true; unset($state['hash']); return true;
        } catch (Throwable $error) { $db->rollback(); throw $error; }
    });
}
