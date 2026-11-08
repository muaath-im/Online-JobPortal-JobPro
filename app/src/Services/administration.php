<?php
require_once jobproPath('src/Services/workflow.php');

function jobproAdminRows(string $sql,string $types='',array $values=[]): array
{
    $stmt=jobproDatabase()->prepare($sql);
    if($types!=='')$stmt->bind_param($types,...$values);
    $stmt->execute();$rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();return $rows;
}

function jobproAdministrationReady(): bool
{
    static $ready;
    if($ready!==null)return $ready;
    $rows=jobproAdminRows("SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('user','jobs','admin_memberships','admin_audit_log')");
    $columns=[];foreach($rows as$row)$columns[$row['TABLE_NAME']][]=$row['COLUMN_NAME'];
    foreach(['user'=>['account_status','account_status_reason','account_status_updated_at'],'jobs'=>['moderation_status','moderation_reason','moderated_at'],'admin_memberships'=>['user_id','role','granted_by','created_at'],'admin_audit_log'=>['id','actor_user_id','action','target_type','target_id','reason','before_state','after_state','created_at']]as$table=>$required){
        if(array_diff($required,$columns[$table]??[]))return $ready=false;
    }
    return $ready=true;
}

function jobproAdminMembership(int $userId): ?array
{
    if(!jobproAdministrationReady())return null;
    return jobproAdminRows("SELECT m.user_id,m.role,m.created_at FROM admin_memberships m JOIN user u ON u.user_id=m.user_id WHERE m.user_id=? AND u.account_status='active'",'i',[$userId])[0]??null;
}

function jobproAdminCan(string $role,string $section): bool
{
    return $role==='owner'||($role==='moderator'&&in_array($section,['overview','companies','jobs','job','audit'],true));
}

function jobproAdminMemberChangeError(int $actorId,int $targetId,string $current,string $role,int $owners): ?string
{
    if($actorId===$targetId)return 'You cannot change your own administrator access.';
    if($current==='owner'&&$role!=='owner'&&$owners<=1)return 'At least one owner must remain.';
    return null;
}

function jobproAdminReason(string $reason): ?string
{
    return !mb_check_encoding($reason,'UTF-8')||str_contains($reason,"\0")||mb_strlen($reason)<10||mb_strlen($reason)>500
        ?'Enter a clear reason of 10–500 characters.':null;
}

function jobproAdminAudit(?int $actor,string $action,string $type,int $target,string $reason,array $before,array $after): void
{
    // Only bounded status/role summaries enter this log: never credentials or profiles.
    $old=json_encode($before,JSON_THROW_ON_ERROR);$new=json_encode($after,JSON_THROW_ON_ERROR);
    $stmt=jobproDatabase()->prepare('INSERT INTO admin_audit_log(actor_user_id,action,target_type,target_id,reason,before_state,after_state) VALUES (?,?,?,?,?,?,?)');
    $stmt->bind_param('ississs',$actor,$action,$type,$target,$reason,$old,$new);$stmt->execute();$stmt->close();
}

function jobproAdminActor(int $userId,string $section): array
{
    // Serialize administrative writes, including last-owner and membership changes.
    $members=jobproAdminRows('SELECT user_id,role FROM admin_memberships ORDER BY user_id FOR UPDATE');
    $actor=null;foreach($members as$member)if((int)$member['user_id']===$userId)$actor=$member;
    $active=jobproAdminRows('SELECT account_status FROM user WHERE user_id=? FOR UPDATE','i',[$userId])[0]['account_status']??null;
    if(!$actor||$active!=='active'||!jobproAdminCan($actor['role'],$section))throw new DomainException('Your administrator permissions changed. Reload before continuing.',403);
    return $actor;
}

