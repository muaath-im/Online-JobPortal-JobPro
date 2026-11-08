<?php
require_once jobproPath('src/Services/administration.php');
jobproStartSession();$user=jobproRequireUser();
header('Cache-Control: private, no-store');
$json=str_contains($_SERVER['HTTP_ACCEPT']??'','application/json');
$action=jobproInput('action');$target=jobproId($_POST['target_id']??null);
$section=['user_status'=>'users','job_visibility'=>'jobs','job_remove'=>'jobs','application_status'=>'applications','admin_access'=>'access'][$action]??'overview';
try{
    $member=jobproAdminMembership((int)$user['user_id']);
    if(!$member||!jobproAdminCan($member['role'],$section)){
        if($json)jobproJson(['success'=>false,'message'=>'Administrator access is required.'],403);
        http_response_code(403);exit('Administrator access is required.');
    }
}catch(Throwable$error){error_log('JobPro administrator authorization unavailable.');if($json)jobproJson(['success'=>false,'message'=>'Administration is temporarily unavailable.'],503);http_response_code(503);exit('Administration is temporarily unavailable.');}
$back='/pages/admin.php';
if($section==='users')$back='/pages/admin-user.php?id='.($target??0);
if($section==='jobs')$back='/pages/admin-job.php?id='.($target??0);
if($section==='applications')$back='/pages/admin-application.php?id='.($target??0);
if($section==='access')$back='/pages/admin-access.php';
if($json)$user=jobproApiUser($user['account_type'],true);else jobproPost($back);
try{
    $id=(int)$user['user_id'];
    if(!jobproRate('admin-write:'.$id,90,900))throw new DomainException('Too many changes. Wait before trying again.',429);
    if($section==='access'&&!jobproRate('admin-access:'.$id.':'.jobproClientKey(),20,3600))throw new DomainException('Too many access changes. Try again later.',429);
    $reason=jobproInput('reason');$status=jobproInput('status');$expected=jobproInput('expected_status');
    if($action==='admin_access'&&!$target){
        $name=jobproInput('username');$matches=jobproAdminRows('SELECT user_id FROM user WHERE username=? LIMIT 2','s',[$name]);
        if(count($matches)!==1)throw new InvalidArgumentException('Enter the exact username of one existing account.',422);
        $target=(int)$matches[0]['user_id'];
    }
    if(!$target)throw new InvalidArgumentException('Choose a valid record.',422);
    if($action==='user_status'){
        jobproAdminChangeUser($id,$target,$status,$expected,$reason);$message='Account status updated. Existing records are preserved.';
    }elseif($action==='job_visibility'){
        jobproAdminModerateJob($id,$target,$status,$expected,$reason);$message='Job visibility updated. The employer’s publication state is preserved.';
    }elseif($action==='job_remove'){
        if(jobproInput('confirm')!=='yes')throw new InvalidArgumentException('Confirm removal before continuing.',422);
        jobproAdminRemoveJob($id,$target,$expected,jobproInput('expected_publication'),$reason);$message='Listing removed from public discovery. The job is closed and hidden; applications are preserved.';
    }elseif($action==='application_status'){
        jobproAdminChangeApplication($id,$target,$status,$expected,$reason);$message='Application status corrected. The candidate can see the new status.';
    }elseif($action==='admin_access'){
        jobproAdminSetMember($id,$target,$status,$expected,$reason);$message='Administrator access updated. Permissions are checked again on the next request.';
    }else throw new InvalidArgumentException('Choose a valid administration action.',422);
    if($json)jobproJson(['success'=>true,'message'=>$message]);
    jobproFlash($message);jobproRedirect($back);
}catch(Throwable$error){
    $known=$error instanceof InvalidArgumentException||$error instanceof OutOfBoundsException||$error instanceof DomainException;
    $status=$known&&in_array((int)$error->getCode(),[403,404,409,422,429],true)?(int)$error->getCode():503;
    $message=$known?$error->getMessage():'The administration change could not be saved. Please try again.';
    if(!$known)error_log('JobPro administration action failed (code '.(int)$error->getCode().').');
    if($json)jobproJson(['success'=>false,'message'=>$message],$status);
    jobproFlash($message,'error');jobproRedirect($back);
}
