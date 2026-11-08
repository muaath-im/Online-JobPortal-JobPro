<?php
require_once jobproPath('src/Core/recovery.php');
jobproStartSession();jobproPost('/pages/forgot-password.php');
$email=jobproInput('email');$old=['email'=>mb_substr($email,0,255)];
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>255)jobproFormFailure('recovery',['email'=>'Enter a valid email address.'],$old,'/pages/forgot-password.php');
if(!jobproMailReady()){jobproFlash('Email recovery is not configured yet. Configure the private email settings before requesting a link.','error');jobproRedirect('/pages/forgot-password.php');}
$start=microtime(true);
$recoveryMessage=jobproMailConfig()['transport']==='log'
    ?'If an account matches that email, a recovery message has been prepared in the private local preview. No email was sent.'
    :'If an account matches that email, look for a recovery message in your inbox and spam folder.';
try{
    if(!jobproRate('recovery-ip:'.jobproClientKey(),12,3600)||!jobproRate('recovery:'.mb_strtolower($email),3,900)){jobproFlash('If a matching account exists, a recovery message can be sent. Please wait before requesting another link.');jobproRedirect('/pages/forgot-password.php');}
    $stmt=jobproDatabase()->prepare('SELECT user_id,email,password FROM user WHERE email=? LIMIT 1');$stmt->bind_param('s',$email);$stmt->execute();$user=$stmt->get_result()->fetch_assoc();$stmt->close();
    if($user)jobproIssueReset($user);
    $remaining=300000-(int)((microtime(true)-$start)*1000000);if($remaining>0)usleep($remaining);
    jobproFlash($recoveryMessage);
}catch(Throwable $error){error_log('JobPro recovery delivery failed (code '.$error->getCode().').');jobproFlash($recoveryMessage);}
jobproRedirect('/pages/forgot-password.php');
