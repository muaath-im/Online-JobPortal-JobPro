<?php
// Preserve the original form endpoint while sharing the employer validation path.
$_POST['action']='save';
unset($_POST['job_id']);
require jobproPath('src/Http/employer.php');