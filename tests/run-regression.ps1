param([ValidateRange(1024,65535)][int]$Port=8097)
$ErrorActionPreference='Stop'
$projectRoot=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$testRoot=Join-Path ([IO.Path]::GetTempPath()) ('jobpro-admin-tests-'+[guid]::NewGuid().ToString('N'))
$keys=@('JOBPRO_TEST_BASE_URL','JOBPRO_AUTH_STORAGE','JOBPRO_MAIL_TRANSPORT','JOBPRO_MAIL_BASE_URL','JOBPRO_DB_CONFIG','JOBPRO_TEST_APP_CONFIG')
$previous=@{};foreach($key in $keys){$previous[$key]=[Environment]::GetEnvironmentVariable($key,'Process')}
$server=$null
Push-Location $projectRoot
try{
    New-Item -ItemType Directory -Path $testRoot | Out-Null
    $state=Join-Path $testRoot 'state';New-Item -ItemType Directory -Path $state | Out-Null
    $env:JOBPRO_AUTH_STORAGE=$state
    $env:JOBPRO_TEST_BASE_URL="http://127.0.0.1:$Port"
    $env:JOBPRO_MAIL_BASE_URL=$env:JOBPRO_TEST_BASE_URL
    $env:JOBPRO_MAIL_TRANSPORT='log'
    $env:JOBPRO_TEST_APP_CONFIG=Join-Path $projectRoot 'app/src/config.php'
    if(-not $env:JOBPRO_DB_CONFIG -and (Test-Path -LiteralPath (Join-Path $projectRoot 'includes/dbconnect.php'))){$env:JOBPRO_DB_CONFIG=Join-Path $projectRoot 'includes/dbconnect.php'}
    $phpBinary=php -r 'echo PHP_BINARY;'
    if($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $phpBinary)){throw 'PHP is required.'}
    $webRoot=Join-Path $projectRoot 'app/htdocs'
    # A private router invokes only the CLI tool's web guard. It is never deployed.
    $toolPath=(Join-Path $projectRoot 'tools/admin-access.php').Replace('\','/')
    $routerPath=Join-Path $testRoot 'router.php'
    $routerSource="<?php`nif (parse_url(`$_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/__cli_guard_probe.php') { require '$toolPath'; return true; }`nreturn false;`n"
    [IO.File]::WriteAllText($routerPath,$routerSource,[Text.UTF8Encoding]::new($false))
    $server=Start-Process -FilePath $phpBinary -ArgumentList @('-d',('"session.save_path='+$state+'"'),'-S',"127.0.0.1:$Port",'-t',('"'+$webRoot+'"'),('"'+$routerPath+'"')) -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $testRoot 'server.stdout') -RedirectStandardError (Join-Path $testRoot 'server.stderr')
    Start-Sleep -Milliseconds 400
    if($server.HasExited){throw 'The loopback test server did not start. Choose another port.'}
    foreach($test in @('administration.php','employer-http.php','administration-http.php')){
        & $phpBinary ('tests/'+$test)
        if($LASTEXITCODE -ne 0){throw ('Regression failed: '+$test)}
    }
    Write-Output 'All administration and employer regression checks passed.'
}finally{
    if($server -and -not $server.HasExited){Stop-Process -Id $server.Id}
    foreach($key in $keys){[Environment]::SetEnvironmentVariable($key,$previous[$key],'Process')}
    $resolved=[IO.Path]::GetFullPath($testRoot);$temp=[IO.Path]::GetFullPath([IO.Path]::GetTempPath()).TrimEnd('\')+'\'
    if(-not $resolved.StartsWith($temp,[StringComparison]::OrdinalIgnoreCase) -or -not ([IO.Path]::GetFileName($resolved)).StartsWith('jobpro-admin-tests-')){throw 'Unexpected test cleanup path.'}
    if(Test-Path -LiteralPath $testRoot){Remove-Item -LiteralPath $testRoot -Recurse -Force}
    Pop-Location
    Write-Output 'Test server stopped; temporary mail/session state removed; environment restored.'
}
