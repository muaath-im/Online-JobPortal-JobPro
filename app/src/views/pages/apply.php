<?php
require_once jobproPath('src/views/candidate-ui.php');
jobproStartSession();
$jobId=jobproId($_GET['id']??null);
if (empty($_SESSION['user_id']) && $jobId) jobproRedirect('/pages/login.php?next='.rawurlencode('/pages/job.php?id='.$jobId));
$job=$jobId?jobproCandidateJob($jobId):null;
if (!$job) http_response_code(404);
$candidateTitle='Review your application'; $candidateActive='applications'; $candidateHeading='A considered next step.';
$candidateLead='Review the opportunity and your introduction before you submit.';
include jobproPath('src/views/candidate-start.php');
try {
    if (!$job) { jobproCandidateEmpty('This opportunity is not available.','It may have been removed, or the link may be incorrect.'); }
    else {
        $existing=jobproCandidateApplication((int)$candidateUser['user_id'],$jobId);
        if ($existing) { ?>
        <section class="jp-candidate-panel"><span class="jp-candidate-status"><?= jobproEscape(jobproApplicationStatusLabel($existing["status"]??"submitted")) ?></span><h2>You have already applied for this role.</h2><p><?= jobproEscape($job['job_title']) ?> at <?= jobproEscape($job['company_name']) ?></p><p>Submitted <?= jobproEscape(jobproCandidateDate($existing['applied_at'])) ?>. You can review your cover letter in your applications.</p><a class="jp-button" href="/pages/applications.php#application-<?= (int)$existing['id'] ?>">View your application</a></section>
        <?php } elseif (($job["publication_status"]??"open")!=="open") { jobproCandidateEmpty("This opportunity is closed.","The employer is no longer accepting new applications. Existing applications are preserved."); } else {
            $old=$_SESSION['candidate_letter']??[]; unset($_SESSION['candidate_letter']);
            $letter=($old['job_id']??null)===$jobId?(string)($old['letter']??''):'';
        ?>
        <div class="jp-candidate-apply-grid"><section class="jp-candidate-panel"><p class="jp-eyebrow">THE OPPORTUNITY</p><h2><?= jobproEscape($job['job_title']) ?></h2><p><?= jobproEscape($job['company_name']) ?></p><div class="jp-candidate-meta"><span><?= jobproEscape($job['job_location']) ?></span><span><?= jobproEscape($job['job_type']) ?></span></div><p class="jp-candidate-pay"><?= jobproEscape(jobproCandidateSalary($job)) ?><small>Currency / pay period not provided</small></p><a class="jp-text-link" href="/pages/job.php?id=<?= $jobId ?>">Read the full role →</a><hr><h3>Your introduction</h3><p><?= jobproEscape($candidateName) ?></p><p class="jp-candidate-note"><?= jobproEscape($candidateProfile['qualification']?:'You can add qualifications to your profile before applying.') ?></p><a class="jp-text-link" href="/pages/account.php?section=profile">Review profile details →</a></section>
        <form class="jp-candidate-panel" action="/api/candidate.php" method="post" data-candidate-form><?= jobproCsrfField() ?><input type="hidden" name="action" value="apply"><input type="hidden" name="job_id" value="<?= $jobId ?>"><h2>Make your introduction</h2><label class="jp-candidate-label" for="application-letter">Cover letter <span>(optional)</span></label><p class="jp-candidate-note" id="letter-help">Explain what interests you about this role and the experience you would bring. Do not include passwords or sensitive identity documents.</p><textarea class="jp-input jp-candidate-textarea" id="application-letter" name="cover_letter" rows="10" maxlength="10000" aria-describedby="letter-help letter-count" data-letter-count="letter-count"><?= jobproEscape($letter) ?></textarea><p class="jp-candidate-counter" id="letter-count"><?= mb_strlen($letter) ?> / 10,000 characters</p><div class="jp-candidate-review"><h3>Before you submit</h3><ul><li>Check the role and employer information.</li><li>Your cover letter can be edited after submission.</li><li>Your account profile remains editable separately.</li></ul></div><label class="jp-candidate-check"><input type="checkbox" required name="reviewed" value="yes"> I have reviewed this opportunity and my application.</label><div class="jp-candidate-form-actions"><button class="jp-button" type="submit" data-pending-label="Submitting application…">Submit application <span aria-hidden="true">→</span></button><a class="jp-text-link" href="/pages/job.php?id=<?= $jobId ?>">Go back</a></div><p class="jp-candidate-note">Submitting records your application in JobPro. It does not confirm that an employer has reviewed it or send an email notification.</p></form></div>
        <?php }
    }
} catch (Throwable $error) { error_log('JobPro application review unavailable.'); jobproCandidateEmpty('We could not open this opportunity.','Please try again in a moment.'); }
include jobproPath('src/views/candidate-end.php');
