<?php
require_once jobproPath('src/Services/employer.php');
jobproStartSession();
$user=jobproApiUser('company',true);$id=jobproId($_POST['job_id']??null);
if(!$id||empty($user['company_id']))jobproJson(['success'=>false,'message'=>'Choose a valid job.'],400);
try {
    $job=jobproEmployerJob((int)$user['company_id'],$id);
    if(!$job)throw new OutOfBoundsException('Job not found.',404);
    $allowed=array_intersect_key($_POST,array_flip(['job_title','job_description','job_category','job_type','job_location','min_salary','max_salary','skill_required','notice_period','joining_facility','publication_status']));
    jobproEmployerSaveJob((int)$user['company_id'],$id,array_replace($job,$allowed));
    jobproJson(['success'=>true,'message'=>'Job updated successfully.']);
}catch(Throwable$error){
    $known=$error instanceof OutOfBoundsException||$error instanceof InvalidArgumentException;
    if(!$known)error_log('JobPro job update failed.');
    jobproJson(['success'=>false,'message'=>$known?$error->getMessage():'The job could not be updated. Please try again.'],$known?(int)$error->getCode():503);
}