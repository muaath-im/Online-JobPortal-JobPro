<?php
require_once jobproPath('src/Services/candidate.php');
jobproStartSession();
$user = jobproApiUser('personal', true);
$id = jobproId($_POST['application_id'] ?? null);
$letter = jobproInput('cover_letter');
if (!$id || jobproCandidateLetter($letter))
    jobproJson(['success' => false, 'message' => 'Choose a valid application and use up to 10,000 characters.'], 400);
try {
    jobproCandidateEdit((int) $user['user_id'], $id, $letter);
    jobproJson(['success' => true, 'message' => 'Application updated successfully.']);
} catch (Throwable $error) {
    $status = $error instanceof OutOfBoundsException ? 404 : 503;
    if ($status === 503)
        error_log('JobPro application update failed.');
    jobproJson(['success' => false, 'message' => $status === 404 ? 'Application not found.' : 'The application could not be updated.'], $status);
}
