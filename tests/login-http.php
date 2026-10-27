<?php
// Opt-in local integration test. Creates disposable accounts and removes them in finally.
if (PHP_SAPI !== 'cli' || !in_array('--run', $argv, true)) {
    if (PHP_SAPI !== 'cli') {
        http_response_code(404);
    }
    exit('Run locally with: php tests/login-http.php --run' . PHP_EOL);
}

require __DIR__ . '/../includes/dbconnect.php';
$fixtureIds = [];
$client = curl_init();
curl_setopt_array($client, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 4, CURLOPT_TIMEOUT => 10, CURLOPT_COOKIEFILE => '', CURLOPT_PROXY => '']);

function requestLoginTest($client, string $path, ?array $form = null): array
{
    curl_setopt($client, CURLOPT_URL, 'http://jobpro.test' . $path);
    if ($form === null) {
        curl_setopt($client, CURLOPT_HTTPGET, true);
    } else {
        curl_setopt($client, CURLOPT_POST, true);
        curl_setopt($client, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $body = curl_exec($client);
    if ($body === false) {
        throw new RuntimeException('Local HTTP request failed: ' . curl_error($client));
    }
    return ['status' => curl_getinfo($client, CURLINFO_HTTP_CODE),
        'url' => curl_getinfo($client, CURLINFO_EFFECTIVE_URL), 'body' => $body];
}

function assertLoginTest(bool $passed, string $label): void
{
    if (!$passed) {
        throw new RuntimeException('FAIL: ' . $label);
    }
    echo 'PASS: ' . $label . PHP_EOL;
}

$exitCode = 0;
try {
    $guest = requestLoginTest($client, '/pages/account.php');
    assertLoginTest(str_ends_with($guest['url'], '/pages/login.php'), 'Guest account request redirects to login');

    foreach (['personal', 'company'] as $role) {
        $name = 'login_fixture_' . bin2hex(random_bytes(8));
        $email = $name . '@example.invalid';
        $password = bin2hex(random_bytes(24));
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare('INSERT INTO user (username, email, password, account_type) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $hash, $role);
        $stmt->execute();
        $id = $mysqli->insert_id;
        $fixtureIds[] = $id;
        $stmt->close();

        $sql = $role === 'personal' ? 'INSERT INTO person (user_id, full_name) VALUES (?, ?)'
            : 'INSERT INTO company (user_id, company_name) VALUES (?, ?)';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('is', $id, $name);
        $stmt->execute();
        $stmt->close();

        $login = requestLoginTest($client, '/api/login_api.php', ['username' => $name, 'password' => $password]);
        if ($login['status'] !== 200 || !str_ends_with($login['url'], '/pages/account.php')
            || !str_contains($login['body'], 'value="' . $name . '"')) {
            echo json_encode(['status' => $login['status'], 'final_url' => $login['url'],
                'credentials_rejected' => str_contains($login['body'], 'The username or password is incorrect'),
                'database_error' => str_contains($login['body'], 'temporarily unavailable'),
                'body_length' => strlen($login['body']),
                'account_heading_present' => str_contains($login['body'], 'Account Information:')]) . PHP_EOL;
        }
        assertLoginTest($login['status'] === 200 && str_ends_with($login['url'], '/pages/account.php')
            && str_contains($login['body'], 'value="' . $name . '"'), $role . ' valid login reaches its account');
        $account = requestLoginTest($client, '/pages/account.php');
        assertLoginTest(str_contains($account['body'], 'value="' . $name . '"'), $role . ' session survives a separate request');
        $applications = requestLoginTest($client, '/pages/applications.php');
        assertLoginTest($applications['status'] === 200 && str_contains($applications['body'], 'id="editApplicationForm"')
            && !preg_match('/Warning:|Fatal error:/', $applications['body']), $role . ' can access the preserved application page');
        $discovery = requestLoginTest($client, '/pages/seekJob.php');
        assertLoginTest(str_contains($discovery['body'], 'data-account-role="' . $role . '"'), $role . ' discovery receives the authenticated role');
        if ($role === 'company') {
            $postJob = requestLoginTest($client, '/pages/createJob.php');
            assertLoginTest(str_contains($postJob['body'], 'name="job_title"'), 'Company session can open the job form');
        }
        requestLoginTest($client, '/api/logout.php?logout=true');
        $afterLogout = requestLoginTest($client, '/pages/account.php');
        assertLoginTest(str_ends_with($afterLogout['url'], '/pages/login.php'), $role . ' logout removes account access');
    }
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    $exitCode = 1;
} finally {
    curl_close($client);
    foreach ($fixtureIds as $id) {
        foreach (['person', 'company', 'user'] as $table) {
            $stmt = $mysqli->prepare('DELETE FROM ' . $table . ' WHERE user_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    echo 'Disposable test accounts removed; existing accounts were not modified.' . PHP_EOL;
}
exit($exitCode);
