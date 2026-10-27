<?php
// No database writes or real credentials: regression checks for login decisions.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../includes/login.php';
$password = 'Local regression fixture only';
$user = ['user_id' => 12, 'username' => 'fixture', 'password' => password_hash($password, PASSWORD_BCRYPT),
    'account_type' => 'personal', 'person_id' => 7, 'company_id' => null];
$checks = [];
$personal = jobproLoginIdentity($user, $password);
$checks['Valid personal login resolves profile'] = ($personal['person_id'] ?? null) === 7;
$checks['Password hash is excluded from session'] = !isset($personal['password']);
$checks['Wrong password is rejected'] = jobproLoginIdentity($user, 'wrong') === null;
$checks['Password whitespace is significant'] = jobproLoginIdentity($user, $password . ' ') === null;
$checks['Invalid stored hash is rejected'] = jobproLoginIdentity(array_replace($user, ['password' => 'invalid']), $password) === null;
$company = jobproLoginIdentity(array_replace($user, ['account_type' => 'Company', 'company_id' => 8]), $password);
$checks['Company role is normalized and resolves profile'] = ($company['account_type'] ?? '') === 'company' && ($company['company_id'] ?? null) === 8;
$checks['Other role profile is excluded'] = !isset($company['person_id']);
$checks['Unsupported role is rejected'] = jobproLoginIdentity(array_replace($user, ['account_type' => 'admin']), $password) === null;
$checks['Missing account ID is rejected'] = jobproLoginIdentity(array_replace($user, ['user_id' => null]), $password) === null;
$checks['Legacy account without a profile can still log in'] = jobproLoginIdentity(array_replace($user, ['person_id' => null]), $password) !== null;
foreach ($checks as $label => $passed) {
    echo ($passed ? 'PASS: ' : 'FAIL: ') . $label . PHP_EOL;
}
exit(in_array(false, $checks, true) ? 1 : 0);
