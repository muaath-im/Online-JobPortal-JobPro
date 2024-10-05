<?php
session_start();
require_once '../includes/dbconnect.php'; // Ensure you have the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect input data and escape it to prevent SQL injection
    $job_title = $mysqli->real_escape_string($_POST['job_title']);
    $job_category = $mysqli->real_escape_string($_POST['job_category']);
    $job_location = $mysqli->real_escape_string($_POST['job_location']);
    $job_type = $mysqli->real_escape_string($_POST['job_type']);
    $min_salary = $mysqli->real_escape_string($_POST['min_salary']);
    $max_salary = $mysqli->real_escape_string($_POST['max_salary']);
    $skill_required = $mysqli->real_escape_string($_POST['skill_required']);
    $notice_period = $mysqli->real_escape_string($_POST['notice_period']);
    $joining_facility = $mysqli->real_escape_string($_POST['joining_facility']);
    $job_description = $mysqli->real_escape_string($_POST['job_description']);
    // set company_id with session[company_id], if not null, else echo only companies can create jobs
    $company_id = $_SESSION['company_id'];
    if ($company_id == null) {
        echo "Only companies can create jobs";
        exit;
    }

    // Prepare SQL statement to insert job details
    $stmt = $mysqli->prepare("INSERT INTO jobs (job_title, job_category, job_location, job_type, min_salary, max_salary, skill_required, notice_period, joining_facility, job_description, company_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssddsssss', $job_title, $job_category, $job_location, $job_type, $min_salary, $max_salary, $skill_required, $notice_period, $joining_facility, $job_description, $company_id);

    if ($stmt->execute()) {
        // Redirect or display a success message
        $_SESSION['success'] = "Job posted successfully!";
        header('Location: ../pages/jobs.php'); // Redirect to a success page
        exit();
    } else {
        // Handle error
        echo "Error posting job: " . $mysqli->error;
    }

    // Close the statement and connection
    $stmt->close();
    $mysqli->close();
} else {
    // Redirect if accessed directly
    header('Location: ../pages/jobs.php');
    exit();
}

