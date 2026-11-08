<?php
require_once jobproPath('src/views/employer-ui.php');
$employerBufferLevel=ob_get_level();
ob_start(); // Keep HTTP status mutable until the server-rendered page is complete.
jobproStartSession();$employerUser=jobproRequireUser();
header('Cache-Control: private, no-store');header('X-Robots-Tag: noindex, nofollow');
$employerAllowed=$employerUser['account_type']==='company';
$employerCompanyId=(int)($employerUser['company_id']??0);
$employerCompany=null;$employerReady=false;
try {
    if($employerAllowed&&$employerCompanyId)$employerCompany=jobproEmployerCompany($employerCompanyId);
    $employerReady=$employerAllowed&&$employerCompany&&jobproJobLifecycleReady()&&jobproApplicationTrackingReady();
}catch(Throwable$error){error_log('JobPro employer workspace unavailable.');}
$pageTitle=$employerTitle.' — JobPro';
if(!$employerAllowed)http_response_code(403);
include jobproPath('src/views/header.php');
if(!$employerAllowed){?><div class="jp-container jp-employer-notice"><h1>Employer workspace</h1><p>Sign in with a company account to manage jobs and review applicants.</p><a class="jp-button" href="/pages/dashboard.php">Return to your candidate workspace</a></div><?php include jobproPath('src/views/footer.php');exit;}
$employerName=trim((string)($employerCompany['company_name']??''))?:$employerUser['username'];
?>
<div class="jp-container jp-employer-layout">
<aside class="jp-employer-sidebar" aria-label="Employer workspace">
    <a class="jp-employer-identity" href="/pages/company-profile.php"><span aria-hidden="true"><?= jobproEscape(mb_strtoupper(mb_substr($employerName,0,2))) ?></span><div><strong><?= jobproEscape($employerName) ?></strong><small>Employer workspace</small></div></a>
    <nav class="jp-employer-nav" aria-label="Employer navigation"><?php foreach(['overview'=>['Overview','employer.php'],'jobs'=>['Your jobs','jobs.php'],'applicants'=>['Applicants','applicants.php'],'company'=>['Company profile','company-profile.php']]as$key=>$nav): ?><a href="/pages/<?= $nav[1] ?>"<?= $employerActive===$key?' aria-current="page"':'' ?>><span><?= $nav[0] ?></span><span aria-hidden="true">↗</span></a><?php endforeach; ?></nav>
    <div class="jp-employer-sidebar-note"><p class="jp-eyebrow">BUILD YOUR NEXT TEAM</p><p>A clear opportunity starts a better conversation.</p><a class="jp-button" href="/pages/createJob.php">Post a job <span aria-hidden="true">+</span></a></div>
    <a class="jp-employer-settings" href="/pages/account.php?section=security">Account &amp; security</a>
</aside>
<section class="jp-employer-content" aria-labelledby="employer-title">
    <header class="jp-employer-heading"><div><p class="jp-eyebrow">YOUR TEAM’S NEXT CHAPTER</p><h1 id="employer-title"><?= jobproEscape($employerHeading??$employerTitle) ?></h1><p><?= jobproEscape($employerLead??'Give every opportunity and application your attention.') ?></p></div><?php if($employerActive!=='post'): ?><a class="jp-button" href="/pages/createJob.php">Post a job <span aria-hidden="true">+</span></a><?php endif; ?></header>
    <?php jobproRenderFlash(jobproTakeFlash()); ?>
    <?php if(!$employerReady): ?><div class="jp-alert jp-alert--error" role="alert"><h2><?= !$employerCompany?'Complete your company profile.':'Employer setup needs attention.' ?></h2><p><?= !$employerCompany?'Add your company details in account settings before posting jobs.':'Apply database/migrations/005_employer.sql to the application database, then reload. Existing jobs and applications are preserved.' ?></p><a class="jp-text-link" href="/pages/account.php?section=profile">Open account profile →</a></div><?php include jobproPath('src/views/employer-end.php');exit; endif; ?>
