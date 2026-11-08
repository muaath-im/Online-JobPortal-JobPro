<?php
require_once dirname(__DIR__) . '/app/src/config.php';
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require jobproPath('src/Core/auth.php');
$directory=jobproAuthStorage();$removed=0;
foreach(glob($directory.DIRECTORY_SEPARATOR.'*.json')?:[]as$file){
    // Reset/mail records are immutable once expired. Live rate counters can be
    // renewed by another request, so this maintenance command leaves them alone.
    if(!preg_match('/^(reset|mail)-[a-f0-9]{64}\.json$/D',basename($file))||is_link($file))continue;
    $handle=fopen($file,'r+');if(!$handle||!flock($handle,LOCK_EX|LOCK_NB)){if($handle)fclose($handle);continue;}
    $state=json_decode(stream_get_contents($handle),true)?:[];
    $expiry=$state['expires']??$state['until']??(isset($state['created'])?$state['created']+1800:filemtime($file)+1800);
    if($expiry<time()-60){flock($handle,LOCK_UN);fclose($handle);if(unlink($file))$removed++;}else{flock($handle,LOCK_UN);fclose($handle);}
}
echo $removed." expired authentication-state files removed. Active links and counters were retained.\n";
