<?php
$candidateTitle = 'Your applications';
$candidateActive = 'applications';
$candidateHeading = 'Every application, a step forward.';
$candidateLead = 'Revisit your submitted roles, refine your introduction, and keep track of your search.';
include jobproPath('src/views/candidate-start.php');
$query = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 100) : '';
$sort = is_string($_GET['sort'] ?? null) && in_array($_GET['sort'], ['newest', 'oldest', 'title'], true) ? $_GET['sort'] : 'newest';
$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
$old = $_SESSION['candidate_letter'] ?? [];
unset($_SESSION['candidate_letter']);
try {
    $list = jobproCandidateList((int) $candidateUser['user_id'], 'applications', $query, $sort, $page);
    $returnTo = '/pages/applications.php?' . http_build_query(['q' => $query, 'sort' => $sort, 'page' => $list['page']]);
    ?>
    <div class="jp-candidate-panel jp-candidate-tracking-note">
        <div class="jp-candidate-section-heading">
            <h2>Your search, in progress</h2><a class="jp-text-link" href="/pages/seekJob.php">Find another opportunity
                →</a>
        </div>
        <p>Your application starts as <strong>Submitted</strong>. Employers can update its review status here. A status
            update does not send an email or schedule an interview.</p>
    </div>
    <form class="jp-candidate-toolbar" action="/pages/applications.php" method="get" role="search"
        aria-label="Search your applications">
        <div><label for="application-query">Search your applications</label><input class="jp-input" id="application-query"
                name="q" type="search" maxlength="100" value="<?= jobproEscape($query) ?>" placeholder="Role or company">
        </div>
        <div><label for="application-sort">Sort by</label><select class="jp-input" id="application-sort"
                name="sort"><?php foreach (['newest' => 'Most recent', 'oldest' => 'Oldest first', 'title' => 'Job title A–Z'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $sort === $value ? ' selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select></div><button class="jp-button jp-button--outline" type="submit">Update results</button>
    </form>
    <p class="jp-candidate-results" role="status"><?= $list['total'] ?>
        <?= $list['total'] === 1 ? 'application' : 'applications' ?>
        <?= $query !== '' ? ' matching “' . jobproEscape($query) . '”' : '' ?>
    </p>
    <?php if (!$list['rows']):
        jobproCandidateEmpty($query !== '' ? 'No matching applications.' : 'Your next chapter is waiting.', $query !== '' ? 'Try another role or company name.' : 'Read an opportunity, review your introduction, and apply when you are ready.', $query !== '' ? '/pages/applications.php' : '/pages/seekJob.php', $query !== '' ? 'Clear search' : 'Explore opportunities');
    else: ?>
        <div class="jp-candidate-application-list">
            <?php foreach ($list['rows'] as $application):
                $id = (int) $application['application_id'];
                $jobId = (int) $application['job_id'];
                $letter = ($old['application_id'] ?? null) === $id ? (string) ($old['letter'] ?? '') : (string) ($application['cover_letter'] ?? '');
                ?>
                <article class="jp-candidate-application" id="application-<?= $id ?>">
                    <header class="jp-candidate-application-header">
                        <div>
                            <p><?= jobproEscape($application['company_name'] ?: 'Employer') ?></p>
                            <h2><a href="/pages/job.php?id=<?= $jobId ?>"><?= jobproEscape($application['job_title']) ?></a></h2>
                        </div><span
                            class="jp-candidate-status"><?= jobproEscape(jobproApplicationStatusLabel($application['status'] ?? 'submitted')) ?></span>
                    </header>
                    <div class="jp-candidate-meta">
                        <span><?= jobproEscape($application['job_location']) ?></span><span><?= jobproEscape($application['job_type']) ?></span><span>Applied
                            <?= jobproEscape(jobproCandidateDate($application['applied_at'])) ?></span>
                    </div>
                    <?php if (trim((string) $application['cover_letter']) !== ''): ?>
                        <details class="jp-candidate-letter-details">
                            <summary>Read your cover letter</summary>
                            <div class="jp-candidate-letter"><?= jobproEscape($application['cover_letter']) ?></div>
                        </details><?php else: ?>
                        <p class="jp-candidate-note">No cover letter added. You can add one below.</p><?php endif; ?>
                    <div class="jp-candidate-application-actions">
                        <details<?= ($old['application_id'] ?? null) === $id ? ' open' : '' ?>>
                            <summary>Edit cover letter</summary>
                            <form action="/api/candidate.php" method="post" data-candidate-form><?= jobproCsrfField() ?><input
                                    type="hidden" name="action" value="edit"><input type="hidden" name="application_id"
                                    value="<?= $id ?>"><input type="hidden" name="return_to"
                                    value="<?= jobproEscape($returnTo) ?>"><label class="jp-candidate-label"
                                    for="letter-<?= $id ?>">Your cover letter</label><textarea
                                    class="jp-input jp-candidate-textarea" id="letter-<?= $id ?>" name="cover_letter" rows="7"
                                    maxlength="10000" aria-describedby="letter-count-<?= $id ?>"
                                    data-letter-count="letter-count-<?= $id ?>"><?= jobproEscape($letter) ?></textarea>
                                <p class="jp-candidate-counter" id="letter-count-<?= $id ?>"><?= mb_strlen($letter) ?> / 10,000
                                    characters</p><button class="jp-button" type="submit"
                                    data-pending-label="Saving cover letter…">Save cover letter</button>
                            </form>
                            </details>
                            <details class="jp-candidate-withdraw">
                                <summary>Withdraw application</summary>
                                <form action="/api/candidate.php" method="post" data-candidate-form><?= jobproCsrfField() ?><input
                                        type="hidden" name="action" value="withdraw"><input type="hidden" name="application_id"
                                        value="<?= $id ?>"><input type="hidden" name="return_to"
                                        value="<?= jobproEscape($returnTo) ?>">
                                    <p class="jp-candidate-note">This deletes the application and its cover letter from JobPro. You
                                        can apply again later, but the original submission date will not be restored. No employer
                                        email is sent.</p><label class="jp-candidate-check"><input type="checkbox" required
                                            name="confirm" value="yes"> Withdraw and permanently delete this
                                        application.</label><button class="jp-button" type="submit"
                                        data-pending-label="Withdrawing…">Withdraw &amp; delete</button>
                                </form>
                            </details>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php jobproCandidatePager($list, '/pages/applications.php', $query, $sort); endif; ?>
    <noscript>
        <p class="jp-candidate-note">JavaScript is off. You can still <a
                href="/pages/applications.php?browse=1#available-jobs">browse available jobs and apply with standard
                forms</a>.</p>
    </noscript>
    <?php if (isset($_GET['browse'])):
        $browse = jobproCandidateBrowse(filter_var($_GET['browse'], FILTER_VALIDATE_INT) ?: 1); ?>
        <section id="available-jobs">
            <div class="jp-candidate-section-heading">
                <h2>Available opportunities</h2><a class="jp-text-link" href="/pages/seekJob.php">Search &amp; filters →</a>
            </div>
            <p class="jp-candidate-note">This server-rendered list works without JavaScript. Choose a role to review its
                application.</p>
            <div class="jp-candidate-card-grid"><?php foreach ($browse['rows'] as $job)
                jobproCandidateJobCard($job); ?></div>
            <?php if (!$browse['rows'])
                jobproCandidateEmpty('No opportunities yet.', 'Check back after an employer posts a role.');
            jobproCandidatePager($browse, '/pages/applications.php', '', 'newest', 'browse'); ?>
        </section>
    <?php endif; ?>
<?php } catch (Throwable $error) {
    error_log('JobPro applications workspace unavailable.');
    jobproCandidateEmpty('Your applications are temporarily unavailable.', 'Please try again shortly. Your applications have not been changed.');
}
include jobproPath('src/views/candidate-end.php');