function jobproAdminChangeUser(int $actorId,int $targetId,string $status,string $expected,string $reason): void
{
    if(!in_array($status,['active','suspended'],true)||!in_array($expected,['active','suspended'],true))throw new InvalidArgumentException('Choose a valid account status.',422);
    if($error=jobproAdminReason($reason))throw new InvalidArgumentException($error,422);
    $db=jobproDatabase();$db->begin_transaction();
    try{
        jobproAdminActor($actorId,'users');
        if($actorId===$targetId)throw new DomainException('You cannot suspend your own account.',409);
        if(jobproAdminRows('SELECT user_id FROM admin_memberships WHERE user_id=?','i',[$targetId]))throw new DomainException('Revoke administrator access before suspending this account.',409);
        $row=jobproAdminRows('SELECT account_status FROM user WHERE user_id=? FOR UPDATE','i',[$targetId])[0]??null;
        if(!$row)throw new OutOfBoundsException('Account not found.',404);
        if($row['account_status']!==$expected)throw new DomainException('This account changed in another tab. Review the latest status.',409);
        if($status!==$expected){
            $stmt=$db->prepare('UPDATE user SET account_status=?,account_status_reason=?,account_status_updated_at=CURRENT_TIMESTAMP WHERE user_id=?');
            $stmt->bind_param('ssi',$status,$reason,$targetId);$stmt->execute();$stmt->close();
            jobproAdminAudit($actorId,'user.'.$status,'user',$targetId,$reason,['status'=>$expected],['status'=>$status]);
        }
        $db->commit();
    }catch(Throwable$error){$db->rollback();throw$error;}
}

function jobproAdminModerateJob(int $actorId,int $jobId,string $status,string $expected,string $reason): void
{
    if(!in_array($status,['visible','hidden'],true)||!in_array($expected,['visible','hidden'],true))throw new InvalidArgumentException('Choose a valid moderation status.',422);
    if($error=jobproAdminReason($reason))throw new InvalidArgumentException($error,422);
    $db=jobproDatabase();$db->begin_transaction();
    try{
        jobproAdminActor($actorId,'jobs');
        $row=jobproAdminRows('SELECT moderation_status FROM jobs WHERE id=? FOR UPDATE','i',[$jobId])[0]??null;
        if(!$row)throw new OutOfBoundsException('Job not found.',404);
        if($row['moderation_status']!==$expected)throw new DomainException('This job changed in another tab. Review its latest visibility.',409);
        if($status!==$expected){
            $stmt=$db->prepare('UPDATE jobs SET moderation_status=?,moderation_reason=?,moderated_at=CURRENT_TIMESTAMP WHERE id=?');$stmt->bind_param('ssi',$status,$reason,$jobId);$stmt->execute();$stmt->close();
            jobproAdminAudit($actorId,'job.'.$status,'job',$jobId,$reason,['moderation_status'=>$expected],['moderation_status'=>$status]);
        }
        $db->commit();
    }catch(Throwable$error){$db->rollback();throw$error;}
}

function jobproAdminRemoveJob(int $actorId,int $id,string $expected,string $publication,string $reason): void
{
    if($error=jobproAdminReason($reason))throw new InvalidArgumentException($error,422);
    $db=jobproDatabase();$db->begin_transaction();
    try{
        jobproAdminActor($actorId,'jobs');
        $row=jobproAdminRows('SELECT moderation_status,publication_status FROM jobs WHERE id=? FOR UPDATE','i',[$id])[0]??null;
        if(!$row)throw new OutOfBoundsException('Job not found.',404);
        if($row['moderation_status']!==$expected||$row['publication_status']!==$publication)throw new DomainException('This listing changed. Review the current visibility and publication state.',409);
        if($row['moderation_status']!=='hidden'||$row['publication_status']!=='closed'){
            $stmt=$db->prepare("UPDATE jobs SET moderation_status='hidden',publication_status='closed',moderation_reason=?,moderated_at=CURRENT_TIMESTAMP WHERE id=?");$stmt->bind_param('si',$reason,$id);$stmt->execute();$stmt->close();
            jobproAdminAudit($actorId,'job.remove','job',$id,$reason,$row,['moderation_status'=>'hidden','publication_status'=>'closed']);
        }
        $db->commit();
    }catch(Throwable$error){$db->rollback();throw$error;}
}

