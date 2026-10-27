<?php
// Public, read-only projection of the existing jobs query. No account data.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['error' => 'Use GET to browse jobs.']);
    exit;
}
if (isset($_GET['id']) && (!is_string($_GET['id']) || !ctype_digit($_GET['id']) || (int) $_GET['id'] < 1)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid job identifier.']);
    exit;
}
try {
    require_once __DIR__ . '/getJobs.php';
    $publicJobs = [];
    foreach (getJobListings() as $row) {
        if (isset($_GET['id']) && (int) $_GET['id'] !== (int) $row['id']) {
            continue;
        }
        $publicJobs[] = [
            'id' => (int) $row['id'], 'title' => $row['job_name'], 'company' => $row['company_name'],
            'description' => $row['description'], 'location' => $row['location'],
            'category' => $row['job_category'], 'type' => $row['job_type'],
            'salaryMin' => (float) $row['min_salary'], 'salaryMax' => (float) $row['max_salary'],
            // Currency and payment period are absent from the original schema.
            'currency' => null, 'period' => null, 'skills' => $row['skill_required'],
            'notice' => $row['notice_period'], 'benefits' => $row['joining_facility'],
            'posted' => $row['created_at'], 'website' => $row['company_website'],
        ];
    }
    if (isset($_GET['id']) && !$publicJobs) {
        http_response_code(404);
    }
    echo json_encode(['jobs' => $publicJobs], JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log('JobPro public discovery query failed (code ' . $error->getCode() . ').');
    http_response_code(503);
    echo json_encode(['error' => 'Job listings are temporarily unavailable.']);
}
