<?php
if(PHP_SAPI!=="cli"){http_response_code(404);exit;}
$base=getenv("JOBPRO_TEST_BASE_URL")?:"";if(!preg_match("~^http://127[.]0[.]0[.]1:[0-9]+$~D",$base))throw new RuntimeException("Use the isolated regression runner.");

ini_set("display_errors","0");
require (getenv("JOBPRO_TEST_APP_CONFIG") ?: dirname(__DIR__)."/app/src/config.php");
require jobproPath("src/Services/employer.php");
$db=jobproDatabase();$clients=[];$users=[];$checks=0;
if(!preg_match("/^(localhost|127[.]0[.]0[.]1|::1)(?:\\s|:)/",$db->host_info))throw new RuntimeException("Use local MySQL only.");
function qa($ok,$message){global$checks;if(!$ok)throw new RuntimeException($message);$checks++;echo "PASS: ".$message.PHP_EOL;}
function hc(){global$clients;$h=curl_init();curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5,CURLOPT_TIMEOUT=>15,CURLOPT_COOKIEFILE=>"",CURLOPT_PROXY=>""]);$clients[]=$h;return$h;}
function req($h,$path,$form=null,$json=false){global $base;curl_setopt($h,CURLOPT_URL,$base.$path);curl_setopt($h,CURLOPT_HTTPHEADER,$json?["Accept: application/json"]:[]);if($form===null)curl_setopt($h,CURLOPT_HTTPGET,true);else{curl_setopt($h,CURLOPT_POST,true);curl_setopt($h,CURLOPT_POSTFIELDS,http_build_query($form));}$body=curl_exec($h);if($body===false)throw new RuntimeException("Herd transport failed.");return["status"=>curl_getinfo($h,CURLINFO_RESPONSE_CODE),"body"=>$body,"url"=>curl_getinfo($h,CURLINFO_EFFECTIVE_URL)];}
function doc($body){$d=new DOMDocument();$prior=libxml_use_internal_errors(true);$d->loadHTML($body,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($prior);return new DOMXPath($d);}
function csrf($body){$nodes=doc($body)->query("//input[@name=\"csrf_token\"]");if(!$nodes->length)throw new RuntimeException("Missing CSRF field.");return$nodes->item(0)->getAttribute("value");}
function cleanPage($r){return$r["status"]===200&&!preg_match("/Fatal error|Warning:|Parse error/",$r["body"]);}
function postAction($who,$action,$values=[],$endpoint="/api/employer.php"){return req($who["client"],$endpoint,array_replace(["csrf_token"=>$who["token"],"action"=>$action],$values),true);}
function fixture($role){global$db,$users;$name="qa5_".bin2hex(random_bytes(6));$email=$name."@example.invalid";$password=bin2hex(random_bytes(20));$hash=jobproHash($password);$s=$db->prepare("INSERT INTO user(username,email,password,account_type)VALUES(?,?,?,?)");$s->bind_param("ssss",$name,$email,$hash,$role);$s->execute();$id=$db->insert_id;$users[]=$id;$display=$role==="company"?"Northstar Studio (QA)":"Alex Morgan (QA)";$table=$role==="company"?"company":"person";$field=$role==="company"?"company_name":"full_name";$s=$db->prepare("INSERT INTO ".$table."(user_id,".$field.")VALUES(?,?)");$s->bind_param("is",$id,$display);$s->execute();$profileId=$db->insert_id;$h=hc();$r=req($h,"/pages/login.php");$r=req($h,"/api/login_api.php",["csrf_token"=>csrf($r["body"]),"username"=>$name,"password"=>$password]);qa(cleanPage($r)&&str_contains($r["url"],"/pages/account.php"),$role." test account can sign in through Herd");return["id"=>$id,"profile_id"=>$profileId,"client"=>$h,"token"=>csrf($r["body"]),"name"=>$name];}
try{
qa(jobproJobLifecycleReady()&&jobproApplicationTrackingReady(),"Both Phase 5 schema changes are available");
$guest=hc();$r=req($guest,"/api/employer.php",["action"=>"save"],true);qa($r["status"]===401,"Anonymous employer mutations are rejected");
$company=fixture("company");$other=fixture("company");$candidate=fixture("personal");$second=fixture("personal");
foreach(["employer","jobs","createJob","applicants","company-profile"]as$page){$r=req($company["client"],"/pages/".$page.".php");qa(cleanPage($r)&&doc($r["body"])->query("//h1")->length===1&&doc($r["body"])->query("//main")->length===1,"Employer ".$page." renders one main landmark and heading");}
$r=req($candidate["client"],"/pages/employer.php");qa($r["status"]===403,"Personal accounts cannot enter employer workspace");
$data=["job_title"=>"QA Phase 5 Frontend Engineer","job_category"=>"Software Development","job_location"=>"Colombo","job_type"=>"Full-time","min_salary"=>"100.25","max_salary"=>"200.50","skill_required"=>"HTML, CSS, JavaScript","notice_period"=>"Flexible","joining_facility"=>"Learning budget","job_description"=>"Build accessible interfaces. <script>never_execute()</script>","publication_status"=>"draft"];
$r=postAction($company,"save",$data);qa($r["status"]===200&&(json_decode($r["body"],true)["success"]??false),"Employer can save a complete draft");
$job=jobproEmployerRows("SELECT * FROM jobs WHERE company_id=? ORDER BY id DESC LIMIT 1","i",[$company["profile_id"]])[0];$jobId=(int)$job["id"];
qa($job["publication_status"]==="draft"&&$job["min_salary"]==="100.25","Draft status and decimal salary persist correctly");
$r=req($guest,"/api/discovery.php?id=".$jobId);qa($r["status"]===404,"Drafts cannot be read through public discovery");
$r=req($candidate["client"],"/pages/apply.php?id=".$jobId);qa($r["status"]===404,"Draft application page is unavailable");
$r=postAction($candidate,"apply",["job_id"=>$jobId,"reviewed"=>"yes"],"/api/candidate.php");qa($r["status"]===404,"Draft applications are rejected by the backend");
$r=req($other["client"],"/api/get_job_details.php?id=".$jobId);qa($r["status"]===404,"Another company cannot read private job details");
$r=req($other["client"],"/pages/createJob.php?id=".$jobId);qa($r["status"]===404,"Another company cannot open a job editor");
$r=postAction($other,"closed",["job_id"=>$jobId]);qa($r["status"]===404,"Another company cannot change job visibility");
$r=postAction($candidate,"open",["job_id"=>$jobId]);qa($r["status"]===403,"Personal accounts cannot publish a job");
$r=req($company["client"],"/api/employer.php",["action"=>"open","job_id"=>$jobId,"csrf_token"=>"invalid"],true);qa($r["status"]===403,"Invalid CSRF tokens block mutations");
$r=postAction($company,"save",array_replace($data,["min_salary"=>"-1"]));qa($r["status"]===422,"Backend rejects negative salary values");
$r=postAction($company,"save",array_replace($data,["job_id"=>"invalid"]));qa($r["status"]===422,"An invalid edit ID cannot create a new job");
$r=req($company["client"],"/api/employer.php",array_replace($data,["csrf_token"=>$company["token"],"action"=>"save","job_title"=>""]));qa(cleanPage($r)&&str_contains($r["url"],"createJob.php")&&str_contains($r["body"],"Enter a job title.")&&str_contains($r["body"],"QA Phase 5")===false,"Native job validation returns an accessible form error");
$r=postAction($company,"open",["job_id"=>$jobId]);qa($r["status"]===200,"Draft can be published");
$r=req($guest,"/api/discovery.php?id=".$jobId);$payload=json_decode($r["body"],true);qa($r["status"]===200&&($payload["jobs"][0]["companyId"]??0)===$company["profile_id"],"Published job exposes its public company link");
$r=postAction($candidate,"save",["job_id"=>$jobId],"/api/candidate.php");qa($r["status"]===200,"Existing candidate bookmark workflow still works");
$r=postAction($candidate,"apply",["job_id"=>$jobId,"reviewed"=>"yes","cover_letter"=>"I enjoy accessible UI. <script>never_execute()</script>"],"/api/candidate.php");qa($r["status"]===200,"A candidate can apply to the open role");
$app=jobproEmployerRows("SELECT id,status FROM applications WHERE job_id=? AND user_id=?","ii",[$jobId,$candidate["id"]])[0];$appId=(int)$app["id"];
qa($app["status"]==="submitted","New applications start as submitted");
$r=postAction($candidate,"apply",["job_id"=>$jobId,"reviewed"=>"yes"],"/api/candidate.php");qa($r["status"]===409,"Duplicate applications remain blocked");
$r=req($company["client"],"/pages/applicants.php?job=".$jobId);qa(cleanPage($r)&&str_contains($r["body"],"Alex Morgan (QA)"),"Employer sees applicants for its own job");
$r=req($company["client"],"/pages/applicant.php?id=".$appId);$xp=doc($r["body"]);qa(cleanPage($r)&&$xp->query("//script[contains(text(),\"never_execute\")]")->length===0&&str_contains($r["body"],"&lt;script&gt;"),"Candidate cover letters are escaped in applicant review");
$r=req($other["client"],"/pages/applicant.php?id=".$appId);qa($r["status"]===404&&!str_contains($r["body"],$candidate["name"]),"Another company cannot view applicant details");
$r=postAction($other,"status",["application_id"=>$appId,"status"=>"hired","expected_status"=>"submitted"]);qa($r["status"]===404,"Another company cannot change candidate status");
$r=postAction($second,"withdraw",["application_id"=>$appId,"confirm"=>"yes"],"/api/candidate.php");qa($r["status"]===404,"A different candidate cannot withdraw this application");
$r=postAction($company,"status",["application_id"=>$appId,"status"=>"reviewing","expected_status"=>"submitted"]);qa($r["status"]===200,"Employer can mark an application in review");
$r=req($candidate["client"],"/pages/applications.php");qa(cleanPage($r)&&str_contains($r["body"],"In review"),"Candidate sees the actual employer review status");
$r=postAction($company,"status",["application_id"=>$appId,"status"=>"rejected","expected_status"=>"submitted"]);qa($r["status"]===409,"A stale status form cannot overwrite another update");
$current="reviewing";foreach(["shortlisted","interview","offered","hired","rejected","submitted"]as$next){$r=postAction($company,"status",["application_id"=>$appId,"status"=>$next,"expected_status"=>$current]);qa($r["status"]===200,"Application state is persisted: ".$next);$current=$next;}
$r=postAction($company,"status",["application_id"=>$appId,"status"=>"unknown","expected_status"=>$current]);qa($r["status"]===422,"Unknown application states are rejected");
$r=postAction($company,"closed",["job_id"=>$jobId]);qa($r["status"]===200,"Employer can close a role");
$r=req($guest,"/api/discovery.php");qa(!in_array($jobId,array_column(json_decode($r["body"],true)["jobs"],"id"),true),"Closed roles leave public search");
$r=req($guest,"/api/discovery.php?id=".$jobId);qa($r["status"]===200&&(json_decode($r["body"],true)["jobs"][0]["publicationStatus"]??"")==="closed","Closed job details remain readable");
$r=req($second["client"],"/pages/apply.php?id=".$jobId);qa(cleanPage($r)&&str_contains($r["body"],"This opportunity is closed.")&&doc($r["body"])->query("//form[@action=\"/api/candidate.php\"]")->length===0,"Closed role has no new-application form");
$r=postAction($second,"apply",["job_id"=>$jobId,"reviewed"=>"yes"],"/api/candidate.php");qa($r["status"]===404,"Closed roles reject new applications");
$r=postAction($company,"delete",["job_id"=>$jobId,"confirm"=>"yes"]);qa($r["status"]===409,"Jobs with applications cannot be deleted");
$r=postAction($company,"save",array_replace($data,["job_id"=>$jobId,"publication_status"=>"draft"]));qa($r["status"]===409,"Jobs with applications cannot become private drafts");
qa(jobproEmployerApplicant($company["profile_id"],$appId)!==null,"Closing preserves the submitted application");
$r=postAction($company,"open",["job_id"=>$jobId]);qa($r["status"]===200,"Closed jobs can be reopened");
$r=req($company["client"],"/api/employer.php",array_replace($data,["csrf_token"=>$company["token"],"action"=>"save","job_id"=>$jobId,"job_title"=>"QA Phase 5 Updated Engineer","publication_status"=>"open"]));qa(cleanPage($r)&&str_contains($r["url"],"/pages/jobs.php")&&str_contains($r["body"],"QA Phase 5 Updated Engineer"),"Native job edit saves and redirects to job management");
$r=postAction($company,"",["job_id"=>$jobId,"job_title"=>"QA Phase 5 Legacy Edit","job_description"=>"Updated role description","job_location"=>"Remote","min_salary"=>"100.25","max_salary"=>"300.50"],"/api/update_job.php");qa($r["status"]===200,"Original job-update endpoint remains functional");
$r=postAction($company,"company",["company_name"=>"QA Northstar <script>never_execute()</script>","website_link"=>"https://example.invalid","address"=>"Colombo, Sri Lanka"]);qa($r["status"]===200,"Company can update public company details");
$r=req($guest,"/pages/company.php?id=".$company["profile_id"]);qa(cleanPage($r)&&str_contains($r["body"],"&lt;script&gt;")&&!str_contains($r["body"],$company["name"]."@example.invalid"),"Public company page escapes text and excludes account email");
$r=postAction($company,"company",["company_name"=>"QA Northstar","website_link"=>"javascript:alert(1)","address"=>"Colombo"]);qa($r["status"]===422,"Unsafe company website URLs are rejected");
$r=req($guest,"/pages/company.php?id=invalid");qa($r["status"]===404&&doc($r["body"])->query("//h1")->length===1,"Malformed public company URLs return an accessible 404");
$empty=jobproEmployerSaveJob($company["profile_id"],null,array_replace($data,["job_title"=>"QA Phase 5 Empty Job","publication_status"=>"open"]));
$r=postAction($candidate,"save",["job_id"=>$empty],"/api/candidate.php");qa($r["status"]===200,"Candidate can save an application-free role");
$r=postAction($company,"delete",["job_id"=>$empty]);qa($r["status"]===422,"Permanent deletion requires explicit confirmation");
$r=postAction($company,"delete",["job_id"=>$empty,"confirm"=>"yes"]);qa($r["status"]===200&&!jobproEmployerJob($company["profile_id"],$empty),"Application-free job can be deleted");
qa((int)jobproEmployerRows("SELECT COUNT(*) AS total FROM saved_jobs WHERE job_id=?","i",[$empty])[0]["total"]===0,"Job deletion removes its saved bookmarks");
for($i=0;$i<9;$i++){jobproEmployerSaveJob($company["profile_id"],null,array_replace($data,["job_title"=>"QA Pagination Role ".$i]));}
$r=req($company["client"],"/pages/jobs.php?q=QA%20Pagination&status=draft&page=2");qa(cleanPage($r)&&doc($r["body"])->query("//article[contains(@class,\"jp-employer-job-card\")]")->length===1,"Job search, status filter, and second-page pagination work together");
$r=req($company["client"],"/pages/applicants.php?q=".rawurlencode("' OR 1=1 --"));qa(cleanPage($r)&&str_contains($r["body"],"No applications to show."),"Applicant search treats SQL-like input as text");
$stats=jobproEmployerStats($company["profile_id"]);qa($stats["applications"]===1&&$stats["draft"]===9&&$stats["open"]===1,"Dashboard counts come from the company data");

require_once jobproPath("src/Services/candidate.php");
$draftRows=jobproEmployerRows("SELECT id FROM jobs WHERE company_id=? AND publication_status=? ORDER BY id","is",[$company["profile_id"],"draft"]);
$i=0;foreach($draftRows as$row){$jid=(int)$row["id"];jobproEmployerJobAction($company["profile_id"],$jid,"open");$aid=jobproCandidateApply($candidate["id"],$jid,"Pagination verification");if($i%2)jobproEmployerSetStatus($company["profile_id"],$aid,"reviewing","submitted");$i++;}
$r=req($company["client"],"/pages/applicants.php?page=2");qa(cleanPage($r)&&doc($r["body"])->query("//article[contains(@class,\"jp-employer-applicant-card\")]")->length===2,"Applicant pagination returns the correct second-page records");
$r=req($company["client"],"/pages/applicants.php?status=reviewing&sort=name");qa(cleanPage($r)&&doc($r["body"])->query("//article[contains(@class,\"jp-employer-applicant-card\")]")->length===4,"Application status filter and name sort work together");
$r=req($candidate["client"],"/pages/applications.php?page=2");qa(cleanPage($r)&&doc($r["body"])->query("//article[contains(@class,\"jp-candidate-application\")]")->length===2,"Candidate application pagination remains correct");
$r=req($candidate["client"],"/pages/dashboard.php");qa(cleanPage($r)&&str_contains($r["body"],"In review"),"Candidate overview shows real application statuses");
$r=postAction($company,"closed",["job_id"=>$jobId]);qa($r["status"]===200,"Role can close after status updates");
$r=req($candidate["client"],"/pages/saved-jobs.php");qa(cleanPage($r)&&str_contains($r["body"],"Closed to new applications"),"Saved opportunities clearly identify closed roles");
$foreign=jobproEmployerSaveJob($other["profile_id"],null,array_replace($data,["job_title"=>"QA Foreign Role"]));
$r=req($company["client"],"/pages/applicants.php?job=".$foreign);qa($r["status"]===404,"Applicant job filters cannot inspect another company");
$r=postAction($company,"",["job_id"=>$foreign],"/api/delete_job.php");qa($r["status"]===404,"Original delete endpoint enforces company ownership");
$r=req($company["client"],"/api/job_post.php",array_replace($data,["job_title"=>"QA Original Post Endpoint","publication_status"=>"open","csrf_token"=>$company["token"]]));qa(cleanPage($r)&&str_contains($r["body"],"QA Original Post Endpoint"),"Original native job-post endpoint uses the shared workflow");
$r=req($candidate["client"],"/pages/account.php");$r=req($candidate["client"],"/api/logout.php",["csrf_token"=>csrf($r["body"])]);$r=req($candidate["client"],"/pages/account.php");qa(str_contains($r["url"],"/pages/login.php"),"Existing candidate logout still revokes access");

echo $checks." employer HTTP/database checks passed.".PHP_EOL;
}catch(Throwable$error){echo "FAIL: ".$error->getMessage().PHP_EOL;$failed=true;
}finally{
foreach($clients as$h)curl_close($h);
foreach($users as$id){
$companyRows=jobproEmployerRows("SELECT company_id FROM company WHERE user_id=?","i",[$id]);
foreach($companyRows as$row){$cid=(int)$row["company_id"];$s=$db->prepare("DELETE a FROM applications a JOIN jobs j ON j.id=a.job_id WHERE j.company_id=?");$s->bind_param("i",$cid);$s->execute();$s=$db->prepare("DELETE FROM jobs WHERE company_id=?");$s->bind_param("i",$cid);$s->execute();}
foreach(["saved_jobs","applications","terms_acceptance","person","company","user"]as$table){$s=$db->prepare("DELETE FROM ".$table." WHERE user_id=?");$s->bind_param("i",$id);$s->execute();}
}
echo "Disposable Phase 5 database fixtures removed.".PHP_EOL;
}

if($failed??false)exit(1);
