<?php
require_once jobproPath('src/Core/auth.php');
jobproStartSession();
$user = jobproApiUser('personal', true);
$data = json_decode(file_get_contents('php://input'), true);
$id = jobproId(is_array($data) ? ($data['application_id'] ?? null) : ($_POST['application_id'] ?? null));
if (!$id) jobproJson(['success' => false, 'message' => 'Choose a valid application.'], 400);
$stmt = jobproDatabase()->prepare('DELETE FROM applications WHERE id=? AND user_id=?');
$stmt->bind_param('ii', $id, $user['user_id']); $stmt->execute(); $deleted = $stmt->affected_rows; $stmt->close();
if (!$deleted) jobproJson(['success' => false, 'message' => 'Application not found.'], 404);
jobproJson(['success' => true, 'message' => 'Application deleted.']);
