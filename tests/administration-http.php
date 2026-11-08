<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require (getenv('JOBPRO_TEST_APP_CONFIG')?:dirname(__DIR__).'/app/src/config.php');
require jobproPath('src/Services/administration.php');
require jobproPath('src/Services/employer.php');
require jobproPath('src/Services/candidate.php');
require jobproPath('src/Core/recovery.php');
$base=getenv('JOBPRO_TEST_BASE_URL')?:'';
if(!preg_match('~^http://127[.]0[.]0[.]1:[0-9]+$~D',$base))throw new RuntimeException('Use tests/run-regression.ps1 with its isolated loopback server.');
$db=jobproDatabase();
if(!preg_match('/^(localhost|127[.]0[.]0[.]1|::1)(?:\s|:)/',$db->host_info))throw new RuntimeException('Tests require local MySQL.');
if(!jobproAdministrationReady())throw new RuntimeException('Apply migration 006 with an administrator first.');
$clients=[];$fixtures=[];$checks=0;$failed=false;
function adminAssert(bool $ok,string $label): void{global$checks;if(!$ok)throw new RuntimeException($label);$checks++;echo 'PASS: '.$label.PHP_EOL;}
function adminClient(){global$clients;$h=curl_init();curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5,CURLOPT_TIMEOUT=>15,CURLOPT_COOKIEFILE=>'',CURLOPT_PROXY=>'']);$clients[]=$h;return $h;}
function adminRequest($h,string $path,?array $form=null,bool $json=false): array{
    global$base;curl_setopt($h,CURLOPT_URL,$base.$path);curl_setopt($h,CURLOPT_HTTPHEADER,$json?['Accept: application/json']:[]);
    if($form===null)curl_setopt($h,CURLOPT_HTTPGET,true);else{curl_setopt($h,CURLOPT_POST,true);curl_setopt($h,CURLOPT_POSTFIELDS,http_build_query($form));}
    $body=curl_exec($h);if($body===false)throw new RuntimeException('Loopback HTTP request failed.');
    return ['body'=>$body,'status'=>curl_getinfo($h,CURLINFO_RESPONSE_CODE),'url'=>curl_getinfo($h,CURLINFO_EFFECTIVE_URL)];
}
function adminDocument(string $body): DOMXPath{$d=new DOMDocument();$old=libxml_use_internal_errors(true);$d->loadHTML($body,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($old);return new DOMXPath($d);}
function adminToken(string $body): string{$nodes=adminDocument($body)->query('//input[@name="csrf_token"]');if(!$nodes->length)throw new RuntimeException('Test page CSRF field missing.');return $nodes->item(0)->getAttribute('value');}
function adminClean(array $r): bool{return $r['status']===200&&!preg_match('/Fatal error|Warning:|Parse error/',$r['body']);}
function adminLogin(array &$who,?string $password=null): array{$r=adminRequest($who['client'],'/pages/login.php');$r=adminRequest($who['client'],'/api/login_api.php',['csrf_token'=>adminToken($r['body']),'username'=>$who['username'],'password'=>$password??$who['password']]);if(str_contains($r['url'],'/pages/account.php'))$who['token']=adminToken($r['body']);return $r;}
function adminFixture(string $type,string $adminRole='',bool $login=true): array{
    global$db,$fixtures;$name='qa6_'.bin2hex(random_bytes(6));$email=$name.'@example.invalid';$password=bin2hex(random_bytes(20));$hash=jobproHash($password);
    $s=$db->prepare('INSERT INTO user(username,email,password,account_type)VALUES(?,?,?,?)');$s->bind_param('ssss',$name,$email,$hash,$type);$s->execute();$id=(int)$db->insert_id;$fixtures[]=$id;
    $table=$type==='company'?'company':'person';$column=$type==='company'?'company_name':'full_name';$display=$type==='company'?'QA6 Employer':'QA6 Candidate';$s=$db->prepare('INSERT INTO '.$table.'(user_id,'.$column.')VALUES(?,?)');$s->bind_param('is',$id,$display);$s->execute();$profile=(int)$db->insert_id;
    if($adminRole!==''){$s=$db->prepare('INSERT INTO admin_memberships(user_id,role)VALUES(?,?)');$s->bind_param('is',$id,$adminRole);$s->execute();}
    $who=['id'=>$id,'profile'=>$profile,'username'=>$name,'email'=>$email,'password'=>$password,'client'=>adminClient()];if($login){$r=adminLogin($who);adminAssert(adminClean($r)&&str_contains($r['url'],'/pages/account.php'),'Disposable '.$type.' / '.($adminRole?:'ordinary').' account can sign in');}return $who;
}
function adminPost(array $who,string $action,array $fields=[]): array{return adminRequest($who['client'],'/api/admin.php',array_replace(['csrf_token'=>$who['token'],'action'=>$action,'reason'=>'QA6 regression verification reason.'],$fields),true);}
function adminCount(?int $actor=null): int{$where=$actor?' WHERE actor_user_id=?':'';return (int)jobproAdminRows('SELECT COUNT(*) AS total FROM admin_audit_log'.$where,$actor?'i':'',$actor?[$actor]:[])[0]['total'];}
try{
    $owner=adminFixture('personal','owner');$moderator=adminFixture('personal','moderator');$candidate=adminFixture('personal');$employer=adminFixture('company');$extra=adminFixture('personal');
    $guest=adminClient();$adminRoutes=['admin.php','admin-users.php','admin-user.php?id='.$candidate['id'],'admin-companies.php','admin-jobs.php','admin-job.php?id=1','admin-applications.php','admin-application.php?id=1','admin-access.php','admin-audit.php'];
    foreach($adminRoutes as$route){$r=adminRequest($guest,'/pages/'.$route);adminAssert(str_contains($r['url'],'/pages/login.php'),'Anonymous page access redirects to login: '.$route);}
    $r=adminRequest($guest,'/api/admin.php',['action'=>'user_status','target_id'=>$candidate['id']],true);adminAssert(str_contains($r['url'],'/pages/login.php'),'Anonymous admin API access redirects to login');
    foreach([$candidate,$employer]as$ordinary){
        foreach($adminRoutes as$route){$r=adminRequest($ordinary['client'],'/pages/'.$route);adminAssert($r['status']===403,'Ordinary '.$ordinary['id'].' cannot access '.$route);}
        $r=adminPost($ordinary,'user_status',['target_id'=>$extra['id'],'status'=>'suspended','expected_status'=>'active']);adminAssert($r['status']===403,'An ordinary account cannot invoke admin mutations');
    }
    foreach(['admin.php','admin-users.php','admin-companies.php','admin-jobs.php','admin-applications.php','admin-access.php','admin-audit.php']as$route){$r=adminRequest($owner['client'],'/pages/'.$route);adminAssert(adminClean($r)&&adminDocument($r['body'])->query('//h1')->length===1&&adminDocument($r['body'])->query('//main')->length===1,'Owner page renders cleanly: '.$route);}
    foreach(['admin-users.php','admin-user.php?id='.$candidate['id'],'admin-applications.php','admin-application.php?id=1','admin-access.php']as$route){$r=adminRequest($moderator['client'],'/pages/'.$route);adminAssert($r['status']===403,'Moderator private-section restriction: '.$route);}
    $r=adminPost($moderator,'user_status',['target_id'=>$candidate['id'],'status'=>'suspended','expected_status'=>'active']);adminAssert($r['status']===403,'Moderator cannot suspend users through the API');
    $r=adminPost($moderator,'admin_access',['target_id'=>$extra['id'],'status'=>'owner','expected_status'=>'none']);adminAssert($r['status']===403,'Moderator cannot elevate administrator permissions');
    $before=adminCount($owner['id']);$r=adminPost($owner,'user_status',['target_id'=>$candidate['id'],'status'=>'suspended','expected_status'=>'active','csrf_token'=>'invalid']);adminAssert($r['status']===403&&adminCount($owner['id'])===$before,'Invalid CSRF causes no status change or audit entry');
    $r=adminRequest($owner['client'],'/api/admin.php');adminAssert($r['status']===405,'Admin mutations reject GET requests');
    $r=adminPost($owner,'user_status',['target_id'=>$owner['id'],'status'=>'suspended','expected_status'=>'active']);adminAssert($r['status']===409,'Owner cannot suspend their own account');
    foreach(['none','moderator']as$role){$r=adminPost($owner,'admin_access',['target_id'=>$owner['id'],'status'=>$role,'expected_status'=>'owner']);adminAssert($r['status']===409,'Owner cannot remove or demote their own access: '.$role);}
    $r=adminRequest($owner['client'],'/api/delete_account.php',['csrf_token'=>$owner['token'],'delete_confirmation'=>'DELETE','delete_password'=>$owner['password']]);adminAssert(adminClean($r)&&str_contains($r['body'],'Another owner must revoke')&&jobproAdminMembership($owner['id'])!==null,'Account deletion cannot bypass self-access or last-owner protection');
    $r=adminPost($owner,'user_status',['target_id'=>$moderator['id'],'status'=>'suspended','expected_status'=>'active']);adminAssert($r['status']===409,'An administrator must lose admin access before suspension');
    $r=adminPost($owner,'user_status',['target_id'=>$candidate['id'],'status'=>'suspended','expected_status'=>'active','reason'=>'short']);adminAssert($r['status']===422,'A meaningful moderation reason is required');
    $r=adminPost($owner,'user_status',['target_id'=>$candidate['id'],'status'=>'suspended','expected_status'=>'active','password'=>'DO_NOT_LOG_THIS_PASSWORD','token'=>'DO_NOT_LOG_THIS_TOKEN']);adminAssert($r['status']===200,'Owner can suspend an ordinary account');
    $entry=jobproAdminRows('SELECT * FROM admin_audit_log WHERE actor_user_id=? ORDER BY id DESC LIMIT 1','i',[$owner['id']])[0];$encoded=json_encode($entry);
    adminAssert($entry['action']==='user.suspended'&&(int)$entry['target_id']===$candidate['id']&&!str_contains($encoded,'DO_NOT_LOG_THIS')&&!str_contains($encoded,$candidate['password']),'Suspend audit records actor/action/target without supplied passwords or tokens');
    $r=adminRequest($candidate['client'],'/pages/account.php');adminAssert(str_contains($r['url'],'/pages/login.php'),'Suspension ends an existing page session on its next protected request');
    $r=adminLogin($candidate);adminAssert(str_contains($r['url'],'/pages/login.php')&&str_contains($r['body'],'username or password is incorrect')&&!str_contains($r['body'],'suspended'),'Suspended sign-in fails with the existing generic message');
    $r=adminRequest($extra['client'],'/pages/account.php');adminAssert(adminClean($r)&&str_contains($r['url'],'/pages/account.php'),'An unrelated active account remains unaffected');
    // Recovery uses isolated local capture. No message is sent externally and no token is printed.
    $recovery=adminClient();$r=adminRequest($recovery,'/pages/forgot-password.php');$r=adminRequest($recovery,'/api/request_reset.php',['csrf_token'=>adminToken($r['body']),'email'=>$candidate['email']]);
    $resetToken=null;foreach(glob(jobproAuthStorage().DIRECTORY_SEPARATOR.'mail-*.json')?:[]as$file){$mail=json_decode(file_get_contents($file),true);if(($mail['to']??'')===$candidate['email']&&preg_match('/[?]token=([a-f0-9]+[.][a-f0-9]+)/',$mail['body']??'',$match))$resetToken=$match[1];}
    adminAssert(is_string($resetToken),'Suspended-account recovery can be tested with private local capture');
    $r=adminRequest($recovery,'/pages/reset-password.php?token='.rawurlencode($resetToken));$newPassword=bin2hex(random_bytes(20));
    $r=adminRequest($recovery,'/api/reset_password.php',['csrf_token'=>adminToken($r['body']),'token'=>$resetToken,'password'=>$newPassword,'confirm-password'=>$newPassword]);adminAssert(str_contains($r['url'],'/pages/login.php'),'Recovery resets the test password without signing the account in');
    $candidate['password']=$newPassword;$r=adminLogin($candidate);adminAssert(str_contains($r['url'],'/pages/login.php'),'Resetting a password cannot bypass suspension');
    $r=adminPost($owner,'user_status',['target_id'=>$candidate['id'],'status'=>'active','expected_status'=>'suspended']);adminAssert($r['status']===200,'Owner can reactivate the account');
    $r=adminLogin($candidate);adminAssert(adminClean($r)&&str_contains($r['url'],'/pages/account.php'),'Reactivation restores normal access with the existing password rules');
    $r=adminPost($owner,'user_status',['target_id'=>$candidate['id'],'status'=>'suspended','expected_status'=>'suspended']);adminAssert($r['status']===409,'Stale account status forms do not overwrite new decisions');
    $baseJob=['job_title'=>'QA6 Accessible Engineer','job_category'=>'Software Development','job_location'=>'Colombo','job_type'=>'Full-time','min_salary'=>'100.25','max_salary'=>'200.50','job_description'=>'Create clear, accessible interfaces. <script>do_not_execute()</script>','publication_status'=>'open'];
    $jobId=jobproEmployerSaveJob($employer['profile'],null,$baseJob);$appId=jobproCandidateApply($candidate['id'],$jobId,'QA6 cover letter <script>do_not_execute()</script>');jobproCandidateSave($candidate['id'],$jobId,true);
    $r=adminPost($moderator,'job_visibility',['target_id'=>$jobId,'status'=>'hidden','expected_status'=>'visible']);adminAssert($r['status']===200,'Moderator can hide a listing');
    adminAssert(jobproEmployerStats($employer['profile'])['open']===0,'Employer live-opportunity counts exclude listings hidden by administration');
    $r=adminRequest($guest,'/api/discovery.php?id='.$jobId);adminAssert($r['status']===404,'Hidden listing is unavailable through public JSON');
    $r=adminRequest($candidate['client'],'/pages/apply.php?id='.$jobId);adminAssert($r['status']===404,'Hidden listing has no candidate application page');
    $r=adminRequest($candidate['client'],'/api/candidate.php',['csrf_token'=>$candidate['token'],'action'=>'apply','job_id'=>$jobId,'reviewed'=>'yes'],true);adminAssert($r['status']===404,'Hidden listing rejects direct application submissions');
    $r=adminRequest($employer['client'],'/pages/jobs.php');adminAssert(adminClean($r)&&str_contains($r['body'],'Hidden by administration'),'Employer can see that moderation is restricting the listing');
    $r=adminRequest($employer['client'],'/api/update_job.php',['csrf_token'=>$employer['token'],'job_id'=>$jobId,'job_title'=>'QA6 Owner Edit','job_description'=>'Valid edited description','job_location'=>'Remote','min_salary'=>'100.25','max_salary'=>'200.50','moderation_status'=>'visible'],true);adminAssert($r['status']===200&&jobproAdminJob($jobId)['moderation_status']==='hidden','An employer edit cannot bypass administrator visibility');
    $r=adminPost($moderator,'job_visibility',['target_id'=>$jobId,'status'=>'visible','expected_status'=>'hidden']);adminAssert($r['status']===200,'Moderator can restore listing visibility');
    $r=adminPost($owner,'user_status',['target_id'=>$employer['id'],'status'=>'suspended','expected_status'=>'active']);adminAssert($r['status']===200,'Owner can suspend an employer');
    $r=adminRequest($guest,'/api/discovery.php?id='.$jobId);adminAssert($r['status']===404,'Suspended employers disappear from public job discovery');
    $r=adminRequest($owner['client'],'/pages/admin-jobs.php?q=QA6%20Owner%20Edit&status=open&visibility=visible&employer=active');adminAssert(adminClean($r)&&str_contains($r['body'],'No matching opportunities.'),'Live-listing filters exclude suspended employers consistently with dashboard counts');
    $r=adminRequest($guest,'/pages/company.php?id='.$employer['profile']);adminAssert($r['status']===404,'Suspended employer public profile is unavailable');
    $r=adminRequest($employer['client'],'/api/employer.php',['csrf_token'=>$employer['token'],'action'=>'open','job_id'=>$jobId],true);adminAssert($r['status']===401,'Suspension invalidates an existing protected API session');
    $r=adminPost($owner,'user_status',['target_id'=>$employer['id'],'status'=>'active','expected_status'=>'suspended']);adminAssert($r['status']===200,'Employer can be reactivated');
    $r=adminLogin($employer);adminAssert(adminClean($r),'Reactivated employer can sign in normally');
    $r=adminPost($owner,'job_remove',['target_id'=>$jobId,'expected_status'=>'visible','expected_publication'=>'open']);adminAssert($r['status']===422,'Listing removal requires explicit confirmation');
    $r=adminPost($owner,'job_remove',['target_id'=>$jobId,'expected_status'=>'visible','expected_publication'=>'open','confirm'=>'yes']);$job=jobproAdminJob($jobId);adminAssert($r['status']===200&&$job['publication_status']==='closed'&&$job['moderation_status']==='hidden','Removal closes and hides the listing');
    adminAssert(jobproAdminApplication($appId)!==null,'Removing a listing preserves its application records');
    $r=adminPost($owner,'application_status',['target_id'=>$appId,'status'=>'reviewing','expected_status'=>'submitted']);adminAssert($r['status']===200,'Owner can correct an application status');
    $r=adminRequest($candidate['client'],'/pages/applications.php');adminAssert(adminClean($r)&&str_contains($r['body'],'In review'),'Candidate sees the corrected application status');
    $r=adminPost($moderator,'application_status',['target_id'=>$appId,'status'=>'hired','expected_status'=>'reviewing']);adminAssert($r['status']===403,'Moderator cannot edit private application records');
    $r=adminPost($owner,'admin_access',['username'=>$extra['username'],'status'=>'moderator','expected_status'=>'none']);adminAssert($r['status']===200,'Owner can grant access to an existing account');
    $r=adminRequest($extra['client'],'/pages/admin-jobs.php');adminAssert(adminClean($r),'Granted access works with the existing session');
    $r=adminPost($owner,'admin_access',['target_id'=>$extra['id'],'status'=>'owner','expected_status'=>'moderator']);adminAssert($r['status']===200&&jobproAdminMembership($extra['id'])['role']==='owner','Owner can promote another administrator');
    $r=adminPost($owner,'admin_access',['target_id'=>$extra['id'],'status'=>'moderator','expected_status'=>'owner']);adminAssert($r['status']===200&&jobproAdminMembership($extra['id'])['role']==='moderator','Another owner can be demoted when owners remain');
    $r=adminPost($owner,'admin_access',['target_id'=>$extra['id'],'status'=>'none','expected_status'=>'moderator']);adminAssert($r['status']===200,'Owner can revoke another administrator');
    $r=adminRequest($extra['client'],'/pages/admin.php');adminAssert($r['status']===403,'Revocation is enforced on the next request');
    $r=adminRequest($moderator['client'],'/pages/admin-audit.php');adminAssert(adminClean($r)&&!str_contains($r['body'],'user.suspended')&&str_contains($r['body'],'job.hidden'),'Moderator audit view contains only their own decisions');
    $r=adminRequest($owner['client'],'/pages/admin-audit.php');adminAssert(adminClean($r)&&str_contains($r['body'],'user.suspended')&&str_contains($r['body'],'admin.access'),'Owner can inspect the state-change audit trail');
    adminAssert(adminDocument($r['body'])->query('//form[@action="/api/admin.php"]')->length===0,'Audit log exposes no modification controls');
    $r=adminPost($owner,'audit_delete',['target_id'=>1]);adminAssert($r['status']===422,'No audit-log mutation action is available');
    foreach(['/pages/admin-user.php?id=invalid','/pages/admin-job.php?id=invalid','/pages/admin-application.php?id=invalid']as$route){$r=adminRequest($owner['client'],$route);adminAssert($r['status']===404,'Malformed admin detail ID returns 404: '.$route);}
    $r=adminRequest($owner['client'],'/pages/admin-user.php?id='.$candidate['id']);$xp=adminDocument($r['body']);adminAssert(adminClean($r)&&$xp->query('//input[@name="password"]')->length===0&&!str_contains($r['body'],jobproUserById($candidate['id'])['password']),'Administrator account review does not expose password fields');
    $r=adminRequest($owner['client'],'/pages/admin-application.php?id='.$appId);adminAssert(adminClean($r)&&adminDocument($r['body'])->query('//script[contains(text(),"do_not_execute")]')->length===0&&str_contains($r['body'],'&lt;script&gt;'),'Private candidate text is escaped in administrator review');
    $logs=jobproAdminRows('SELECT before_state,after_state FROM admin_audit_log WHERE actor_user_id=?','i',[$owner['id']]);$safe=true;foreach($logs as$log){$raw=$log['before_state'].$log['after_state'];if(preg_match('/password|token|api_key|cover_letter/i',$raw))$safe=false;}adminAssert($safe,'Audit snapshots contain only bounded role/status summaries');
    $r=adminRequest($owner['client'],'/pages/admin-users.php?q='.rawurlencode("' OR 1=1 --"));adminAssert(adminClean($r)&&str_contains($r['body'],'No matching accounts.'),'Account search treats SQL-like input as plain text');
    for($i=0;$i<11;$i++){$pageUser=adminFixture('personal','',false);$marker='QA6 Pagination Marker';$s=$db->prepare('UPDATE person SET full_name=? WHERE user_id=?');$s->bind_param('si',$marker,$pageUser['id']);$s->execute();}
    $r=adminRequest($owner['client'],'/pages/admin-users.php?q=QA6%20Pagination%20Marker&page=2');adminAssert(adminClean($r)&&adminDocument($r['body'])->query('//tbody/tr')->length===1,'Administrator account search paginates correctly on page two');
    $r=adminRequest($owner['client'],'/pages/admin-audit.php?page=2');adminAssert(adminClean($r)&&adminDocument($r['body'])->query('//nav[@aria-label="Results pages"]//a[@aria-current="page" and text()="2"]')->length===1,'Audit records paginate without adding mutation controls');
    adminAssert(!str_starts_with(realpath(dirname(__DIR__).'/tools/admin-access.php'),realpath(jobproWebRoot()).DIRECTORY_SEPARATOR),'CLI grant tool is physically outside the public document root');
    $before=adminCount();$r=adminRequest($owner['client'],'/__cli_guard_probe.php');adminAssert($r['status']===403&&str_contains($r['body'],'command line')&&adminCount()===$before,'CLI bootstrap refuses HTTP execution without changing permissions or audit records');
    $rateOwner=adminFixture('personal','owner');
    for($i=0;$i<21;$i++)$r=adminPost($rateOwner,'admin_access',['username'=>'qa6_missing_'.bin2hex(random_bytes(5)),'status'=>'moderator','expected_status'=>'none']);
    adminAssert($r['status']===429&&adminCount($rateOwner['id'])===0,'Administrator access changes are rate limited without creating audit noise or granting access');
    $loginProbe=adminFixture('personal','',false);
    for($i=0;$i<9;$i++)$r=adminLogin($loginProbe,'This test password is deliberately incorrect.');
    adminAssert(str_contains($r['url'],'/pages/login.php')&&str_contains($r['body'],'Too many sign-in attempts'),'Existing login rate limiting remains enforced');
    echo $checks.' administration HTTP/database checks passed.'.PHP_EOL;
}catch(Throwable$error){echo 'FAIL: '.$error->getMessage().PHP_EOL;$failed=true;}
finally{
    foreach($clients as$client)curl_close($client);
    foreach($fixtures as$id){$stmt=$db->prepare('DELETE FROM admin_audit_log WHERE actor_user_id=?');$stmt->bind_param('i',$id);$stmt->execute();$stmt=$db->prepare('DELETE FROM admin_memberships WHERE user_id=?');$stmt->bind_param('i',$id);$stmt->execute();}
    foreach($fixtures as$id){foreach(jobproAdminRows('SELECT company_id FROM company WHERE user_id=?','i',[$id])as$row){$cid=(int)$row['company_id'];$stmt=$db->prepare('DELETE a FROM applications a JOIN jobs j ON j.id=a.job_id WHERE j.company_id=?');$stmt->bind_param('i',$cid);$stmt->execute();$stmt=$db->prepare('DELETE FROM jobs WHERE company_id=?');$stmt->bind_param('i',$cid);$stmt->execute();}foreach(['saved_jobs','applications','terms_acceptance','person','company','user']as$table){$stmt=$db->prepare('DELETE FROM '.$table.' WHERE user_id=?');$stmt->bind_param('i',$id);$stmt->execute();}}
    echo 'Disposable administration fixtures removed; real administrator access preserved.'.PHP_EOL;
}
if($failed)exit(1);
