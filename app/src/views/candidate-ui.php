<?php
require_once jobproPath('src/views/auth-ui.php');
require_once jobproPath('src/Services/candidate.php');

function jobproCandidateDate(?string $value): string
{
    $time=$value?strtotime($value):false;
    return $time?date('M j, Y',$time):'Date not available';
}

function jobproCandidateSalary(array $job): string
{
    if ((float)$job['min_salary']===0.0 && (float)$job['max_salary']===0.0) return 'Salary not specified';
    $format=static fn($value)=>rtrim(rtrim(number_format((float)$value,2,'.',','),'0'),'.');
    return $format($job['min_salary']).'–'.$format($job['max_salary']);
}

function jobproCandidateEmpty(string $title, string $copy, string $link='/pages/seekJob.php', string $label='Explore opportunities'): void
{
    ?><div class="jp-candidate-empty"><span aria-hidden="true">↗</span><h2><?= jobproEscape($title) ?></h2><p><?= jobproEscape($copy) ?></p><a class="jp-button" href="<?= jobproEscape($link) ?>"><?= jobproEscape($label) ?></a></div><?php
}

function jobproCandidatePager(array $list, string $path, string $query, string $sort, string $pageKey='page'): void
{
    if ($list['pages']<2) return;
    ?><nav class="jp-candidate-pagination" aria-label="Results pages"><?php
    for ($page=1;$page<=$list['pages'];$page++) {
        if ($page!==1 && $page!==$list['pages'] && abs($page-$list['page'])>1) {
            if (abs($page-$list['page'])===2) echo '<span aria-hidden="true">…</span>';
            continue;
        }
        $url=$path.'?'.http_build_query(['q'=>$query,'sort'=>$sort,$pageKey=>$page]);
        ?><a href="<?= jobproEscape($url) ?>" aria-label="Page <?= $page ?>"<?= $page===$list['page']?' aria-current="page"':'' ?>><?= $page ?></a><?php
    }
    ?></nav><?php
}

function jobproCandidateJobCard(array $job, bool $saved=false, string $returnTo='/pages/saved-jobs.php'): void
{
    $id=(int)($job['job_id']??$job['id']);
    ?><article class="jp-candidate-job"><div class="jp-candidate-job-top"><span class="jp-candidate-company-mark" aria-hidden="true"><?= jobproEscape(mb_strtoupper(mb_substr($job['company_name']?:'JP',0,2))) ?></span><div><p><?= jobproEscape($job['company_name']?:'Employer') ?></p><h3><a href="/pages/job.php?id=<?= $id ?>"><?= jobproEscape($job['job_title']) ?></a></h3></div></div><div class="jp-candidate-meta"><span><?= jobproEscape($job['job_location']) ?></span><span><?= jobproEscape($job['job_type']) ?></span><span><?= jobproEscape($job['job_category']) ?></span></div><p class="jp-candidate-pay"><?= jobproEscape(jobproCandidateSalary($job)) ?><small>Currency / pay period not provided</small></p><div class="jp-candidate-job-bottom"><?php if(($job["publication_status"]??"open")==="closed"): ?><span class="jp-candidate-status">Closed to new applications</span><?php else: ?><a class="jp-text-link" href="/pages/apply.php?id=<?= $id ?>">Review &amp; apply <span aria-hidden="true">→</span></a><?php endif; ?><?php if($saved): ?><form action="/api/candidate.php" method="post"><?= jobproCsrfField() ?><input type="hidden" name="action" value="unsave"><input type="hidden" name="return_to" value="<?= jobproEscape($returnTo) ?>"><input type="hidden" name="job_id" value="<?= $id ?>"><button class="jp-candidate-link-button" type="submit" aria-label="Remove <?= jobproEscape($job['job_title']) ?> from saved jobs">Remove saved job</button></form><?php endif; ?></div><?php if($saved): ?><p class="jp-candidate-date">Saved <?= jobproEscape(jobproCandidateDate($job['saved_at'])) ?></p><?php endif; ?></article><?php
}
