<?php
require_once jobproPath('src/Services/employer.php');
jobproStartSession();
header('Cache-Control: private, no-store');
$json=str_contains($_SERVER['HTTP_ACCEPT']??'','application/json');
if($json)$user=jobproApiUser('company',true);
else {
    $user=jobproRequireUser();jobproPost('/pages/employer.php');
    if($user['account_type']!=='company'){http_response_code(403);exit('Company accounts only.');}
}
$action=jobproInput('action');$companyId=(int)($user['company_id']??0);
$jobId=jobproId($_POST['job_id']??null);$applicationId=jobproId($_POST['application_id']??null);
$back='/pages/jobs.php';
if($action==='status')$back='/pages/applicant.php?id='.($applicationId??0);
if($action==='company')$back='/pages/company-profile.php';
try {
    if(!$companyId)throw new InvalidArgumentException('Complete your company profile in account settings first.',422);
    if(!jobproJobLifecycleReady()||!jobproApplicationTrackingReady())throw new RuntimeException('Database upgrade required.');
    if(!jobproRate('employer-write:'.(int)$user['user_id'],120,900))throw new DomainException('Too many changes. Wait before trying again.',429);
    if($action==='save') {
        if(array_key_exists('job_id',$_POST)&&!$jobId)throw new InvalidArgumentException('Choose a valid job.',422);
        $back='/pages/createJob.php'.($jobId?'?id='.$jobId:'');
        $validation=jobproEmployerJobValues($_POST);
        if($validation['errors']) {
            if($json)jobproJson(['success'=>false,'message'=>'Check the job fields.','errors'=>$validation['errors']],422);
            jobproFormFailure('employer_job_'.($jobId??0),$validation['errors'],$validation['data'],$back);
        }
        jobproEmployerSaveJob($companyId,$jobId,$validation['data']);
        $back='/pages/jobs.php';$message=$validation['data']['publication_status']==='draft'?'Draft saved. Only your company can see it.':'Job saved successfully.';
    } elseif(in_array($action,['open','closed','delete'],true)) {
        if(!$jobId)throw new InvalidArgumentException('Choose a valid job.',422);
        if($action==='delete'&&jobproInput('confirm')!=='yes')throw new InvalidArgumentException('Confirm permanent deletion before continuing.',422);
        jobproEmployerJobAction($companyId,$jobId,$action);
        $message=['open'=>'The job is open for applications.','closed'=>'The job is closed. Existing applications are preserved.','delete'=>'Job deleted.'][$action];
    } elseif($action==='status') {
        if(!$applicationId)throw new InvalidArgumentException('Choose a valid application.',422);
        jobproEmployerSetStatus($companyId,$applicationId,jobproInput('status'),jobproInput('expected_status'));
        $message='Application status updated. The candidate can see it in their applications.';
    } elseif($action==='company') {
        $name=jobproInput('company_name');$website=jobproInput('website_link');$address=jobproInput('address');
        $errors=[];
        if($name===''||mb_strlen($name)>255||!mb_check_encoding($name,'UTF-8')||str_contains($name,"\0"))$errors['company_name']='Enter a company name of up to 255 characters.';
        if(mb_strlen($website)>255||jobproUrlError($website)!==null)$errors['website_link']='Enter a complete http:// or https:// website address, or leave it blank.';
        if(mb_strlen($address)>255||!mb_check_encoding($address,'UTF-8')||str_contains($address,"\0"))$errors['address']='Use an address of up to 255 characters.';
        if($errors){
            if($json)jobproJson(['success'=>false,'message'=>'Check the company profile fields.','errors'=>$errors],422);
            jobproFormFailure('employer_company',$errors,['company_name'=>$name,'website_link'=>$website,'address'=>$address],$back);
        }
        $stmt=jobproDatabase()->prepare('UPDATE company SET company_name=?,website_link=?,address=? WHERE company_id=? AND user_id=?');
        $stmt->bind_param('sssii',$name,$website,$address,$companyId,$user['user_id']);$stmt->execute();$stmt->close();
        $message='Company profile saved. Your public company page and job listings use these details.';
    } else throw new InvalidArgumentException('Choose a valid action.',422);
    if($json)jobproJson(['success'=>true,'message'=>$message]);
    jobproFlash($message);jobproRedirect($back);
}catch(Throwable$error){
    $known=$error instanceof InvalidArgumentException||$error instanceof OutOfBoundsException||$error instanceof DomainException;
    $message=$known?$error->getMessage():'Employer tools are temporarily unavailable. Check the Phase 5 database migration and try again.';
    $status=$known&&in_array((int)$error->getCode(),[400,404,409,422,429],true)?(int)$error->getCode():503;
    if(!$known)error_log('JobPro employer action failed (code '.(int)$error->getCode().').');
    if($json)jobproJson(['success'=>false,'message'=>$message],$status);
    if($action==='save'&&isset($validation))jobproFormFailure('employer_job_'.($jobId??0),['form'=>$message],$validation['data'],$back);
    jobproFlash($message,'error');jobproRedirect($back);
}
