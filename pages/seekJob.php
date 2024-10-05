<?php
session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$pageTitle = "Want a Job";
$pagename = "seekJob";
include '../includes/header.php';
require_once '../includes/dbconnect.php';
require_once '../api/getJobs.php';
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
        if(!empty($jobListings)){
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
                    <button class="apply-button">Apply</button>
                </div>
                <?php
            }
        } else {
            echo "<p>No jobs available</p>";
        }
        ?>
    </div>

</div>

<?php include '../includes/footer.php'; ?>