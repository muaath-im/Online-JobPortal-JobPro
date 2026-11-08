<?php
require_once jobproPath('src/Core/auth.php');
require_once jobproPath('src/Services/workflow.php');

function jobproCandidateProfile(int $userId): array
{
    $stmt = jobproDatabase()->prepare('SELECT full_name,qualification,year_of_experience,location,city,country,portfolio_link,cv_link FROM person WHERE user_id=? LIMIT 1');
    $stmt->bind_param('i', $userId); $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc() ?: []; $stmt->close();
    return array_replace(['full_name'=>'','qualification'=>'','year_of_experience'=>null,'location'=>'','city'=>'','country'=>'','portfolio_link'=>'','cv_link'=>''],$profile);
}

function jobproCandidateProgress(array $profile): array
{
    $fields = ['full_name'=>'Your name','qualification'=>'Qualifications','location'=>'Location','country'=>'Country','portfolio_link'=>'Portfolio link','cv_link'=>'Resume link'];
    $complete = []; $missing = [];
    foreach ($fields as $field => $label) {
        $value=trim((string)($profile[$field]??''));
        $isValidResume = $field === 'cv_link' && jobproResumePath($value) !== null;
        if ($value!=='' && (!in_array($field,['portfolio_link','cv_link'],true) || jobproUrlError($value)===null || $isValidResume)) $complete[] = $label;
        else $missing[$field] = $label;
    }
    return ['complete'=>count($complete),'total'=>count($fields),'percent'=>(int)round(count($complete)/count($fields)*100),'missing'=>$missing];
}

function jobproCandidateJob(int $id): ?array
{
    $visibility=jobproJobLifecycleReady()?" AND j.publication_status<>'draft'":'';
    $visibility.=' AND '.jobproPublicJobCondition('j');
    $stmt = jobproDatabase()->prepare('SELECT j.*,COALESCE(c.company_name,\'Employer\') AS company_name FROM jobs j LEFT JOIN company c ON c.company_id=j.company_id WHERE j.id=?'.$visibility);
    $stmt->bind_param('i', $id); $stmt->execute(); $row = $stmt->get_result()->fetch_assoc(); $stmt->close();
    return $row;
}

function jobproCandidateApplication(int $userId, int $jobId): ?array
{
    $tracking=jobproApplicationTrackingReady()?',status':",'submitted' AS status";
    $stmt = jobproDatabase()->prepare('SELECT id,cover_letter,applied_at'.$tracking.' FROM applications WHERE user_id=? AND job_id=? ORDER BY id LIMIT 1');
    $stmt->bind_param('ii',$userId,$jobId); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close();
    return $row;
}

function jobproCandidateList(int $userId, string $kind, string $query = '', string $sort = 'newest', int $page = 1): array
{
    $saved = $kind === 'saved';
    $table = $saved ? 'saved_jobs' : 'applications';
    $date = $saved ? 's.saved_at' : 's.applied_at';
    $search = '%' . addcslashes(mb_substr($query,0,100), '\\%_') . '%';
    $where = 's.user_id=? AND (j.job_title LIKE ? OR COALESCE(c.company_name,\'\') LIKE ?)';
    if($saved&&jobproJobLifecycleReady())$where.=" AND j.publication_status<>'draft'";
    if($saved)$where.=' AND '.jobproPublicJobCondition('j');
    $from = " FROM $table s JOIN jobs j ON j.id=s.job_id LEFT JOIN company c ON c.company_id=j.company_id WHERE $where";
    $stmt = jobproDatabase()->prepare('SELECT COUNT(*)' . $from);
    $stmt->bind_param('iss',$userId,$search,$search); $stmt->execute(); $total=(int)$stmt->get_result()->fetch_row()[0]; $stmt->close();
    $pages = max(1,(int)ceil($total/8)); $page=max(1,min($pages,$page)); $offset=($page-1)*8;
    $order = $sort === 'oldest' ? "$date ASC,s.job_id ASC" : ($sort === 'title' ? "j.job_title ASC,s.job_id ASC" : "$date DESC,s.job_id DESC");
    $extra = $saved ? 's.saved_at' : 's.id AS application_id,s.applied_at,s.cover_letter';
    $extra.=jobproJobLifecycleReady()?',j.publication_status':",'open' AS publication_status";
    if(!$saved)$extra.=jobproApplicationTrackingReady()?',s.status,s.status_updated_at':",'submitted' AS status,NULL AS status_updated_at";
    $stmt = jobproDatabase()->prepare("SELECT j.id AS job_id,j.job_title,j.job_location,j.job_type,j.job_category,j.min_salary,j.max_salary,c.company_name,$extra" . $from . " ORDER BY $order LIMIT 8 OFFSET ?");
    $stmt->bind_param('issi',$userId,$search,$search,$offset); $stmt->execute(); $rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    return compact('rows','total','pages','page');
}

function jobproCandidateStats(int $userId): array
{
    $visibility=jobproJobLifecycleReady()?" AND j.publication_status<>'draft'":'';
    $visibility.=' AND '.jobproPublicJobCondition('j');
    $stmt = jobproDatabase()->prepare('SELECT (SELECT COUNT(*) FROM applications WHERE user_id=?) AS applications,(SELECT COUNT(*) FROM saved_jobs s JOIN jobs j ON j.id=s.job_id WHERE s.user_id=?'.$visibility.') AS saved');
    $stmt->bind_param('ii',$userId,$userId); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close();
    return ['applications'=>(int)$row['applications'],'saved'=>(int)$row['saved']];
}

