<?php
require_once jobproPath('src/Services/employer.php');
jobproStartSession();$user=jobproApiUser('company');$id=jobproId($_GET['id']??null);
if(!$id||empty($user['company_id']))jobproJson(['success'=>false,'message'=>'Choose a valid job.'],400);
try {
    $job=jobproEmployerJob((int)$user['company_id'],$id);
    if(!$job)jobproJson(['success'=>false,'message'=>'Job not found.'],404);
    jobproJson($job);
}catch(Throwable$error){error_log('JobPro job details unavailable.');jobproJson(['success'=>false,'message'=>'Job details are temporarily unavailable.'],503);}