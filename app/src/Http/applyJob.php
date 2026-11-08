<?php
require_once jobproPath('src/Services/candidate.php');
jobproStartSession();
$user = jobproApiUser('personal', true);
$data = json_decode(file_get_contents('php://input'), true);
$id = jobproId(is_array($data) ? ($data['job_id'] ?? null) : ($_POST['job_id'] ?? null));
$letter = is_array($data) ? ($data['cover_letter'] ?? '') : jobproInput('cover_letter');
if (!$id || !is_string($letter) || jobproCandidateLetter($letter))
    jobproJson(['success' => false, 'message' => 'Choose a valid job and use a cover letter up to 10,000 characters.'], 400);
try {
    jobproCandidateApply((int) $user['user_id'], $id, trim($letter));
    jobproJson(['success' => true, 'message' => 'Application submitted successfully.']);
} catch (Throwable $error) {
    $status = in_array((int) $error->getCode(), [404, 409], true) ? (int) $error->getCode() : 503;
    if ($status === 503)
        error_log('JobPro application submission failed.');
    jobproJson(['success' => false, 'message' => $status === 503 ? 'The application could not be submitted.' : $error->getMessage()], $status);
}
