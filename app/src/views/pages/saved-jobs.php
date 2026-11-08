<?php
$candidateTitle='Saved opportunities'; $candidateActive='saved'; $candidateHeading='Keep a possibility close.';
$candidateLead='A shortlist for the roles you want to return to. Saved securely in your account across sessions.';
include jobproPath('src/views/candidate-start.php');
$query=is_string($_GET['q']??null)?mb_substr(trim($_GET['q']),0,100):'';
$sort=is_string($_GET['sort']??null)&&in_array($_GET['sort'],['newest','oldest','title'],true)?$_GET['sort']:'newest';
$page=filter_var($_GET['page']??1,FILTER_VALIDATE_INT)?:1;
try {
    $list=jobproCandidateList((int)$candidateUser['user_id'],'saved',$query,$sort,$page);
    $returnTo='/pages/saved-jobs.php?'.http_build_query(['q'=>$query,'sort'=>$sort,'page'=>$list['page']]);
?>
<form class="jp-candidate-toolbar" action="/pages/saved-jobs.php" method="get" role="search" aria-label="Search saved opportunities"><div><label for="saved-query">Search your shortlist</label><input class="jp-input" id="saved-query" name="q" type="search" maxlength="100" value="<?= jobproEscape($query) ?>" placeholder="Role or company"></div><div><label for="saved-sort">Sort by</label><select class="jp-input" id="saved-sort" name="sort"><?php foreach(['newest'=>'Recently saved','oldest'=>'Saved longest ago','title'=>'Job title A–Z'] as $value=>$label): ?><option value="<?= $value ?>"<?= $sort===$value?' selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></div><button class="jp-button jp-button--outline" type="submit">Update results</button></form>
<p class="jp-candidate-results" role="status"><?= $list['total'] ?> saved <?= $list['total']===1?'opportunity':'opportunities' ?><?= $query!==''?' matching “'.jobproEscape($query).'”':'' ?></p>
<?php if(!$list['rows']): jobproCandidateEmpty($query!==''?'No matches in your shortlist.':'Your shortlist starts here.',$query!==''?'Try another role or company name.':'Use Save job on a real opportunity to keep it in your account.',$query!==''?'/pages/saved-jobs.php':'/pages/seekJob.php',$query!==''?'Clear search':'Find opportunities'); else: ?><div class="jp-candidate-card-grid"><?php foreach($list['rows'] as $job) jobproCandidateJobCard($job,true,$returnTo); ?></div><?php jobproCandidatePager($list,'/pages/saved-jobs.php',$query,$sort); endif; ?>
<p class="jp-candidate-note">Saving a role does not submit an application. Opportunities removed by an employer are also removed from your shortlist.</p>
<?php } catch (Throwable $error) { error_log('JobPro saved opportunities unavailable.'); jobproCandidateEmpty('Your shortlist is temporarily unavailable.','Please try again shortly. Your search can continue.'); }
include jobproPath('src/views/candidate-end.php');
