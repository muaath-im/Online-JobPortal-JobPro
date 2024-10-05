<?php 
session_start();
$pageTitle = "Sign Up";
$pagename = 'register';
$registerType = "Personal";
include '../includes/header.php';
?>
        <div class="container">
            <form class="registration-form" action="/jobpro/api/auth_api.php" method="POST" enctype="multipart/form-data">
                <div class="form-tabs">
                    <?php if($registerType == "Personal") : ?>
                        <button type="button" class="active">Personal Account</button>
                        <button type="button" onclick="window.location.href='/jobpro/pages/comRegister.php'">Company Account</button>
                    <?php else : ?>
                        <button type="button" onclick="window.location.href='/jobpro/pages/register.php'">Personal Account</button>
                        <button type="button" class="active">Company Account</button>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="registerType" value="<?=$registerType?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <label for="fullname">Full Name:</label>
                        <input type="text" id="fullname" name="fullname" placeholder="Full name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <span id="toggle-password" class="eye-icon" onclick="togglePasswordVisibility('password')"><i class="fa-solid fa-eye"></i></span>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password:</label>
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Re-Enter Password" required>
                        <span id="toggle-confirm-password" class="eye-icon" onclick="togglePasswordVisibility('confirm-password')"><i class="fa-solid fa-eye"></i></span>
                    </div>
                    <div class="form-group">
                        <label for="qualification">Qualification:</label>
                        <input type="text" id="qualification" name="qualification" placeholder="Qualification">
                    </div>
                    <div class="form-group">
                        <label for="experience">Year of Experience:</label>
                        <input type="number" id="experience" name="experience" placeholder="Year of Experience">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" placeholder="Phone number">
                    </div>
                    <div class="form-group">
                        <label for="location">Location:</label>
                        <input type="text" id="location" name="location" placeholder="Location">
                    </div>
                    <div class="form-group">
                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" placeholder="City">
                    </div>
                    <div class="form-group">
                        <label for="zipcode">Zip Code:</label>
                        <input type="text" id="zipcode" name="zipcode" placeholder="Zip code">
                    </div>
                    <div class="form-group">
                        <label for="country">Country:</label>
                        <select id="country" name="country">
                            <option value="">Select Country</option>
                            <!-- Add country options here -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="portfolio">Portfolio Link:</label>
                        <input type="text" id="portfolio" name="portfolio" placeholder="Portfolio Link">
                    </div>
                    <div class="form-group">
                        <label for="cv">CV Link:</label>
                        <input type="text" id="cv" name="cv" placeholder="CV Link">
                    </div>
                    <div class="profile-image">
                        <img src="/api/placeholder/150/150" alt="Profile Image Placeholder">
                        <input type="file" id="profile-image" name="profile-image" accept="image/*">
                        <label for="profile-image">Choose file</label>
                    </div>
                </div>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" required>
                        I accept that all the details I have entered are correct.
                    </label>
                </div>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" required>
                        I accept the Terms and Conditions.
                    </label>
                </div>
                <button type="submit" class="register-button">Register</button>
                <p>Already a member? <a href="#">Login Here</a></p>
            </form>
        </div>

        <?php include '../includes/footer.php'; ?>

    
<script>
    function togglePasswordVisibility(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(`toggle-${fieldId}`);

    if (passwordField.type === "password") {
        passwordField.type = "text"; // Show password
        eyeIcon.innerHTML = "<i class="fa-solid fa-eye-slash"></i>"; // Change icon to an eye with a slash
    } else {
        passwordField.type = "password"; // Hide password
        eyeIcon.innerHTML = "<i class="fa-solid fa-eye"></i>"; // Change icon back to normal eye
    }
}

// Function to validate the password confirmation
function validatePasswords() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return false; // Prevent form submission
    }
    return true; // Allow form submission
}

// Attach the validatePasswords function to the form submit event
document.querySelector('.registration-form').addEventListener('submit', function(event) {
    if (!validatePasswords()) {
        event.preventDefault(); // Prevent form submission if passwords do not match
    }
});
</script>