function jobproAdminChangeApplication(int $actorId,int $id,string $status,string $expected,string $reason): void
{
    if(!isset(jobproApplicationStatuses()[$status],jobproApplicationStatuses()[$expected]))throw new InvalidArgumentException('Choose a valid application status.',422);
    if($error=jobproAdminReason($reason))throw new InvalidArgumentException($error,422);
    $db=jobproDatabase();$db->begin_transaction();
    try{
        jobproAdminActor($actorId,'applications');
        $row=jobproAdminRows('SELECT status FROM applications WHERE id=? FOR UPDATE','i',[$id])[0]??null;
        if(!$row)throw new OutOfBoundsException('Application not found.',404);
        if($row['status']!==$expected)throw new DomainException('This application changed. Review the latest status.',409);
        if($status!==$expected){$stmt=$db->prepare('UPDATE applications SET status=?,status_updated_at=CURRENT_TIMESTAMP WHERE id=?');$stmt->bind_param('si',$status,$id);$stmt->execute();$stmt->close();jobproAdminAudit($actorId,'application.status','application',$id,$reason,['status'=>$expected],['status'=>$status]);}
        $db->commit();
    }catch(Throwable$error){$db->rollback();throw$error;}
}

function jobproAdminSetMember(int $actorId,int $targetId,string $role,string $expected,string $reason): void
{
    if(!in_array($role,['owner','moderator','none'],true)||!in_array($expected,['owner','moderator','none'],true))throw new InvalidArgumentException('Choose a valid access role.',422);
    if($error=jobproAdminReason($reason))throw new InvalidArgumentException($error,422);
    $db=jobproDatabase();$db->begin_transaction();
    try{
        jobproAdminActor($actorId,'access');
        $user=jobproAdminRows('SELECT user_id,account_status FROM user WHERE user_id=? FOR UPDATE','i',[$targetId])[0]??null;
        if(!$user)throw new OutOfBoundsException('Account not found.',404);
        if($user['account_status']!=='active')throw new DomainException('Restore this account before granting administrator access.',409);
        $current=jobproAdminRows('SELECT role FROM admin_memberships WHERE user_id=?','i',[$targetId])[0]['role']??'none';
        if($current!==$expected)throw new DomainException('Administrator access changed. Reload the access list.',409);
        $owners=(int)jobproAdminRows("SELECT COUNT(*) AS total FROM admin_memberships WHERE role='owner'")[0]['total'];
        if($error=jobproAdminMemberChangeError($actorId,$targetId,$current,$role,$owners))throw new DomainException($error,409);
        if($role!==$current){
            if($role==='none'){$stmt=$db->prepare('DELETE FROM admin_memberships WHERE user_id=?');$stmt->bind_param('i',$targetId);}
            else{$stmt=$db->prepare('INSERT INTO admin_memberships(user_id,role,granted_by) VALUES(?,?,?) ON DUPLICATE KEY UPDATE role=VALUES(role),granted_by=VALUES(granted_by)');$stmt->bind_param('isi',$targetId,$role,$actorId);}
            $stmt->execute();$stmt->close();jobproAdminAudit($actorId,'admin.access','admin',$targetId,$reason,['role'=>$current],['role'=>$role]);
        }
        $db->commit();
    }catch(Throwable$error){$db->rollback();throw$error;}
}

function jobproAdminPage(string $select,string $from,string $where,string $types,array $values,string $order,int $page): array
{
    $total=(int)jobproAdminRows('SELECT COUNT(*) AS total '.$from.' WHERE '.$where,$types,$values)[0]['total'];
    $pages=max(1,(int)ceil($total/10));$page=max(1,min($page,$pages));$offset=($page-1)*10;
    $rows=jobproAdminRows('SELECT '.$select.' '.$from.' WHERE '.$where.' ORDER BY '.$order.' LIMIT 10 OFFSET ?',$types.'i',[...$values,$offset]);
    return compact('rows','total','pages','page');
}

