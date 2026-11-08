<?php
require_once jobproPath('src/views/auth-ui.php');
require_once jobproPath('src/Services/employer.php');

function jobproEmployerDate(?string $date): string
{
    $time=$date?strtotime($date):false;
    return $time?date('M j, Y',$time):'Not recorded';
}

function jobproEmployerBadge(string $status): void
{
    $label=['open'=>'Open','draft'=>'Draft','closed'=>'Closed'][$status]??jobproApplicationStatusLabel($status);
    ?><span class="jp-employer-badge" data-status="<?= jobproEscape($status) ?>"><?= jobproEscape($label) ?></span><?php
}

function jobproEmployerEmpty(string $title,string $copy,string $href='/pages/createJob.php',string $label='Post a job'): void
{
    ?><div class="jp-employer-empty"><span aria-hidden="true">↗</span><h2><?= jobproEscape($title) ?></h2><p><?= jobproEscape($copy) ?></p><a class="jp-button" href="<?= jobproEscape($href) ?>"><?= jobproEscape($label) ?></a></div><?php
}

function jobproEmployerPager(array $list,string $path,array $filters): void
{
    if($list['pages']<2)return;
    ?><nav class="jp-employer-pagination" aria-label="Results pages"><?php
    for($page=1;$page<=$list['pages'];$page++){
        if($page!==1&&$page!==$list['pages']&&abs($page-$list['page'])>1){if(abs($page-$list['page'])===2)echo '<span aria-hidden="true">…</span>';continue;}
        ?><a href="<?= jobproEscape($path.'?'.http_build_query(array_replace($filters,['page'=>$page]))) ?>" aria-label="Page <?= $page ?>"<?= $page===$list['page']?' aria-current="page"':'' ?>><?= $page ?></a><?php
    }
    ?></nav><?php
}

function jobproEmployerFilters(): array
{
    $query=is_string($_GET['q']??null)?mb_substr(trim($_GET['q']),0,100):'';
    $status=is_string($_GET['status']??null)?$_GET['status']:'';
    $sort=is_string($_GET['sort']??null)?$_GET['sort']:'newest';
    $page=filter_var($_GET['page']??1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>100000]])?:1;
    $job=jobproId($_GET['job']??null)??0;
    return compact('query','status','sort','page','job');
}

function jobproEmployerTextarea(string $name,string $label,array $state,int $limit,int $rows=6): void
{
    $error=$state['errors'][$name]??null;
    ?><div class="jp-auth-field"><label for="<?= $name ?>"><?= jobproEscape($label) ?></label><textarea class="jp-input" id="<?= $name ?>" name="<?= $name ?>" rows="<?= $rows ?>" maxlength="<?= $limit ?>"<?= $error?' aria-invalid="true" aria-describedby="'.$name.'-error"':'' ?><?= $name==='job_description'?' required':'' ?>><?= jobproEscape($state['old'][$name]??'') ?></textarea><?php if($error): ?><p class="jp-auth-error" id="<?= $name ?>-error"><?= jobproEscape($error) ?></p><?php endif; ?></div><?php
}
