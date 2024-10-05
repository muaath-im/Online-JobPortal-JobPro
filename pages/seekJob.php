<?php
session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$pageTitle = "Want a Job";
$pagename = "seekJob";
include '../includes/header.php';
require_once '../includes/dbconnect.php';
require_once '../api/getJobs.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<div class="container want-job-page">
    <h1>Available Jobs:</h1>
    <div class="notification-toggle">
        <label for="notification-switch">Notification</label>
        <input type="checkbox" id="notification-switch" class="toggle-switch">
    </div>
    <div class="job-listings">
        <?php
        $jobListings = getJobListings();
        if (!empty($jobListings)) {
            foreach ($jobListings as $job) {
                ?>
                <div class="job-card">
                    <div class="job-logo">
                        <img src="<?php echo $job['logo_url']; ?>" alt="<?php echo $job['company_name']; ?> logo">
                    </div>
                    <div class="job-info">
                        <h2><?php echo $job['job_name']; ?></h2>
                        <h3><?php echo $job['company_name']; ?></h3>
                        <p class="job-salary">Salary range: <?php echo $job['salary_range']; ?></p>
                        <p class="job-description"><?php echo $job['description']; ?></p>
                        <p class="job-location"><?php echo $job['location']; ?></p>
                    </div>
                    <div class="job-rating">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $job['rating'] ? '★' : '☆';
                        }
                        ?>
                    </div>
                    <button class="apply-button" onclick="applyForJob(<?= $job['id'] ?>)">Apply</button>
                </div>
                <?php
            }
        } else {
            echo "<p>No jobs available</p>";
        }
        ?>
    </div>

    <h2>Your Applications:</h2>
    <div class="applications-list">
        <?php
        // Fetch applications for the logged-in user
        $applications = getUserApplications($user_id); // Function to fetch user applications
        if (!empty($applications)) {
            foreach ($applications as $application) {
                ?>
                <div class="application-card">
                    <p>Applied for: <?php echo $application['job_title']; ?></p>
                    <p>Applied on: <?php echo date('M d, Y', strtotime($application['applied_at'])); ?></p>
                    <button class="delete-button" onclick="deleteApplication(<?= $application['id'] ?>)">Delete Application</button>
                </div>
                <?php
            }
        } else {
            echo "<p>You have not applied for any jobs yet.</p>";
        }
        ?>
    </div>
</div>

<script>
// Apply for a job
// Apply for a job
function applyForJob(jobId) {
    const userId = <?= json_encode($user_id); ?>; // Get user ID from PHP
    fetch('/jobpro/api/applyJob.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            job_id: jobId, // Pass as JSON object
            user_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Application submitted successfully!');
            location.reload(); // Reload to update the application list
        } else {
            alert('Error applying for job: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Delete application
function deleteApplication(applicationId) {
    if (confirm('Are you sure you want to delete this application?')) {
        fetch(`/jobpro/api/deleteApplication.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ application_id: applicationId }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Application deleted successfully!');
                location.reload(); // Reload to update the application list
            } else {
                alert('Error deleting application: ' + data.message);
            }
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
