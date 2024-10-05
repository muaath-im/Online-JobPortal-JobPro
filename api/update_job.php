<?php
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