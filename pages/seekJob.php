<?php
session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// Hide errors from being displayed to the user
ini_set('display_errors', 0);
error_reporting(0);

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
                        <img src="<?=$job['image']?>" alt="<?php echo $job['company_name']; ?> logo">
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
                    <p>Cover Letter: <?php echo $application['cover_letter']; ?></p>
                    <button class="edit-button" onclick="openEditForm(<?= $application['id'] ?>)">Edit Application</button>
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

<!-- Edit Application Modal -->
<div id="editApplicationModal" style="display:none;">
    <form id="editApplicationForm">
        <h2>Edit Cover Letter</h2>
        <input type="hidden" id="edit-application-id" name="application_id">
        <label for="cover_letter">Cover Letter</label>
        <input type="text" id="cover_letter" name="cover_letter" required>
        <button type="submit">Update Application</button>
        <button type="button" onclick="closeEditForm()">Cancel</button>
    </form>
</div>

<script>
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

// Open edit form
function openEditForm(applicationId) {
    // Fetch application details (replace with actual API call)
    fetch(`/jobpro/api/getApplication.php?application_id=${applicationId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const application = data.application;
            document.getElementById('edit-application-id').value = application.id;
            document.getElementById('cover_letter').value = application.cover_letter; 
            document.getElementById('editApplicationModal').style.display = 'flex';
        } else {
            alert('Error fetching application details: ' + data.message);
        }
    });
}

// Close edit form
function closeEditForm() {
    document.getElementById('editApplicationModal').style.display = 'none';
}

// Handle edit application form submission
document.getElementById('editApplicationForm').onsubmit = function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    
    fetch('/jobpro/api/editApplication.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Application updated successfully!');
            location.reload(); // Reload to update the application list
        } else {
            alert('Error updating application: ' + data.message);
        }
    });
};

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
