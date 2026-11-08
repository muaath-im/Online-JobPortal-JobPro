<?php
require_once jobproPath('src/Core/auth.php');
jobproStartSession();
$role=strtolower(jobproInput('registerType'));
$back=$role === 'company' ? '/pages/comRegister.php' : '/pages/register.php';
jobproPost($back);
if (isset($_SESSION['user_id'])) { jobproFlash('Log out before creating another account.', 'error'); jobproRedirect('/pages/account.php'); }
$data=[];
foreach(['username','email','fullname','companyName','phone'] as $field) $data[$field]=jobproInput($field);
$password=jobproSecret('password'); $confirmation=jobproSecret('confirm-password');
$errors=[];
if (!in_array($role,['personal','company'],true)) $errors['form']='Choose a personal or company account.';
if (!preg_match('/^[A-Za-z0-9_][A-Za-z0-9_.-]{2,29}$/D',$data['username'])) $errors['username']='Use 3–30 letters, numbers, dots, hyphens, or underscores.';
if (strlen($data['email'])>255 || !filter_var($data['email'],FILTER_VALIDATE_EMAIL)) $errors['email']='Enter a valid email address.';
$nameField=$role==='company'?'companyName':'fullname';
if ($data[$nameField]==='' || mb_strlen($data[$nameField])>255) $errors[$nameField]='Enter a name up to 255 characters.';
if (strlen($data['phone'])>20) $errors['phone']='Use up to 20 characters.';
if ($message=jobproPasswordError($password)) $errors['password']=$message;
if ($password !== $confirmation) $errors['confirm-password']='The passwords do not match.';
if (jobproInput('terms')!=='1' || jobproInput('details_correct')!=='1') $errors['terms']='Confirm your details and accept the terms to create an account.';
if ($errors) jobproFormFailure('register',$errors,$data,$back);
$db=null; $locked=false;
try {
    if(!jobproRate('register:'.jobproClientKey(),20,3600)) jobproFormFailure('register',['form'=>'Too many registrations. Please try again in an hour.'],$data,$back);
    $db=jobproDatabase();
    // The old schema has no unique username index. Serialize this registration path.
    $locked=(int)$db->query("SELECT GET_LOCK('jobpro_registration',5)")->fetch_row()[0]===1;
    if(!$locked) throw new RuntimeException('Registration is busy.');
    $stmt=$db->prepare('SELECT user_id FROM user WHERE username=? OR email=? LIMIT 1');
    $stmt->bind_param('ss',$data['username'],$data['email']); $stmt->execute(); $taken=$stmt->get_result()->num_rows>0; $stmt->close();
    if($taken) { $db->query("SELECT RELEASE_LOCK('jobpro_registration')"); $locked=false; jobproFormFailure('register',['form'=>'That username or email is already in use. Sign in or choose different details.'],$data,$back); }
    $db->begin_transaction(); $hash=jobproHash($password);
    $stmt=$db->prepare('INSERT INTO user (username,email,password,phone_number,account_type) VALUES (?,?,?,?,?)');
    $stmt->bind_param('sssss',$data['username'],$data['email'],$hash,$data['phone'],$role); $stmt->execute(); $id=$db->insert_id; $stmt->close();
    $sql=$role==='company'?'INSERT INTO company (user_id,company_name) VALUES (?,?)':'INSERT INTO person (user_id,full_name) VALUES (?,?)';
    $stmt=$db->prepare($sql);$stmt->bind_param('is',$id,$data[$nameField]);$stmt->execute();$stmt->close();
    $stmt=$db->prepare('INSERT INTO terms_acceptance (user_id,terms_accepted,details_correct) VALUES (?,1,1)');$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();
    $db->commit(); $db->query("SELECT RELEASE_LOCK('jobpro_registration')"); $locked=false;
    jobproFlash('Your account is ready. Sign in with your username to continue.');
    $_SESSION['form_login']=['errors'=>[],'old'=>['username'=>$data['username']]];
    jobproRedirect('/pages/login.php');
} catch(Throwable $error) {
    if($db) $db->rollback();
    if($locked) $db->query("SELECT RELEASE_LOCK('jobpro_registration')");
    error_log('JobPro registration failed (code '.$error->getCode().').');
    jobproFormFailure('register',['form'=>$error->getCode()===1062?'That username or email is already in use.':'We could not create your account. Please try again.'],$data,$back);
}
