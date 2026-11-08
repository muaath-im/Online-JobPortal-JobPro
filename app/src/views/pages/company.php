<?php
require_once jobproPath('src/views/employer-ui.php');
jobproStartSession();
$companyId=jobproId($_GET['id']??null);$company=null;$companyJobs=[];$companyError=false;
$page=max(1,filter_var($_GET['page']??1,FILTER_VALIDATE_INT)?:1);$total=0;$pages=1;
try {
    $company=$companyId?jobproEmployerCompany($companyId):null;
    if(($company['account_status']??'active')!=='active')$company=null;
    if($company){
        $where='j.company_id=?'.(jobproJobLifecycleReady()?" AND j.publication_status='open'":'').' AND '.jobproPublicJobCondition('j');
        $total=(int)jobproEmployerRows('SELECT COUNT(*) AS total FROM jobs j WHERE '.$where,'i',[$companyId])[0]['total'];
        $pages=max(1,(int)ceil($total/8));$page=min($pages,$page);$offset=($page-1)*8;
        $companyJobs=jobproEmployerRows('SELECT j.id,j.job_title,j.job_location,j.job_type,j.job_category,j.created_at FROM jobs j WHERE '.$where.' ORDER BY j.created_at DESC,j.id DESC LIMIT 8 OFFSET ?','ii',[$companyId,$offset]);
    }else http_response_code(404);
}catch(Throwable$error){error_log('JobPro public company profile unavailable.');http_response_code(503);$companyError=true;}
$pageTitle=($company['company_name']??'Company profile').' — JobPro';
include jobproPath('src/views/header.php');
?>
<div class="jp-container jp-company-public"><?php if($companyError||!$company): ?><h1>Company profile</h1><?php endif; ?><?php if($companyError): jobproEmployerEmpty('Company profile temporarily unavailable.','Please try again later.','/pages/seekJob.php','Browse jobs');elseif(!$company): jobproEmployerEmpty('Company not found.','The link may be incorrect or the company may no longer have an account.','/pages/seekJob.php','Browse jobs');else: ?>
<nav class="jp-employer-breadcrumb" aria-label="Breadcrumb"><a href="/pages/seekJob.php">All opportunities</a><span aria-hidden="true">/</span><span>Company profile</span></nav>
<header class="jp-company-public-heading"><?php if(jobproAvatarPath($company['profile_image']??null)): ?><img class="jp-employer-company-mark" src="<?= jobproEscape($company['profile_image']) ?>" alt="" width="68" height="68"><?php else: ?><span class="jp-employer-company-mark" aria-hidden="true"><?= jobproEscape(mb_strtoupper(mb_substr($company['company_name']?:'JP',0,2))) ?></span><?php endif; ?><div><p class="jp-eyebrow">MEET THE EMPLOYER</p><h1><?= jobproEscape($company['company_name']?:'Company profile') ?></h1><?php if($company['address']): ?><p><?= jobproEscape($company['address']) ?></p><?php endif; ?><?php if($company['website_link']&&jobproUrlError($company['website_link'])===null): ?><a class="jp-text-link" href="<?= jobproEscape($company['website_link']) ?>" rel="noopener noreferrer" target="_blank">Visit company website ↗</a><?php endif; ?></div></header>
<section aria-labelledby="company-openings"><div class="jp-employer-section-heading"><h2 id="company-openings">Open opportunities <span class="jp-employer-muted">(<?= $total ?>)</span></h2><a class="jp-text-link" href="/pages/seekJob.php">Explore all jobs →</a></div><?php if(!$companyJobs): jobproEmployerEmpty('No open opportunities right now.','Check back when this company publishes another role.','/pages/seekJob.php','Explore other opportunities');else: ?><div class="jp-employer-applicant-grid"><?php foreach($companyJobs as$job): ?><article class="jp-employer-applicant-card"><p class="jp-eyebrow"><?= jobproEscape($job['job_category']) ?></p><h3><a href="/pages/job.php?id=<?= (int)$job['id'] ?>"><?= jobproEscape($job['job_title']) ?></a></h3><p class="jp-employer-muted"><?= jobproEscape($job['job_location'].' · '.$job['job_type']) ?></p><small>Posted <?= jobproEmployerDate($job['created_at']) ?></small><a class="jp-text-link" href="/pages/job.php?id=<?= (int)$job['id'] ?>">View opportunity →</a></article><?php endforeach; ?></div><?php jobproEmployerPager(compact('pages','page'),'/pages/company.php',['id'=>$companyId]);endif; ?></section><p class="jp-employer-muted">Company information is supplied by the employer. JobPro does not independently verify company claims.</p>
<?php endif; ?></div>
<?php include jobproPath('src/views/footer.php'); ?>
