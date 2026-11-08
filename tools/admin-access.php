<?php
// Offline bootstrap only. No account creation and no passwords or configuration writes.
if(PHP_SAPI!=='cli'){http_response_code(403);exit('This operation is available only from the command line.');}
require dirname(__DIR__).'/app/src/config.php';
require jobproPath('src/Services/administration.php');
$command=$argv[1]??'';$username='';
foreach(array_slice($argv,2)as$argument){if(str_starts_with($argument,'--username='))$username=trim(substr($argument,11));else{fwrite(STDERR,"Unknown argument.\n");exit(1);}}
if(!in_array($command,['check','bootstrap'],true)||($command==='bootstrap'&&$username==='')){fwrite(STDERR,"Usage: php tools/admin-access.php check\n       php tools/admin-access.php bootstrap --username=YOUR_EXISTING_USERNAME\n");exit(1);}
$db=null;$locked=false;$transaction=false;
try{
    if(!jobproAdministrationReady())throw new RuntimeException('Apply database/migrations/006_administration.sql with a database administrator first.');
    $db=jobproDatabase();
    if($command==='check'){
        $count=(int)jobproAdminRows("SELECT COUNT(*) AS total FROM admin_memberships m JOIN user u ON u.user_id=m.user_id WHERE m.role='owner' AND u.account_status='active'")[0]['total'];
        echo 'Administration schema ready. Active owners: '.$count.PHP_EOL;exit(0);
    }
    $locked=(int)$db->query("SELECT GET_LOCK('jobpro_admin_bootstrap',5)")->fetch_row()[0]===1;
    if(!$locked)throw new RuntimeException('Bootstrap is busy. Try again.');
    $db->begin_transaction();$transaction=true;
    $members=jobproAdminRows('SELECT user_id,role FROM admin_memberships ORDER BY user_id FOR UPDATE');
    $users=jobproAdminRows('SELECT user_id,account_status FROM user WHERE username=? LIMIT 2 FOR UPDATE','s',[$username]);
    if(count($users)!==1)throw new RuntimeException('The username must identify exactly one existing JobPro account.');
    $id=(int)$users[0]['user_id'];
    if($users[0]['account_status']!=='active')throw new RuntimeException('The selected account must be active.');
    $existing=null;foreach($members as$member)if((int)$member['user_id']===$id)$existing=$member['role'];
    if($existing==='owner'){
        $db->commit();$transaction=false;echo 'Owner access is already enabled for the named account. No changes made.'.PHP_EOL;
    }else{
        if($members)throw new RuntimeException('Administration is already initialized. An owner must manage access in the administration interface.');
        $stmt=$db->prepare("INSERT INTO admin_memberships(user_id,role,granted_by) VALUES (?,'owner',NULL)");$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();
        jobproAdminAudit(null,'admin.bootstrap','admin',$id,'First owner initialized by the authorized local CLI operator.',['role'=>'none'],['role'=>'owner']);
        $db->commit();$transaction=false;echo 'Owner access granted to the named existing account. No account or password was created.'.PHP_EOL;
    }
}catch(Throwable$error){
    if($transaction&&$db)$db->rollback();
    $message=$error instanceof mysqli_sql_exception?'Database operation failed (code '.(int)$error->getCode().'). Check schema and normal application permissions.':$error->getMessage();
    fwrite(STDERR,'ERROR: '.$message.PHP_EOL);$failed=true;
}finally{if($locked&&$db)$db->query("SELECT RELEASE_LOCK('jobpro_admin_bootstrap')");}
if($failed??false)exit(1);
