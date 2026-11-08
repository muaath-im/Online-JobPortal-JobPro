<?php
require_once jobproPath('src/Services/candidate.php');
jobproStartSession();
header('Cache-Control: no-store');
$json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    $user=jobproApiUser('personal');
    try { jobproJson(['success'=>true,'saved_ids'=>jobproCandidateSavedIds((int)$user['user_id'])]); }
    catch (Throwable $error) { error_log('JobPro saved jobs read failed.'); jobproJson(['success'=>false,'message'=>'Saved jobs could not be loaded. Please try again.'],503); }
}
$action=jobproInput('action'); $jobId=jobproId($_POST['job_id']??null); $applicationId=jobproId($_POST['application_id']??null);
$back='/pages/applications.php';
if (in_array($action,['save','unsave'],true)) $back='/pages/saved-jobs.php';
if ($action==='apply' && $jobId) $back='/pages/apply.php?id='.$jobId;
$return=jobproInput('return_to');
if (in_array($action,['save','unsave','edit','withdraw'],true)) $back=jobproCandidateReturn($return,$back);
if ($json) $user=jobproApiUser('personal',true);
else {
    $user=jobproRequireUser(); jobproPost($back);
    if ($user['account_type']!=='personal') { http_response_code(403); exit('Personal accounts only.'); }
}
try {
    if (!jobproRate('candidate-write:'.(int)$user['user_id'],120,900)) throw new RuntimeException('Too many changes. Please wait and try again.',429);
    $letter=jobproInput('cover_letter'); $message='';
    if (in_array($action,['save','unsave','apply'],true) && !$jobId) throw new InvalidArgumentException('Choose a valid opportunity.',400);
    if (in_array($action,['edit','withdraw'],true) && !$applicationId) throw new InvalidArgumentException('Choose a valid application.',400);
    if ($action==='save' || $action==='unsave') {
        jobproCandidateSave((int)$user['user_id'],$jobId,$action==='save');
        $message=$action==='save'?'Opportunity saved to your account.':'Opportunity removed from saved jobs.';
    } elseif ($action==='apply') {
        if (jobproInput('reviewed')!=='yes') throw new InvalidArgumentException('Review the opportunity and confirm before submitting.',422);
        jobproCandidateApply((int)$user['user_id'],$jobId,$letter);
        $back='/pages/applications.php'; $message='Your application has been submitted.';
    } elseif ($action==='edit') {
        jobproCandidateEdit((int)$user['user_id'],$applicationId,$letter); $message='Your cover letter has been updated.';
    } elseif ($action==='withdraw') {
        if (jobproInput('confirm')!=='yes') throw new InvalidArgumentException('Confirm that you want to withdraw and delete this application.',422);
        $stmt=jobproDatabase()->prepare('DELETE FROM applications WHERE id=? AND user_id=?');
        $stmt->bind_param('ii',$applicationId,$user['user_id']); $stmt->execute(); $removed=$stmt->affected_rows; $stmt->close();
        if (!$removed) throw new OutOfBoundsException('Application not found.',404);
        $message='Your application has been withdrawn and deleted.';
    } else throw new InvalidArgumentException('Choose a valid action.',400);
    if ($json) jobproJson(['success'=>true,'message'=>$message,'saved'=>$action==='save']);
    jobproFlash($message); jobproRedirect($back);
} catch (Throwable $error) {
    $known=$error instanceof InvalidArgumentException || $error instanceof OutOfBoundsException || $error instanceof DomainException || $error->getCode()===429;
    $message=$known?$error->getMessage():'We could not save that change. Please try again.';
    $status=$known?(in_array((int)$error->getCode(),[400,404,409,422,429],true)?(int)$error->getCode():400):503;
    if (!$known) error_log('JobPro candidate write failed.');
    if ($json) jobproJson(['success'=>false,'message'=>$message],$status);
    if (in_array($action,['apply','edit'],true)) $_SESSION['candidate_letter']=['job_id'=>$jobId,'application_id'=>$applicationId,'letter'=>mb_substr(jobproInput('cover_letter'),0,10000)];
    jobproFlash($message,'error'); jobproRedirect($back);
}
