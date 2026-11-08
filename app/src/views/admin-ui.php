<?php
require_once jobproPath('src/views/auth-ui.php');
require_once jobproPath('src/Services/administration.php');

function jobproAdminDate(?string $date,bool $time=false): string
{
    $value=$date?strtotime($date):false;return $value?date($time?'M j, Y · H:i':'M j, Y',$value):'Not recorded';
}

function jobproAdminBadge(string $status): void
{
    $labels=['active'=>'Active','suspended'=>'Suspended','visible'=>'Visible','hidden'=>'Hidden','owner'=>'Owner','moderator'=>'Moderator','none'=>'No admin access','personal'=>'Job seeker','company'=>'Employer','open'=>'Open','closed'=>'Closed','draft'=>'Draft']+jobproApplicationStatuses();
    ?><span class="jp-admin-badge" data-status="<?= jobproEscape($status) ?>"><?= jobproEscape($labels[$status]??$status) ?></span><?php
}

function jobproAdminEmpty(string $title,string $copy,string $href='/pages/admin.php',string $label='Back to overview'): void
{
    ?><div class="jp-admin-empty"><span aria-hidden="true">↗</span><h2><?= jobproEscape($title) ?></h2><p><?= jobproEscape($copy) ?></p><a class="jp-button jp-button--outline" href="<?= jobproEscape($href) ?>"><?= jobproEscape($label) ?></a></div><?php
}

function jobproAdminFilters(): array
{
    $text=static fn($key,$default='')=>is_string($_GET[$key]??null)?mb_substr(trim($_GET[$key]),0,100):$default;
    return ['q'=>$text('q'),'status'=>$text('status'),'role'=>$text('role'),'visibility'=>$text('visibility'),'employer'=>$text('employer'),'sort'=>$text('sort','newest'),'page'=>filter_var($_GET['page']??1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>100000]])?:1,'company'=>jobproId($_GET['company']??null)??0];
}

function jobproAdminPager(array $list,string $path,array $filters): void
{
    if($list['pages']<2)return;
    ?><nav class="jp-admin-pagination" aria-label="Results pages"><?php for($p=1;$p<=$list['pages'];$p++){
        if($p!==1&&$p!==$list['pages']&&abs($p-$list['page'])>1){if(abs($p-$list['page'])===2)echo '<span aria-hidden="true">…</span>';continue;}
        ?><a href="<?= jobproEscape($path.'?'.http_build_query(array_replace($filters,['page'=>$p]))) ?>" aria-label="Page <?= $p ?>"<?= $p===$list['page']?' aria-current="page"':'' ?>><?= $p ?></a><?php
    } ?></nav><?php
}

function jobproAdminSelect(string $id,string $label,string $name,string $selected,array $options): void
{
    ?><div><label for="<?= jobproEscape($id) ?>"><?= jobproEscape($label) ?></label><select class="jp-input" id="<?= jobproEscape($id) ?>" name="<?= jobproEscape($name) ?>"><?php foreach($options as$value=>$text): ?><option value="<?= jobproEscape($value) ?>"<?= $value===$selected?' selected':'' ?>><?= jobproEscape($text) ?></option><?php endforeach; ?></select></div><?php
}

function jobproAdminSearch(string $path,array $filters,string $label,array $selects=[]): void
{
    ?><form class="jp-admin-toolbar" action="<?= jobproEscape($path) ?>" method="get" role="search" aria-label="<?= jobproEscape($label) ?>"><div class="jp-admin-search-field"><label for="admin-query"><?= jobproEscape($label) ?></label><input class="jp-input" id="admin-query" name="q" type="search" maxlength="100" value="<?= jobproEscape($filters['q']) ?>" placeholder="Search records"></div><?php foreach($selects as$name=>$config)jobproAdminSelect('filter-'.$name,$config[0],$name,(string)($filters[$name]??''),$config[1]);jobproAdminSelect('filter-sort','Sort by','sort',$filters['sort'],['newest'=>'Newest first','oldest'=>'Oldest first']);if(($filters['company']??0)>0): ?><input type="hidden" name="company" value="<?= (int)$filters['company'] ?>"><?php endif; ?><button class="jp-button jp-button--outline" type="submit">Apply filters</button></form><?php
}

function jobproAdminReasonField(string $id='admin-reason'): void
{
    ?><div class="jp-auth-field"><label for="<?= jobproEscape($id) ?>">Reason for this change</label><textarea class="jp-input" id="<?= jobproEscape($id) ?>" name="reason" rows="3" minlength="10" maxlength="500" required aria-describedby="<?= jobproEscape($id) ?>-help"></textarea><p class="jp-admin-muted" id="<?= jobproEscape($id) ?>-help">Use 10–500 characters. This reason is recorded in the audit log. Do not include passwords, tokens, or sensitive documents.</p></div><?php
}
