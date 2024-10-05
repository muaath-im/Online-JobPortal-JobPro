<?php
// Update the existing file (let's assume it's jobs.php)
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
$pageTitle = "Jobs You Posted";
$pagename = "jobs";
require_once '../includes/dbconnect.php';

// Check if the user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'company') {
 header('Location: login.php');
exit();
}
$company_id = $_SESSION['company_id'];
// Fetch jobs posted by this company
$query = "SELECT j.id, j.job_title, j.job_description, j.min_salary, j.max_salary, j.job_location, j.created_at
FROM jobs j
WHERE j.company_id = ?
ORDER BY j.created_at DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $company_id);
$stmt->execute();
$result = $stmt->get_result();
$jobs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$mysqli->close();

include '../includes/header.php';
?>

<div class="jobs-container">
    <h1>Your Posted Jobs</h1>
    <?php if (empty($jobs)): ?>
        <p>You haven't posted any jobs yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Location</th>
                    <th>Salary Range</th>
                    <th>Posted Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td><?= htmlspecialchars($job['job_title']) ?></td>
                        <td><?= htmlspecialchars($job['job_location']) ?></td>
                        <td>$<?= htmlspecialchars($job['min_salary']) ?> - $<?= htmlspecialchars($job['max_salary']) ?></td>
                        <td><?= date('M d, Y', strtotime($job['created_at'])) ?></td>
                        <td>
                            <button onclick="openEditPopup(<?= $job['id'] ?>)">Edit</button>
                            <a href="/jobpro/api/delete_job.php?id=<?= $job['id'] ?>" onclick="return confirm('Are you sure you want to delete this job?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <a href="createJob.php" class="button">Post a New Job</a>
</div>

<!-- Edit Job Popup -->
<div id="editJobPopup" class="popup">
    <div class="popup-content">
        <span class="close" onclick="closeEditPopup()">&times;</span>
        <h2>Edit Job</h2>
        <form id="editJobForm">
            <input type="hidden" id="editJobId" name="job_id">
            <label for="editJobTitle">Job Title:</label>
            <input type="text" id="editJobTitle" name="job_title" required>
            
            <label for="editJobDescription">Job Description:</label>
            <textarea id="editJobDescription" name="job_description" required></textarea>
            
            <label for="editMinSalary">Minimum Salary:</label>
            <input type="number" id="editMinSalary" name="min_salary" required>
            
            <label for="editMaxSalary">Maximum Salary:</label>
            <input type="number" id="editMaxSalary" name="max_salary" required>
            
            <label for="editJobLocation">Job Location:</label>
            <input type="text" id="editJobLocation" name="job_location" required>
            
            <button type="submit">Update Job</button>
        </form>
    </div>
</div>

<script>
function openEditPopup(jobId) {
    // Fetch job details and populate the form
    fetch(`/jobpro/api/get_job_details.php?id=${jobId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editJobId').value = data.id;
            document.getElementById('editJobTitle').value = data.job_title;
            document.getElementById('editJobDescription').value = data.job_description;
            document.getElementById('editMinSalary').value = data.min_salary;
            document.getElementById('editMaxSalary').value = data.max_salary;
            document.getElementById('editJobLocation').value = data.job_location;
            
            document.getElementById('editJobPopup').style.display = 'block';
        });
}

function closeEditPopup() {
    document.getElementById('editJobPopup').style.display = 'none';
}

document.getElementById('editJobForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/jobpro/api/update_job.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Job updated successfully!');
            closeEditPopup();
            location.reload(); // Reload the page to show updated data
        } else {
            alert('Error updating job: ' + data.message);
        }
    });
});
</script>


<?php include '../includes/footer.php'; ?>