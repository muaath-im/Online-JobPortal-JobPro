<?php 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = "User Account";
$pagename = 'account';
include '../includes/header.php';
include '../includes/functions.php';

// Fetch user data from the database
$userId = $_SESSION['user_id']; // Assume user is logged in
$userData = getUserData($userId); // Implement this function to fetch user data

// Fetch personal account data
$personData = getPersonData($userId); // Implement this function to fetch personal data if user is a person

// Fetch company account data
$companyData = getCompanyData($userId); // Implement this function to fetch company data if user is a company
?>

<div class="container user-account-page">
    <div class="user-profile">
        <h2>User:</h2>
        <div class="profile-image">
            <img src="<?php echo $personData['profile_image'] ?? '/assets/images/default-avatar.png'; ?>" alt="User Avatar">
        </div>
        
        <?php if ($personData): ?>
            <h3>Bio:</h3>
            <p><?php echo htmlspecialchars($personData['qualification']); ?></p>
            <h3>Experience:</h3>
            <p><?php echo htmlspecialchars($personData['year_of_experience']); ?> years</p>
            <h3>Location:</h3>
            <p><?php echo htmlspecialchars($personData['location'] . ', ' . $personData['city'] . ', ' . $personData['country'] . ' - ' . $personData['zip_code']); ?></p>
            <h3>Portfolio:</h3>
            <p><a href="<?php echo htmlspecialchars($personData['portfolio_link']); ?>" target="_blank"><?php echo htmlspecialchars($personData['portfolio_link']); ?></a></p>
            <h3>CV:</h3>
            <p><a href="<?php echo htmlspecialchars($personData['cv_link']); ?>" target="_blank">Download CV</a></p>
        <?php endif; ?>

        <?php if ($companyData): ?>
            <h3>Company Name:</h3>
            <p><?php echo htmlspecialchars($companyData['company_name']); ?></p>
            <h3>Website:</h3>
            <p><a href="<?php echo htmlspecialchars($companyData['website_link']); ?>" target="_blank"><?php echo htmlspecialchars($companyData['website_link']); ?></a></p>
            <h3>Address:</h3>
            <p><?php echo htmlspecialchars($companyData['address']); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="account-info-section">
        <div class="account-info">
            <h2>Account Information:</h2>
            <form action="/jobpro/api/update_account.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" readonly>
                </div>
                <?php if($_SESSION['account_type'] == 'personal') : ?>
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($personData['full_name']); ?>" readonly>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="phone">Phone number:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userData['phone_number']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>">
                </div>
                <button type="submit" name="update_info" class="btn-primary">Update Info</button>
            </form>
            <form action="logout.php" method="POST">
                <button type="submit" class="btn-secondary">Log out</button>
            </form>
        </div>
        
        <div class="change-password">
            <h2>Change Password:</h2>
            <form action="/jobpro/api/change_password.php" method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="btn-primary">Change</button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

