<?php
$employerTitle='Employer overview';$employerHeading='Good teams start here.';$employerActive='overview';
$employerLead='A clear view of your opportunities and the people taking their next step.';
include jobproPath('src/views/employer-start.php');
try {
    $stats=jobproEmployerStats($employerCompanyId);$recentJobs=jobproEmployerJobs($employerCompanyId);$recentApps=jobproEmployerApplicants($employerCompanyId);
?>
<div class="jp-employer-welcome"><div><p class="jp-eyebrow">WELCOME BACK, <?= jobproEscape($employerName) ?></p><h2>Your next great hire<br>starts with an introduction.</h2><p>Publish a thoughtful role, keep applicants informed, and build your team with intention.</p><a class="jp-text-link" href="/pages/applicants.php?status=submitted">Review new applications →</a></div><div class="jp-employer-welcome-art" aria-hidden="true"><span>OPPORTUNITY</span><strong>People.<br>Potential.<br>Possibility.</strong><span>LET’S MAKE THE INTRODUCTION ↗</span></div></div>
<div class="jp-employer-metrics"><?php foreach([
    ['open','Open opportunities','/pages/jobs.php?status=open','Visible to job seekers'],
    ['draft','Drafts','/pages/jobs.php?status=draft','Ready when you are'],
    ['applications','Applications','/pages/applicants.php','Across your opportunities'],
    ['unreviewed','Awaiting review','/pages/applicants.php?status=submitted','Start the conversation']
]as$metric): ?><a href="<?= $metric[2] ?>"><span><?= $metric[1] ?></span><strong><?= $stats[$metric[0]] ?></strong><small><?= $metric[3] ?> <span aria-hidden="true">↗</span></small></a><?php endforeach; ?></div>
<div class="jp-employer-two-col">
<section class="jp-employer-panel"><div class="jp-employer-section-heading"><h2>Recent opportunities</h2><a class="jp-text-link" href="/pages/jobs.php">All jobs →</a></div>
<?php if(!$recentJobs['rows']): jobproEmployerEmpty('Make your first introduction.','Describe the work, the skills, and what someone could achieve with your team.');else: ?><ul class="jp-employer-activity"><?php foreach(array_slice($recentJobs['rows'],0,4)as$job): ?><li><div><a href="/pages/createJob.php?id=<?= (int)$job['id'] ?>"><?= jobproEscape($job['job_title']) ?></a><p><?= (int)$job['applicant_count'] ?> <?= (int)$job['applicant_count']===1?'application':'applications' ?> · <?= jobproEscape($job['job_location']) ?></p></div><?php jobproEmployerBadge($job['publication_status']); ?></li><?php endforeach; ?></ul><?php endif; ?></section>
<section class="jp-employer-panel"><div class="jp-employer-section-heading"><h2>Latest applications</h2><a class="jp-text-link" href="/pages/applicants.php">View all →</a></div>
<?php if(!$recentApps['rows']): jobproEmployerEmpty('Your next connection is ahead.','Applications to your published opportunities will appear here.','/pages/jobs.php','Manage opportunities');else: ?><ul class="jp-employer-activity"><?php foreach(array_slice($recentApps['rows'],0,4)as$app): ?><li><div><a href="/pages/applicant.php?id=<?= (int)$app['id'] ?>"><?= jobproEscape($app['candidate_name']) ?></a><p><?= jobproEscape($app['job_title']) ?></p><small>Applied <?= jobproEmployerDate($app['applied_at']) ?></small></div><?php jobproEmployerBadge($app['status']); ?></li><?php endforeach; ?></ul><?php endif; ?></section>
</div>
<section class="jp-employer-panel jp-employer-profile-prompt"><div><p class="jp-eyebrow">MAKE A CLEAR INTRODUCTION</p><h2>Let candidates get to know your company.</h2><p>Your name, website, and company address appear on your public company page. Keep them accurate and current.</p></div><a class="jp-button jp-button--outline" href="/pages/company-profile.php">Review company profile →</a></section>
<?php }catch(Throwable$error){error_log('JobPro employer overview failed.');jobproEmployerEmpty('Your overview is temporarily unavailable.','Please reload to try again.','/pages/employer.php','Reload overview');}include jobproPath('src/views/employer-end.php'); ?>