function jobproCandidateBrowse(int $page): array
{
    $db=jobproDatabase();$visibility=jobproJobLifecycleReady()?" WHERE j.publication_status='open'":'';
    $visibility.=($visibility===''?' WHERE ':' AND ').jobproPublicJobCondition('j');
    $total=(int)$db->query('SELECT COUNT(*) FROM jobs j'.$visibility)->fetch_row()[0];
    $pages=max(1,(int)ceil($total/8)); $page=max(1,min($pages,$page)); $offset=($page-1)*8;
    $stmt=$db->prepare('SELECT j.id AS job_id,j.job_title,j.job_location,j.job_type,j.job_category,j.min_salary,j.max_salary,c.company_name FROM jobs j LEFT JOIN company c ON c.company_id=j.company_id'.$visibility.' ORDER BY j.created_at DESC,j.id DESC LIMIT 8 OFFSET ?');
    $stmt->bind_param('i',$offset); $stmt->execute(); $rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    return compact('rows','total','pages','page');
}

function jobproCandidateSavedIds(int $userId): array
{
    $stmt = jobproDatabase()->prepare('SELECT job_id FROM saved_jobs WHERE user_id=?');
    $stmt->bind_param('i',$userId); $stmt->execute(); $ids=array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC),'job_id'); $stmt->close();
    return array_map('intval',$ids);
}

function jobproCandidateSave(int $userId, int $jobId, bool $save): void
{
    $db=jobproDatabase();
    if ($save) {
        $job=jobproCandidateJob($jobId);
        if (!$job || ($job['publication_status']??'open')!=='open') throw new OutOfBoundsException('This opportunity is no longer accepting saved jobs.',404);
        $stmt=$db->prepare('INSERT INTO saved_jobs(user_id,job_id) VALUES (?,?) ON DUPLICATE KEY UPDATE job_id=job_id');
    } else $stmt=$db->prepare('DELETE FROM saved_jobs WHERE user_id=? AND job_id=?');
    $stmt->bind_param('ii',$userId,$jobId); $stmt->execute(); $stmt->close();
}

function jobproCandidateLetter(string $letter): ?string
{
    if (!mb_check_encoding($letter,'UTF-8') || str_contains($letter,"\0") || mb_strlen($letter,'UTF-8')>10000) return 'Use a cover letter of up to 10,000 characters.';
    return null;
}

function jobproCandidateReturn(string $value, string $fallback): string
{
    if ($value==='' || strlen($value)>1000 || preg_match('/[\x00-\x1f\x7f]/',$value)) return $fallback;
    $parts=parse_url($value);
    if (!is_array($parts) || isset($parts['scheme']) || isset($parts['host']) || isset($parts['fragment'])
        || !in_array($parts['path']??'',['/pages/dashboard.php','/pages/saved-jobs.php','/pages/applications.php'],true)) return $fallback;
    if ($parts['path']==='/pages/dashboard.php') return $parts['path'];
    parse_str($parts['query']??'',$query);
    $q=is_string($query['q']??null)?mb_substr(trim($query['q']),0,100):'';
    $sort=is_string($query['sort']??null)&&in_array($query['sort'],['newest','oldest','title'],true)?$query['sort']:'newest';
    $page=filter_var($query['page']??1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>100000]])?:1;
    return $parts['path'].'?'.http_build_query(['q'=>$q,'sort'=>$sort,'page'=>$page]);
}

function jobproCandidateApply(int $userId, int $jobId, string $letter): int
{
    if ($error=jobproCandidateLetter($letter)) throw new InvalidArgumentException($error,422);
    $db=jobproDatabase(); $db->begin_transaction();
    try {
        $columns=jobproJobLifecycleReady()?'id,publication_status':'id';
        $stmt=$db->prepare('SELECT '.$columns.' FROM jobs j WHERE j.id=? AND '.jobproPublicJobCondition('j').' FOR UPDATE');
        $stmt->bind_param('i',$jobId); $stmt->execute(); $job=$stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$job || ($job['publication_status']??'open')!=='open') throw new OutOfBoundsException('This opportunity is no longer accepting applications.',404);
        if (jobproCandidateApplication($userId,$jobId)) throw new DomainException('You have already applied for this job.',409);
        $stmt=$db->prepare('INSERT INTO applications(job_id,user_id,cover_letter) VALUES (?,?,?)');
        $storedLetter=$letter!==''?$letter:null;
        $stmt->bind_param('iis',$jobId,$userId,$storedLetter); $stmt->execute(); $id=$db->insert_id; $stmt->close();
        $db->commit(); return $id;
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function jobproCandidateEdit(int $userId, int $applicationId, string $letter): void
{
    if ($error=jobproCandidateLetter($letter)) throw new InvalidArgumentException($error,422);
    $db=jobproDatabase(); $db->begin_transaction();
    try {
        $stmt=$db->prepare('SELECT id FROM applications WHERE id=? AND user_id=? FOR UPDATE');
        $stmt->bind_param('ii',$applicationId,$userId); $stmt->execute(); $exists=$stmt->get_result()->num_rows>0; $stmt->close();
        if (!$exists) throw new OutOfBoundsException('Application not found.',404);
        $stmt=$db->prepare('UPDATE applications SET cover_letter=? WHERE id=? AND user_id=?');
        $stmt->bind_param('sii',$letter,$applicationId,$userId); $stmt->execute(); $stmt->close(); $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}
