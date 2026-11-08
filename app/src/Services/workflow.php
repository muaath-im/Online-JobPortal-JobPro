<?php
require_once jobproPath('src/Core/auth.php');

function jobproJobLifecycleReady(): bool
{
    static $ready;
    if ($ready === null) {
        $columns = array_column(jobproDatabase()->query('SHOW COLUMNS FROM jobs')->fetch_all(MYSQLI_ASSOC), 'Field');
        $ready = in_array('publication_status', $columns, true) && in_array('updated_at', $columns, true);
    }
    return $ready;
}

function jobproApplicationTrackingReady(): bool
{
    static $ready;
    if ($ready === null) {
        $columns = array_column(jobproDatabase()->query('SHOW COLUMNS FROM applications')->fetch_all(MYSQLI_ASSOC), 'Field');
        $ready = in_array('status', $columns, true) && in_array('status_updated_at', $columns, true);
    }
    return $ready;
}

function jobproApplicationStatuses(): array
{
    return ['submitted'=>'Submitted','reviewing'=>'In review','shortlisted'=>'Shortlisted','interview'=>'Interview','offered'=>'Offer made','hired'=>'Hired','rejected'=>'Not selected'];
}

function jobproApplicationStatusLabel(string $status): string
{
    return jobproApplicationStatuses()[$status] ?? 'Submitted';
}

function jobproJobModerationReady(): bool
{
    static $ready;
    if($ready===null){
        $stmt=jobproDatabase()->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND ((TABLE_NAME='jobs' AND COLUMN_NAME='moderation_status') OR (TABLE_NAME='user' AND COLUMN_NAME='account_status'))");
        $stmt->execute();$ready=(int)$stmt->get_result()->fetch_row()[0]===2;$stmt->close();
    }
    return $ready;
}

function jobproPublicJobCondition(string $alias='j'): string
{
    if(!preg_match('/^[a-z][a-z0-9_]*$/D',$alias))throw new InvalidArgumentException('Invalid internal table alias.');
    if(!jobproJobModerationReady())return '1=1';
    return "$alias.moderation_status='visible' AND EXISTS (SELECT 1 FROM company visible_company JOIN user visible_employer ON visible_employer.user_id=visible_company.user_id WHERE visible_company.company_id=$alias.company_id AND visible_employer.account_status='active')";
}
