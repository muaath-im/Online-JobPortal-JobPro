<?php
require_once jobproPath('src/Core/auth.php');
require_once jobproPath('src/Services/administration.php');
jobproStartSession();
$currentUser = jobproRequireUser();

$targetId = jobproId($_GET['id'] ?? null) ?? (int)$currentUser['user_id'];

$stmt = jobproDatabase()->prepare('SELECT u.user_id, u.username, p.full_name, p.cv_link FROM user u LEFT JOIN person p ON p.user_id = u.user_id WHERE u.user_id = ?');
$stmt->bind_param('i', $targetId);
$stmt->execute();
$target = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$target || empty($target['cv_link'])) {
    http_response_code(404);
    exit('Resume not found.');
}

// External URLs (e.g. Google Drive, personal site)
if (preg_match('~^https?://~i', $target['cv_link'])) {
    header('Location: ' . $target['cv_link'], true, 303);
    exit;
}

// Local uploaded file authorization: candidate owner, employer reviewing applicant, or admin
$isOwner = (int)$currentUser['user_id'] === $targetId;
$isAdmin = false;
try {
    $membership = jobproAdminMembership((int)$currentUser['user_id']);
    $isAdmin = $membership !== null;
} catch (Throwable) {}

$isEmployerOfApplicant = false;
if (!$isOwner && !$isAdmin && $currentUser['account_type'] === 'company' && !empty($currentUser['company_id'])) {
    $stmt = jobproDatabase()->prepare('SELECT 1 FROM applications a JOIN jobs j ON j.id = a.job_id WHERE a.user_id = ? AND j.company_id = ? LIMIT 1');
    $stmt->bind_param('ii', $targetId, $currentUser['company_id']);
    $stmt->execute();
    $isEmployerOfApplicant = (bool)$stmt->get_result()->fetch_row();
    $stmt->close();
}

if (!$isOwner && !$isAdmin && !$isEmployerOfApplicant) {
    http_response_code(403);
    exit('Access denied. You do not have permission to view this resume.');
}

$filePath = jobproResumePath($target['cv_link']);
if (!$filePath || !is_file($filePath)) {
    http_response_code(404);
    exit('Resume document could not be located on the server.');
}

$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mimeMap = [
    'pdf' => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'doc' => 'application/msword'
];
$mime = $mimeMap[$ext] ?? 'application/octet-stream';
$cleanName = preg_replace('/[^A-Za-z0-9_.-]/', '_', ($target['full_name'] ?: $target['username']) . '_Resume.' . $ext);

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $cleanName . '"');
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($filePath);
exit;
