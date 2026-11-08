<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    require_once jobproPath('src/Core/auth.php'); jobproStartSession();
}
require_once jobproPath('src/Core/auth.php');
require_once jobproPath('src/Services/workflow.php');
$mysqli = jobproDatabase();

// Function to get user applications
function getUserApplications($user_id) {
    global $mysqli;  // Use the global connection

    $query = "SELECT a.id, j.job_title, a.applied_at, a.cover_letter FROM applications a 
              JOIN jobs j ON a.job_id = j.id WHERE a.user_id = ?";
    $stmt = $mysqli->prepare($query);

    if (!$stmt) {
        error_log('JobPro query preparation failed: ' . $mysqli->error);
        throw new RuntimeException('Unable to load applications.');
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        error_log('JobPro fetching result failed: ' . $mysqli->error);
        throw new RuntimeException('Unable to load application results.');
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}



// Function to fetch job listings
function getJobListings(bool $includeClosed = false) {
    global $mysqli; // Ensure to use the $mysqli variable for DB connection
    $jobListings = [];
    $visibility = jobproJobLifecycleReady() ? ($includeClosed?" WHERE j.publication_status<>'draft'":" WHERE j.publication_status='open'") : '';
    $visibility.=($visibility===''?' WHERE ':' AND ').jobproPublicJobCondition('j');
    $publication=jobproJobLifecycleReady()?'j.publication_status':"'open' AS publication_status";

    // SQL query to fetch job details
    $stmt = $mysqli->prepare("SELECT j.id, j.job_title AS job_name, c.company_name, j.job_description AS description, 
                              CONCAT(j.min_salary, ' - ', j.max_salary) AS salary_range, j.job_location AS location, j.image
                              , j.job_category, j.job_type, j.min_salary, j.max_salary,
                              j.skill_required, j.notice_period, j.joining_facility, j.created_at,
                              c.website_link AS company_website,c.company_id,$publication
                              FROM jobs j 
                              JOIN company c ON j.company_id = c.company_id
                              $visibility ORDER BY j.created_at DESC, j.id DESC");

    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all jobs into an array
    while ($row = $result->fetch_assoc()) {
        // The original SQL dump stores images under the old /jobpro/ mount.
        // Normalize those public asset URLs without modifying database records.
        if (isset($row['image']) && str_starts_with($row['image'], '/jobpro/assets/')) {
            $row['image'] = substr($row['image'], strlen('/jobpro'));
        }
        $jobListings[] = $row;
    }

    // Close the statement and return the job listings
    $stmt->close();
    return $jobListings;
}
