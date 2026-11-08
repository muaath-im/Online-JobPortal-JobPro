<?php
// Shared delivery adapter. Authentication and token storage remain in auth/recovery.php.
require_once jobproPath('src/Core/auth.php');

function jobproMailConfig(): array
{
    $defaults = require jobproPath('config/mail.example.php');
    $path = jobproPrivateConfigPath('mail.local.php', 'JOBPRO_MAIL_CONFIG');
    $local = $path !== null ? (static function (string $file) { return require $file; })($path) : [];
    $config = array_replace($defaults, is_array($local) ? $local : []);
    foreach (array_keys($defaults) as $key) {
        $value = getenv('JOBPRO_MAIL_' . strtoupper($key));
        if ($value !== false) $config[$key] = $value;
    }
    return $config;
}

function jobproMailLocalOrigin(string $origin): bool
{
    $url = parse_url($origin);
    return is_array($url) && in_array($url['scheme'] ?? '', ['http', 'https'], true)
        && in_array($url['host'] ?? '', ['jobpro.test', 'localhost', '127.0.0.1', '::1', '[::1]'], true)
        && !isset($url['user']) && !isset($url['pass']) && !isset($url['query']) && !isset($url['fragment'])
        && in_array($url['path'] ?? '', ['', '/'], true);
}

function jobproMailReady(): bool
{
    $config = jobproMailConfig();
    if ($config['transport'] === 'log') return jobproMailLocalOrigin((string) $config['base_url']);
    return $config['transport'] === 'brevo' && extension_loaded('curl') && extension_loaded('openssl')
        && is_string($config['api_key']) && trim($config['api_key']) !== ''
        && !preg_match('/[\r\n]/', $config['api_key'])
        && filter_var($config['from_address'], FILTER_VALIDATE_EMAIL) !== false;
}

function jobproBrevoPayload(array $config, string $email, string $subject, string $body, ?string $replyTo = null): array
{
    foreach ([$email, (string) $config['from_address'], $replyTo] as $address) {
        if ($address !== null && (!filter_var($address, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $address))) throw new InvalidArgumentException('Invalid email address.');
    }
    if ($subject === '' || strlen($subject) > 255 || preg_match('/[\r\n]/', $subject)) throw new InvalidArgumentException('Invalid email subject.');
    $payload = [
        'sender' => ['name' => (string) $config['from_name'], 'email' => $config['from_address']],
        'to' => [['email' => $email]], 'subject' => $subject, 'textContent' => $body,
    ];
    if ($replyTo !== null) $payload['replyTo'] = ['email' => $replyTo];
    return $payload;
}

function jobproBrevoHttp(string $method, string $path, string $apiKey, ?array $payload = null): array
{
    if (!extension_loaded('curl') || !extension_loaded('openssl')) throw new RuntimeException('HTTPS email support is unavailable.');
    if (!in_array([$method, $path], [['POST', '/smtp/email'], ['GET', '/account']], true) || $apiKey === '' || preg_match('/[\r\n]/', $apiKey)) throw new InvalidArgumentException('Invalid email API request.');
    $handle = curl_init('https://api.brevo.com/v3' . $path);
    if ($handle === false) throw new RuntimeException('Email connection could not be initialized.');
    try {
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ['accept: application/json', 'content-type: application/json', 'api-key: ' . $apiKey],
            CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        if ($payload !== null) curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
        $body = curl_exec($handle);
        if ($body === false) throw new RuntimeException('Email HTTPS request failed.');
        return ['status' => (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE), 'body' => $body];
    } finally {
        curl_close($handle);
    }
}

function jobproSendMail(string $email, string $subject, string $body, ?string $replyTo = null, ?callable $http = null): void
{
    if (!jobproMailReady()) throw new RuntimeException('Email delivery is not configured.');
    $config = jobproMailConfig();
    if ($config['transport'] === 'log') {
        // A local preview needs no verified sender; validate its addresses/body only.
        jobproBrevoPayload(array_replace($config, ['from_address' => 'local-preview@example.invalid']), $email, $subject, $body, $replyTo);
        // Explicit local capture only; never report this as external delivery.
        jobproState('mail', bin2hex(random_bytes(16)), static function (&$state) use ($email, $subject, $body, $replyTo) {
            $state = ['to' => $email, 'subject' => $subject, 'body' => $body, 'reply_to' => $replyTo, 'created' => time()];
        });
        return;
    }
    $payload = jobproBrevoPayload($config, $email, $subject, $body, $replyTo);
    $response = ($http ?? 'jobproBrevoHttp')('POST', '/smtp/email', $config['api_key'], $payload);
    $result = json_decode($response['body'], true);
    if ($response['status'] !== 201 || !is_array($result) || !is_string($result['messageId'] ?? null) || $result['messageId'] === '') {
        // Provider response bodies may contain private data. Never expose them.
        throw new RuntimeException('The email provider did not accept the message.');
    }
}
