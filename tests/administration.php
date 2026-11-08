<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/app/src/config.php';
require jobproPath('src/Services/administration.php');
$checks=0;
function unit(bool $ok,string $label): void{global$checks;if(!$ok)throw new RuntimeException('FAIL: '.$label);$checks++;echo 'PASS: '.$label.PHP_EOL;}
foreach(['overview','users','companies','jobs','applications','access','audit']as$section)unit(jobproAdminCan('owner',$section),'Owner permission: '.$section);
foreach(['overview','companies','jobs','job','audit']as$section)unit(jobproAdminCan('moderator',$section),'Moderator permission: '.$section);
foreach(['users','applications','access']as$section)unit(!jobproAdminCan('moderator',$section),'Moderator cannot access '.$section);
unit(!jobproAdminCan('personal','overview')&&!jobproAdminCan('company','jobs'),'Ordinary account types do not grant administration');
unit(jobproAdminMemberChangeError(1,1,'owner','none',2)!==null,'An administrator cannot revoke their own access');
unit(jobproAdminMemberChangeError(1,1,'owner','moderator',1)!==null,'The sole owner cannot demote themselves');
unit(jobproAdminMemberChangeError(2,1,'owner','moderator',1)!==null,'Last-owner protection rejects a demotion independently of self protection');
unit(jobproAdminMemberChangeError(2,1,'owner','none',1)!==null,'Last-owner protection rejects revocation');
unit(jobproAdminMemberChangeError(2,1,'owner','moderator',2)===null,'Another owner can demote an owner when an owner remains');
unit(jobproAdminReason('A clear moderation reason.')===null,'A clear reason is accepted');
unit(jobproAdminReason('short')!==null,'A short reason is rejected');
unit(jobproAdminReason(str_repeat('x',501))!==null,'A long reason is rejected');
unit(jobproAdminReason('Reason'.chr(0).'with a null byte')!==null,'Null bytes are rejected');
unit(jobproAdminReason("Invalid UTF-8 \xff")!==null,'Malformed UTF-8 is rejected');
$sql=file_get_contents(dirname(__DIR__).'/database/migrations/006_administration.sql');
unit(!preg_match('/\b(DROP|DELETE|TRUNCATE)\b/i',$sql),'Migration contains no prohibited destructive commands or keywords');
unit(substr_count($sql,'FROM information_schema.COLUMNS')===6,'Each moderation column has an independent idempotence guard');
unit(substr_count($sql,'CREATE TABLE IF NOT EXISTS')===2,'Both administration tables have idempotent creation');
unit(substr_count($sql,'FROM information_schema.STATISTICS')===2,'Both added indexes have independent idempotence guards');
echo $checks.' administration unit checks passed.'.PHP_EOL;
