<?php
require_once jobproPath('src/Services/workflow.php');

function jobproEmployerRows(string $sql, string $types = '', array $values = []): array
{
    $stmt = jobproDatabase()->prepare($sql);
    if ($types !== '') $stmt->bind_param($types, ...$values);
    $stmt->execute(); $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    return $rows;
}

function jobproEmployerCompany(int $companyId): ?array
{
    $status=jobproJobModerationReady()?'u.account_status':"'active' AS account_status";
    return jobproEmployerRows('SELECT c.company_id,c.company_name,c.website_link,c.address,u.profile_image,'.$status.' FROM company c LEFT JOIN user u ON u.user_id=c.user_id WHERE c.company_id=?', 'i', [$companyId])[0] ?? null;
}

function jobproEmployerJob(int $companyId, int $jobId, bool $lock = false): ?array
{
    return jobproEmployerRows('SELECT * FROM jobs WHERE company_id=? AND id=?' . ($lock ? ' FOR UPDATE' : ''), 'ii', [$companyId,$jobId])[0] ?? null;
}

function jobproEmployerStats(int $companyId): array
{
    $visible=jobproJobModerationReady()?" AND moderation_status='visible'":'';
    $jobs = jobproEmployerRows("SELECT COUNT(*) AS total,COALESCE(SUM(publication_status='open'$visible),0) AS open,COALESCE(SUM(publication_status='draft'),0) AS draft,COALESCE(SUM(publication_status='closed'),0) AS closed FROM jobs WHERE company_id=?", 'i', [$companyId])[0];
    $apps = jobproEmployerRows("SELECT COUNT(*) AS applications,COALESCE(SUM(a.status='submitted'),0) AS unreviewed,COALESCE(SUM(a.status='shortlisted'),0) AS shortlisted FROM applications a JOIN jobs j ON j.id=a.job_id WHERE j.company_id=?", 'i', [$companyId])[0];
    return array_map('intval', array_merge($jobs,$apps));
}

function jobproEmployerJobs(int $companyId, string $query = '', string $status = '', string $sort = 'newest', int $page = 1): array
{
    $search = '%' . addcslashes(mb_substr($query,0,100), '\\%_') . '%';
    $where = ' WHERE j.company_id=? AND (j.job_title LIKE ? OR j.job_location LIKE ?)';
    $values = [$companyId,$search,$search]; $types = 'iss';
    if (in_array($status,['draft','open','closed'],true)) { $where .= ' AND j.publication_status=?'; $values[]=$status; $types.='s'; }
    $total = (int)jobproEmployerRows('SELECT COUNT(*) AS total FROM jobs j'.$where,$types,$values)[0]['total'];
    $pages=max(1,(int)ceil($total/8)); $page=max(1,min($page,$pages)); $offset=($page-1)*8;
    $order=['oldest'=>'j.created_at ASC,j.id ASC','title'=>'j.job_title ASC,j.id ASC','applicants'=>'applicant_count DESC,j.id DESC'][$sort]??'j.created_at DESC,j.id DESC';
    $rows=jobproEmployerRows('SELECT j.*,(SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) AS applicant_count FROM jobs j'.$where.' ORDER BY '.$order.' LIMIT 8 OFFSET ?',$types.'i',[...$values,$offset]);
    return compact('rows','total','pages','page');
}

