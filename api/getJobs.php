<?php
session_start();
require_once '../includes/dbconnect.php'; // Ensure you have the database connection

// Function to get user applications
function getUserApplications($user_id) {
    global $mysqli;  // Use the global connection

    $query = "SELECT a.id, j.job_title, a.applied_at FROM applications a 
              JOIN jobs j ON a.job_id = j.id WHERE a.user_id = ?";
    $stmt = $mysqli->prepare($query);

    if (!$stmt) {
        die('Query preparation failed: ' . $mysqli->error); // Debugging output
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die('Fetching result failed: ' . $mysqli->error); // Debugging output
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}



// Function to fetch job listings
function getJobListings() {
    global $mysqli; // Ensure to use the $mysqli variable for DB connection
    $jobListings = [];

    // SQL query to fetch job details
    $stmt = $mysqli->prepare("SELECT j.id, j.job_title AS job_name, c.company_name, j.job_description AS description, 
                              CONCAT(j.min_salary, ' - ', j.max_salary) AS salary_range, j.job_location AS location
                              FROM jobs j 
                              JOIN company c ON j.company_id = c.company_id");

    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all jobs into an array
    while ($row = $result->fetch_assoc()) {
        $jobListings[] = $row;
    }

    // Close the statement and return the job listings
    $stmt->close();
    return $jobListings;
}
