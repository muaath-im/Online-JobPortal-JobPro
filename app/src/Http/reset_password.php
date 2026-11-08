<?php
require_once jobproPath('src/Core/recovery.php');
jobproStartSession();
$token=jobproSecret('token');$parts=jobproResetParts($token);
$back=$parts?'/pages/reset-password.php?token='.rawurlencode($token):'/pages/forgot-password.php';
jobproPost($back);
$password=jobproSecret('password');$confirm=jobproSecret('confirm-password');$errors=[];
if($message=jobproPasswordError($password))$errors['password']=$message;
if($password!==$confirm)$errors['confirm-password']='The passwords do not match.';
if($errors)jobproFormFailure('reset',$errors,[],$back);
try{
    if(!jobproRate('reset:'.jobproClientKey(),20,900))throw new RuntimeException('Too many reset attempts.');
    if(!jobproConsumeReset($token,$password)){jobproFlash('This reset link is invalid, expired, or has already been used. Request a new link.','error');jobproRedirect('/pages/forgot-password.php');}
    $_SESSION=[];session_regenerate_id(true);jobproFlash('Your password has been reset. Sign in with your new password.');jobproRedirect('/pages/login.php');
}catch(Throwable $error){error_log('JobPro password reset failed (code '.$error->getCode().').');jobproFlash('We could not reset your password. Please request a new link or try again later.','error');jobproRedirect('/pages/forgot-password.php');}
