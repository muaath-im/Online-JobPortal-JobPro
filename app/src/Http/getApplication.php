<?php
require_once jobproPath('src/Core/auth.php');
jobproStartSession();
$user = jobproApiUser('personal');
$id = jobproId($_GET['application_id'] ?? null);
if (!$id) jobproJson(['success' => false, 'message' => 'Choose a valid application.'], 400);
$stmt = jobproDatabase()->prepare('SELECT id,job_id,cover_letter,applied_at FROM applications WHERE id=? AND user_id=?');
$stmt->bind_param('ii', $id, $user['user_id']); $stmt->execute(); $application = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$application) jobproJson(['success' => false, 'message' => 'Application not found.'], 404);
jobproJson(['success' => true, 'application' => $application]);