function jobproEmployerApplicants(int $companyId, string $query = '', string $status = '', int $jobId = 0, string $sort = 'newest', int $page = 1): array
{
    $search='%'.addcslashes(mb_substr($query,0,100),'\\%_').'%';
    $from=' FROM applications a JOIN jobs j ON j.id=a.job_id JOIN user u ON u.user_id=a.user_id LEFT JOIN person p ON p.user_id=u.user_id';
    $where=' WHERE j.company_id=? AND (COALESCE(p.full_name,u.username) LIKE ? OR j.job_title LIKE ? OR u.email LIKE ?)';
    $values=[$companyId,$search,$search,$search]; $types='isss';
    if (isset(jobproApplicationStatuses()[$status])) { $where.=' AND a.status=?'; $values[]=$status; $types.='s'; }
    if ($jobId>0) { $where.=' AND j.id=?'; $values[]=$jobId; $types.='i'; }
    $total=(int)jobproEmployerRows('SELECT COUNT(*) AS total'.$from.$where,$types,$values)[0]['total'];
    $pages=max(1,(int)ceil($total/8)); $page=max(1,min($page,$pages)); $offset=($page-1)*8;
    $order=['oldest'=>'a.applied_at ASC,a.id ASC','name'=>'COALESCE(p.full_name,u.username) ASC,a.id DESC'][$sort]??'a.applied_at DESC,a.id DESC';
    $rows=jobproEmployerRows('SELECT a.id,a.job_id,a.applied_at,a.status,a.status_updated_at,j.job_title,j.publication_status,COALESCE(NULLIF(p.full_name,\'\'),u.username) AS candidate_name,p.qualification,p.location,p.country'.$from.$where.' ORDER BY '.$order.' LIMIT 8 OFFSET ?',$types.'i',[...$values,$offset]);
    return compact('rows','total','pages','page');
}

function jobproEmployerApplicant(int $companyId, int $applicationId): ?array
{
    // Account credentials and unrelated candidates are never selected.
    return jobproEmployerRows('SELECT a.id,a.job_id,a.cover_letter,a.applied_at,a.status,a.status_updated_at,j.job_title,j.publication_status,u.email,u.phone_number,COALESCE(NULLIF(p.full_name,\'\'),u.username) AS candidate_name,p.qualification,p.year_of_experience,p.location,p.country,p.portfolio_link,p.cv_link FROM applications a JOIN jobs j ON j.id=a.job_id JOIN user u ON u.user_id=a.user_id LEFT JOIN person p ON p.user_id=u.user_id WHERE j.company_id=? AND a.id=?','ii',[$companyId,$applicationId])[0]??null;
}

function jobproEmployerJobValues(array $input): array
{
    $data=[]; $errors=[];
    $limits=['job_title'=>150,'job_category'=>100,'job_location'=>150,'job_type'=>100,'skill_required'=>255,'notice_period'=>100,'joining_facility'=>255,'job_description'=>10000];
    foreach($limits as $field=>$limit) {
        $value=is_string($input[$field]??null)?trim($input[$field]):''; $data[$field]=$value;
        if (!mb_check_encoding($value,'UTF-8') || str_contains($value,"\0") || mb_strlen($value)>$limit) $errors[$field]='Use valid text of up to '.number_format($limit).' characters.';
    }
    foreach(['job_title'=>'Enter a job title.','job_category'=>'Enter a category.','job_location'=>'Enter a location.','job_type'=>'Choose an employment type.','job_description'=>'Describe the role.'] as $field=>$message) if($data[$field]==='')$errors[$field]=$message;
    foreach(['min_salary','max_salary'] as $field) {
        $value=is_string($input[$field]??null)?trim($input[$field]):''; $data[$field]=$value;
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D',$value) || (float)$value>99999999.99) $errors[$field]='Enter a non-negative amount with up to two decimal places.';
    }
    if(!isset($errors['min_salary']) && !isset($errors['max_salary']) && (float)$data['max_salary']<(float)$data['min_salary']) $errors['max_salary']='Maximum salary must be at least the minimum.';
    $data['publication_status']=array_key_exists('publication_status',$input)?(is_string($input['publication_status'])?$input['publication_status']:''):'open';
    if(!in_array($data['publication_status'],['draft','open','closed'],true))$errors['publication_status']='Choose draft, open or closed.';
    return ['data'=>$data,'errors'=>$errors];
}

