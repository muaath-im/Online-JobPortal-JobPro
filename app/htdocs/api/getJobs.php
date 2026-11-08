<?php
require_once is_file(__DIR__ . '/../src/config.php') ? __DIR__ . '/../src/config.php' : __DIR__ . '/../../src/config.php';
require_once jobproPath('src/Services/jobs.php');

if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    jobproJson(['success' => true, 'jobs' => getJobListings()]);
}