function jobproAdminUsers(string $query,string $role,string $status,string $sort,int $page): array
{
    $search='%'.addcslashes(mb_substr($query,0,100),'\\%_').'%';$where='(u.username LIKE ? OR u.email LIKE ? OR COALESCE(p.full_name,c.company_name,\'\') LIKE ?)';$values=[$search,$search,$search];$types='sss';
    if(in_array($role,['personal','company'],true)){$where.=' AND u.account_type=?';$values[]=$role;$types.='s';}
    if(in_array($status,['active','suspended'],true)){$where.=' AND u.account_status=?';$values[]=$status;$types.='s';}
    $order=['name'=>'u.username ASC,u.user_id ASC','oldest'=>'u.created_at ASC,u.user_id ASC'][$sort]??'u.created_at DESC,u.user_id DESC';
    return jobproAdminPage('u.user_id,u.username,u.email,u.account_type,u.account_status,u.created_at,COALESCE(p.full_name,c.company_name) AS display_name,m.role AS admin_role','FROM user u LEFT JOIN person p ON p.user_id=u.user_id LEFT JOIN company c ON c.user_id=u.user_id LEFT JOIN admin_memberships m ON m.user_id=u.user_id',$where,$types,$values,$order,$page);
}

function jobproAdminUser(int $id): ?array
{
    return jobproAdminRows('SELECT u.user_id,u.username,u.email,u.phone_number,u.account_type,u.account_status,u.account_status_reason,u.account_status_updated_at,u.created_at,p.full_name,p.qualification,p.location,p.country,c.company_id,c.company_name,c.website_link,c.address,m.role AS admin_role,(SELECT COUNT(*) FROM applications a WHERE a.user_id=u.user_id) AS application_count,(SELECT COUNT(*) FROM jobs j WHERE j.company_id=c.company_id) AS job_count FROM user u LEFT JOIN person p ON p.user_id=u.user_id LEFT JOIN company c ON c.user_id=u.user_id LEFT JOIN admin_memberships m ON m.user_id=u.user_id WHERE u.user_id=?','i',[$id])[0]??null;
}

function jobproAdminCompanies(string $query,string $status,string $sort,int $page): array
{
    $search='%'.addcslashes(mb_substr($query,0,100),'\\%_').'%';$where='(COALESCE(c.company_name,\'\') LIKE ? OR COALESCE(c.address,\'\') LIKE ?)';$types='ss';$values=[$search,$search];
    if(in_array($status,['active','suspended'],true)){$where.=' AND u.account_status=?';$types.='s';$values[]=$status;}
    return jobproAdminPage('c.company_id,c.company_name,c.website_link,c.address,u.user_id,u.username,u.account_status,(SELECT COUNT(*) FROM jobs j WHERE j.company_id=c.company_id) AS job_count','FROM company c JOIN user u ON u.user_id=c.user_id',$where,$types,$values,$sort==='oldest'?'c.company_id ASC':'c.company_id DESC',$page);
}

function jobproAdminJobs(string $query,string $status,string $visibility,int $companyId,string $sort,int $page,string $employerStatus=''): array
{
    $search='%'.addcslashes(mb_substr($query,0,100),'\\%_').'%';$where='(j.job_title LIKE ? OR COALESCE(c.company_name,\'\') LIKE ?)';$types='ss';$values=[$search,$search];
    if(in_array($status,['draft','open','closed'],true)){$where.=' AND j.publication_status=?';$types.='s';$values[]=$status;}
    if(in_array($visibility,['visible','hidden'],true)){$where.=' AND j.moderation_status=?';$types.='s';$values[]=$visibility;}
    if($companyId>0){$where.=' AND j.company_id=?';$types.='i';$values[]=$companyId;}
    if(in_array($employerStatus,['active','suspended'],true)){$where.=' AND u.account_status=?';$types.='s';$values[]=$employerStatus;}
    return jobproAdminPage('j.id,j.job_title,j.job_location,j.publication_status,j.moderation_status,j.created_at,c.company_name,u.account_status AS employer_status,(SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) AS applicant_count','FROM jobs j LEFT JOIN company c ON c.company_id=j.company_id LEFT JOIN user u ON u.user_id=c.user_id',$where,$types,$values,$sort==='oldest'?'j.created_at ASC,j.id ASC':'j.created_at DESC,j.id DESC',$page);
}

function jobproAdminJob(int $id): ?array
{
    return jobproAdminRows('SELECT j.*,c.company_name,c.company_id,u.account_status AS employer_status,(SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) AS applicant_count FROM jobs j LEFT JOIN company c ON c.company_id=j.company_id LEFT JOIN user u ON u.user_id=c.user_id WHERE j.id=?','i',[$id])[0]??null;
}