function jobproEmployerSaveJob(int $companyId, ?int $jobId, array $data): int
{
    $validated=jobproEmployerJobValues($data);
    if($validated['errors'])throw new InvalidArgumentException('Check the highlighted job fields.',422);
    $data=$validated['data']; $db=jobproDatabase(); $db->begin_transaction();
    try {
        if($jobId && !jobproEmployerJob($companyId,$jobId,true))throw new OutOfBoundsException('Job not found.',404);
        if($jobId&&$data['publication_status']==='draft'&&(int)jobproEmployerRows('SELECT COUNT(*) AS total FROM applications WHERE job_id=?','i',[$jobId])[0]['total']>0)throw new DomainException('This job has applications. Close it instead of moving it to drafts.',409);
        $fields=['job_title','job_category','job_location','job_type','min_salary','max_salary','skill_required','notice_period','joining_facility','job_description','publication_status'];
        $values=array_map(static fn($field)=>$data[$field],$fields);
        if($jobId) {
            $sql='UPDATE jobs SET '.implode(',',array_map(static fn($field)=>$field.'=?',$fields)).' WHERE id=? AND company_id=?';
            $types='ssssddsssssii'; $values[]=$jobId; $values[]=$companyId;
        } else {
            $sql='INSERT INTO jobs('.implode(',',$fields).',company_id) VALUES ('.implode(',',array_fill(0,12,'?')).')';
            $types='ssssddsssssi'; $values[]=$companyId;
        }
        $stmt=$db->prepare($sql);$stmt->bind_param($types,...$values);$stmt->execute();$id=$jobId?:$db->insert_id;$stmt->close();$db->commit();return $id;
    }catch(Throwable $error){$db->rollback();throw $error;}
}

function jobproEmployerJobAction(int $companyId,int $jobId,string $action): void
{
    if(!in_array($action,['open','closed','delete'],true))throw new InvalidArgumentException('Choose a valid job action.',422);
    $db=jobproDatabase();$db->begin_transaction();
    try {
        $job=jobproEmployerJob($companyId,$jobId,true);
        if(!$job)throw new OutOfBoundsException('Job not found.',404);
        if($action==='open'){
            $validation=jobproEmployerJobValues(array_replace($job,['publication_status'=>'open']));
            if($validation['errors'])throw new InvalidArgumentException('Complete the job details in the editor before publishing.',422);
        }
        if($action==='delete') {
            $count=jobproEmployerRows('SELECT COUNT(*) AS total FROM applications WHERE job_id=?','i',[$jobId])[0]['total'];
            if($count>0)throw new DomainException('This job has applications. Close it to preserve candidate records.',409);
            $stmt=$db->prepare('DELETE FROM jobs WHERE id=? AND company_id=?');$stmt->bind_param('ii',$jobId,$companyId);
        } else {
            $stmt=$db->prepare('UPDATE jobs SET publication_status=? WHERE id=? AND company_id=?');$stmt->bind_param('sii',$action,$jobId,$companyId);
        }
        $stmt->execute();$stmt->close();$db->commit();
    }catch(Throwable$error){$db->rollback();throw$error;}
}

function jobproEmployerSetStatus(int $companyId,int $applicationId,string $status,string $expected): void
{
    if(!isset(jobproApplicationStatuses()[$status]) || !isset(jobproApplicationStatuses()[$expected]))throw new InvalidArgumentException('Choose a valid application status.',422);
    $db=jobproDatabase();$db->begin_transaction();
    try {
        $row=jobproEmployerRows('SELECT a.id,a.status FROM applications a JOIN jobs j ON j.id=a.job_id WHERE j.company_id=? AND a.id=? FOR UPDATE','ii',[$companyId,$applicationId])[0]??null;
        if(!$row)throw new OutOfBoundsException('Application not found.',404);
        if($row['status']!==$expected)throw new DomainException('This application changed in another tab. Review its latest status and try again.',409);
        if($row['status']!==$status){$stmt=$db->prepare('UPDATE applications SET status=?,status_updated_at=CURRENT_TIMESTAMP WHERE id=?');$stmt->bind_param('si',$status,$applicationId);$stmt->execute();$stmt->close();}
        $db->commit();
    }catch(Throwable$error){$db->rollback();throw$error;}
}
