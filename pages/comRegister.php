<?php 
session_start();
$pageTitle = "Sign Up";
$registerType = "Company";
include '../includes/header.php';
?>
        <div class="container">
            <form class="registration-form" action="/jobpro/api/auth_api.php" method="POST" enctype="multipart/form-data">
                <div class="form-tabs">
                    <?php if($registerType == "Person") : ?>
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
                        <label for="companyName">Company Name:</label>
                        <input type="text" id="companyName" name="companyName" placeholder="companyName" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password:</label>
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Re-Enter Password" required>
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
                        <label for="website">Website Link:</label>
                        <input type="text" id="website" name="website" placeholder="website Link">
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