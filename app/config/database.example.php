<?php
// For hosting, copy to config/dbconnect.php and enter credentials privately.
// The existing repository includes/dbconnect.php remains supported unchanged.
// The active connector is ignored by Git. Existing active files should be retained.
$host = 'localhost';
$dbname = 'jobpro';
$username = 'your_db_user';
$password = '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $mysqli = new mysqli($host, $username, $password, $dbname);
    $mysqli->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('JobPro database connection failed (code ' . $exception->getCode() . ').');
    http_response_code(500);
    exit('Database connection failed. Check your local configuration.');
}
