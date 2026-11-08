<?php
require_once jobproPath('src/views/candidate-ui.php');
jobproStartSession();
$candidateUser=jobproRequireUser();
header('Cache-Control: no-store'); header('X-Robots-Tag: noindex, nofollow');
$candidatePersonal=$candidateUser['account_type']==='personal';
$candidateProfile=$candidatePersonal?jobproCandidateProfile((int)$candidateUser['user_id']):[];
$candidateName=trim($candidateProfile['full_name']??'')?:$candidateUser['username'];
$candidateProgress=jobproCandidateProgress($candidateProfile);
$pageTitle=$candidateTitle.' — JobPro';
if (!$candidatePersonal && $candidateActive!=='applications') http_response_code(403);
include jobproPath('src/views/header.php');
if (!$candidatePersonal) {
    ?><div class="jp-container jp-candidate-company"><p class="jp-eyebrow">COMPANY WORKSPACE</p><h1>This space is for job seekers.</h1><p>Your company account can publish and manage opportunities. Personal applications and saved jobs belong to personal accounts.</p><a class="jp-button" href="/pages/jobs.php">Manage your posted jobs</a><a class="jp-text-link" href="/pages/seekJob.php">Browse opportunities</a></div><?php
    include jobproPath('src/views/footer.php'); exit;
}
$candidateFlash=jobproTakeFlash();
?>
<div class="jp-container jp-candidate-layout">
    <aside class="jp-candidate-sidebar" aria-label="Candidate workspace">
        <a class="jp-candidate-identity" href="/pages/profile.php"><?php if(jobproAvatarPath($candidateUser['profile_image'])): ?><img src="<?= jobproEscape($candidateUser['profile_image']) ?>" alt="" width="48" height="48"><?php else: ?><span aria-hidden="true"><?= jobproEscape(mb_strtoupper(mb_substr($candidateName,0,1))) ?></span><?php endif; ?><div><strong><?= jobproEscape($candidateName) ?></strong><small>Personal workspace</small></div></a>
        <nav class="jp-candidate-nav" aria-label="Job seeker navigation"><?php foreach(['dashboard'=>['Your overview','dashboard.php'],'applications'=>['Applications','applications.php'],'saved'=>['Saved opportunities','saved-jobs.php'],'profile'=>['Your profile','profile.php']] as $key=>$item): ?><a href="/pages/<?= $item[1] ?>"<?= $candidateActive===$key?' aria-current="page"':'' ?>><span><?= $item[0] ?></span><span aria-hidden="true">↗</span></a><?php endforeach; ?></nav>
        <div class="jp-candidate-sidebar-note"><p class="jp-eyebrow">ONE STEP AT A TIME</p><p>Find work that fits the direction you want to go.</p><a class="jp-button jp-button--outline" href="/pages/seekJob.php">Find a job</a></div>
        <a class="jp-candidate-settings" href="/pages/account.php?section=security">Account &amp; security</a>
    </aside>
    <section class="jp-candidate-content" aria-labelledby="candidate-title">
        <header class="jp-candidate-heading"><p class="jp-eyebrow"><?= jobproEscape($candidateEyebrow??'YOUR NEXT CHAPTER') ?></p><h1 id="candidate-title"><?= jobproEscape($candidateHeading??$candidateTitle) ?></h1><p><?= jobproEscape($candidateLead??'Keep your next move in view.') ?></p></header>
        <?php jobproRenderFlash($candidateFlash); ?>
