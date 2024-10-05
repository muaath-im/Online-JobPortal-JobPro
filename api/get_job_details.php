<?php
// File: get_job_details.php
session_start();
require_once '../includes/dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'company') {
    exit(json_encode(['error' => 'Unauthorized']));
}

$job_id = $_GET['id'] ?? null;
$company_id = $_SESSION['company_id'];

if (!$job_id) {
    exit(json_encode(['error' => 'Job ID is required']));
}

$query = "SELECT id, job_title, job_description, min_salary, max_salary, job_location 
          FROM jobs 
          WHERE id = ? AND company_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('ii', $job_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();

if ($job = $result->fetch_assoc()) {
    echo json_encode($job);
} else {
    echo json_encode(['error' => 'Job not found']);
}

$stmt->close();
$mysqli->close();

// File: update_job.php
session_start();
require_once '../includes/dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'company') {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$job_id = $_POST['job_id'] ?? null;
$company_id = $_SESSION['company_id'];
$job_title = $_POST['job_title'] ?? '';
$job_description = $_POST['job_description'] ?? '';
$min_salary = $_POST['min_salary'] ?? 0;
$max_salary = $_POST['max_salary'] ?? 0;
$job_location = $_POST['job_location'] ?? '';

if (!$job_id) {
    exit(json_encode(['success' => false, 'message' => 'Job ID is required']));
}

$query = "UPDATE jobs 
          SET job_title = ?, job_description = ?, min_salary = ?, max_salary = ?, job_location = ? 
          WHERE id = ? AND company_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('ssiiiii', $job_title, $job_description, $min_salary, $max_salary, $job_location, $job_id, $company_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Job updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating job: ' . $mysqli->error]);
}

$stmt->close();
$mysqli->close();