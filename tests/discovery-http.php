<?php
// Read-only HTTP checks against the local Herd site.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$client=curl_init();
curl_setopt_array($client,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_TIMEOUT=>10,CURLOPT_PROXY=>'']);
function discoveryRequest($client, string $path, string $method='GET'): array {
    curl_setopt($client,CURLOPT_URL,'http://jobpro.test'.$path);
    curl_setopt($client,CURLOPT_CUSTOMREQUEST,$method);
    $body=curl_exec($client);
    if($body===false) throw new RuntimeException(curl_error($client));
    return ['status'=>curl_getinfo($client,CURLINFO_HTTP_CODE),'url'=>curl_getinfo($client,CURLINFO_EFFECTIVE_URL),'body'=>$body];
}
function discoveryAssert(bool $passed,string $label): void {
    if(!$passed) throw new RuntimeException('FAIL: '.$label);
    echo 'PASS: '.$label.PHP_EOL;
}
try {
    $list=discoveryRequest($client,'/pages/seekJob.php');
    discoveryAssert($list['status']===200 && str_ends_with($list['url'],'/pages/seekJob.php'),'Listing shell is public');
    discoveryAssert(!str_contains($list['body'],'font-awesome') && !str_contains($list['body'],'/css/style.css'),'Discovery does not load legacy styles or icon CDNs');
    $feed=discoveryRequest($client,'/api/discovery.php');
    $data=json_decode($feed['body'],true,512,JSON_THROW_ON_ERROR);
    discoveryAssert($feed['status']===200 && is_array($data['jobs']),'Public catalog returns JSON');
    foreach($data['jobs'] as $job) {
        discoveryAssert(!array_intersect(['user_id','password','email','phone_number'],array_keys($job)),'Job projection excludes account secrets');
    }
    if($data['jobs']) {
        $id=$data['jobs'][0]['id'];
        $details=discoveryRequest($client,'/api/discovery.php?id='.$id);
        discoveryAssert(count(json_decode($details['body'],true)['jobs'])===1,'Valid ID selects exactly one job');
        $page=discoveryRequest($client,'/pages/job.php?id='.$id);
        discoveryAssert($page['status']===200 && str_contains($page['body'],'data-detail'),'Job detail shell is public');
    }
    discoveryAssert(discoveryRequest($client,'/api/discovery.php?id=2147483647')['status']===404,'Unknown job returns 404');
    discoveryAssert(discoveryRequest($client,'/api/discovery.php?id[]=3')['status']===400,'Array ID is rejected');
    discoveryAssert(discoveryRequest($client,'/api/discovery.php?id=3%20OR%201=1')['status']===400,'Malformed ID is rejected');
    discoveryAssert(discoveryRequest($client,'/api/discovery.php','POST')['status']===405,'Read-only catalog rejects POST');
    discoveryAssert(discoveryRequest($client,'/pages/job.php?id=invalid')['status']===404,'Malformed detail-page ID returns HTTP 404');
    $legacy=discoveryRequest($client,'/pages/applications.php');
    discoveryAssert(str_ends_with($legacy['url'],'/pages/login.php'),'Existing application workflow still requires authentication');
} finally { curl_close($client); }
