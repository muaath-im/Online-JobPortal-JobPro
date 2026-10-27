<?php

// Build only the session values used by the existing account workflows.
function jobproLoginIdentity(array $user, string $password): ?array
{
    if (!password_verify($password, (string) ($user['password'] ?? ''))) {
        return null;
    }

    $role = strtolower((string) ($user['account_type'] ?? ''));
    if (!in_array($role, ['personal', 'company'], true) || empty($user['user_id'])) {
        return null;
    }

    $identity = [
        'user_id' => (int) $user['user_id'],
        'username' => (string) $user['username'],
        'account_type' => $role,
    ];
    $profileKey = $role === 'company' ? 'company_id' : 'person_id';
    if (!empty($user[$profileKey])) {
        $identity[$profileKey] = (int) $user[$profileKey];
    }

    return $identity;
}
