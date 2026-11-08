<?php
require_once jobproPath('src/Core/auth.php');
jobproStartSession();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') jobproRedirect('/pages/logout.php');
jobproPost('/pages/logout.php');
jobproSignOut();
jobproRedirect('/pages/login.php?logged_out=1');
