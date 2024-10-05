<?php
session_start();
$pageTitle = "Post a Job";
include '../includes/header.php';
?>

<div class="container post-job-page">
    <h1>Job Details:</h1>
    <form action="/jobpro/api/job_post.php" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="job-title">Job Title:</label>
                <input type="text" id="job-title" name="job_title" placeholder="Job Title" required>
            </div>
            <div class="form-group">
                <label for="job-category">Job Category:</label>
                <select id="job-category" name="job_category" required>
                    <option value="">Job Category</option>
                    <!-- Add job categories here -->
                     <option value="Software Development">Software Development</option>
                     <option value="Data Science">Data Science</option>
                     <option value="Marketing">Marketing</option>

                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="job-location">Job Location:</label>
                <select id="job-location" name="job_location" required>
                    <option value="">Job Location</option>
                    <!-- Add job locations here -->
                     <option value="New York">New York</option>
                     <option value="Los Angeles">Los Angeles</option>
                     <option value="Nugegoda">Nugegoda</option>
                     <option value="Colombo">Colombo</option>
                     <option value="Tokyo">Tokyo</option>
                </select>
            </div>
            <div class="form-group">
                <label for="job-type">Job Type:</label>
                <select id="job-type" name="job_type" required>
                    <option value="">Job Type</option>
                    <!-- Add job types here -->
                     <option value="Full-time">Full-time</option>
                     <option value="Part-time">Part-time</option>
                     <option value="Internship">Internship</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="min-salary">Salary:</label>
                <input type="number" id="min-salary" name="min_salary" placeholder="min salary" required>
                <input type="number" id="max-salary" name="max_salary" placeholder="max salary" required>
            </div>
            <div class="form-group">
                <label for="skill-required">Skill Required:</label>
                <input type="text" id="skill-required" name="skill_required" placeholder="Skill Required" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="notice-period">Notice Period:</label>
                <input type="text" id="notice-period" name="notice_period" placeholder="Notice Period" required>
            </div>
            <div class="form-group">
                <label for="joining-facility">Joining facility:</label>
                <input type="text" id="joining-facility" name="joining_facility" placeholder="Joining facilities" required>
            </div>
        </div>
        <div class="form-group">
            <label for="job-description">Job Description:</label>
            <textarea id="job-description" name="job_description" rows="5" required></textarea>
        </div>
        <button type="submit" class="submit-button">Post</button>
        <!-- the button on click redirects to jobs.php -->
         <button type="button" style="margin-top:1rem" class="submit-button" onclick="window.location.href='jobs.php'">See <?=$_SESSION['username']?>'s Jobs</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>