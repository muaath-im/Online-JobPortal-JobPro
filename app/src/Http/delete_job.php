<?php
require_once jobproPath('src/Services/employer.php');
jobproStartSession();$user=jobproApiUser('company',true);$id=jobproId($_POST['job_id']??null);
if(!$id||empty($user['company_id']))jobproJson(['success'=>false,'message'=>'Choose a valid job.'],400);
try {
    jobproEmployerJobAction((int)$user['company_id'],$id,'delete');
    jobproJson(['success'=>true,'message'=>'Job deleted successfully.']);
}catch(Throwable$error){
    $known=$error instanceof OutOfBoundsException||$error instanceof DomainException;
    if(!$known)error_log('JobPro job deletion failed.');
    jobproJson(['success'=>false,'message'=>$known?$error->getMessage():'The job could not be deleted. Please try again.'],$known?(int)$error->getCode():503);
}