<?php
require_once jobproPath('src/Core/mail.php');
jobproStartSession();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: /pages/contact.php', true, 303);
    exit;
}
jobproPost('/pages/contact.php');
unset($_SESSION['success'], $_SESSION['error']);
$username = jobproInput('username');
$email = jobproInput('email');
$message = jobproInput('message');
if ($username === '' || $email === '' || $message === '') {
    $_SESSION['error'] = 'All fields are required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
    $_SESSION['error'] = 'Invalid email address.';
} elseif (mb_strlen($username) > 100 || preg_match('/[\r\n]/', $username) || mb_strlen($message) > 10000) {
    $_SESSION['error'] = 'Use a name up to 100 characters and a message up to 10,000 characters.';
} else {
    try {
        if (!jobproRate('contact-ip:' . jobproClientKey(), 5, 900)) {
            $_SESSION['error'] = 'Please wait before sending another message.';
            header('Location: /pages/contact.php', true, 303);
            exit;
        }
        $config = jobproMailConfig();
        $subject = mb_strcut('Contact Form Submission from ' . $username, 0, 255, 'UTF-8');
        jobproSendMail((string) $config['contact_to'], $subject,
            "Username: $username\nEmail: $email\n\nMessage:\n$message", $email);
        $_SESSION['success'] = $config['transport'] === 'log'
            ? 'Your message was saved to the private local preview. No email was sent.'
            : 'Your message has been accepted for delivery.';
    } catch (Throwable $error) {
        error_log('JobPro contact email delivery failed (code ' . $error->getCode() . ').');
        $_SESSION['error'] = 'Failed to send your message. Please try again later.';
    }
}
header('Location: /pages/contact.php', true, 303);
exit;