function jobproAdminApplications(string $query,string $status,string $sort,int $page): array
{
    $search='%'.addcslashes(mb_substr($query,0,100),'\\%_').'%';$where='(u.username LIKE ? OR COALESCE(p.full_name,\'\') LIKE ? OR j.job_title LIKE ? OR COALESCE(c.company_name,\'\') LIKE ?)';$types='ssss';$values=[$search,$search,$search,$search];
    if(isset(jobproApplicationStatuses()[$status])){$where.=' AND a.status=?';$values[]=$status;$types.='s';}
    return jobproAdminPage('a.id,a.status,a.applied_at,j.job_title,c.company_name,COALESCE(NULLIF(p.full_name,\'\'),u.username) AS candidate_name','FROM applications a JOIN jobs j ON j.id=a.job_id JOIN user u ON u.user_id=a.user_id LEFT JOIN person p ON p.user_id=u.user_id LEFT JOIN company c ON c.company_id=j.company_id',$where,$types,$values,$sort==='oldest'?'a.applied_at ASC,a.id ASC':'a.applied_at DESC,a.id DESC',$page);
}

function jobproAdminApplication(int $id): ?array
{
    return jobproAdminRows('SELECT a.id,a.status,a.applied_at,a.status_updated_at,a.cover_letter,j.job_title,c.company_name,u.user_id,u.email,COALESCE(NULLIF(p.full_name,\'\'),u.username) AS candidate_name,p.qualification,p.location,p.country,p.cv_link,p.portfolio_link FROM applications a JOIN jobs j ON j.id=a.job_id JOIN user u ON u.user_id=a.user_id LEFT JOIN person p ON p.user_id=u.user_id LEFT JOIN company c ON c.company_id=j.company_id WHERE a.id=?','i',[$id])[0]??null;
}

function jobproAdminAuditList(int $actorId,string $role,string $query,string $sort,int $page): array
{
    $search='%'.addcslashes(mb_substr($query,0,100),'\\%_').'%';$where='(l.action LIKE ? OR l.target_type LIKE ? OR l.reason LIKE ?)';$values=[$search,$search,$search];$types='sss';
    if($role!=='owner'){$where.=' AND l.actor_user_id=?';$values[]=$actorId;$types.='i';}
    return jobproAdminPage('l.id,l.action,l.target_type,l.target_id,l.reason,l.before_state,l.after_state,l.created_at,u.username AS actor_name','FROM admin_audit_log l LEFT JOIN user u ON u.user_id=l.actor_user_id',$where,$types,$values,$sort==='oldest'?'l.created_at ASC,l.id ASC':'l.created_at DESC,l.id DESC',$page);
}

function jobproAdminOverview(): array
{
    return array_map('intval',jobproAdminRows("SELECT (SELECT COUNT(*) FROM user) AS users,(SELECT COUNT(*) FROM company) AS companies,(SELECT COUNT(*) FROM applications) AS applications,(SELECT COUNT(*) FROM user WHERE account_status='suspended') AS suspended,(SELECT COUNT(*) FROM jobs WHERE moderation_status='hidden') AS hidden,(SELECT COUNT(*) FROM jobs j WHERE j.publication_status='open' AND ".jobproPublicJobCondition('j').") AS live_jobs")[0]);
}

function jobproAdminActivity(): array
{
    $end=jobproAdminRows('SELECT CURRENT_DATE AS today')[0]['today'];
    $days=[];for($i=6;$i>=0;$i--){$date=(new DateTimeImmutable($end))->modify('-'.$i.' days')->format('Y-m-d');$days[$date]=['jobs'=>0,'applications'=>0];}
    foreach(['jobs'=>'created_at','applications'=>'applied_at']as$table=>$column){
        foreach(jobproAdminRows('SELECT DATE('.$column.') AS day,COUNT(*) AS total FROM '.$table.' WHERE '.$column.'>=CURRENT_DATE-INTERVAL 6 DAY AND '.$column.'<CURRENT_DATE+INTERVAL 1 DAY GROUP BY DATE('.$column.')')as$row)if(isset($days[$row['day']]))$days[$row['day']][$table]=(int)$row['total'];
    }
    return $days;
}
