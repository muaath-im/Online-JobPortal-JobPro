<?php
session_start();
require_once '../includes/dbconnect.php'; // Ensure you have the database connection

// Function to fetch job listings
function getJobListings() {
    global $mysqli; // Ensure to use the $mysqli variable for DB connection
    $jobListings = [];

    // SQL query to fetch job details
    $stmt = $mysqli->prepare("SELECT j.job_title AS job_name, c.company_name, j.job_description AS description, 
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
