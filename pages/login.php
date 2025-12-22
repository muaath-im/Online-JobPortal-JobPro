<?php 
session_start();
$pageTitle = "Login to Your Account";
$pagename = 'login';
include '../includes/header.php';
// var_dump($_SESSION['user_id']);
?>
<div class="container">
    <div class="login-container">
        <h1>Login to Your Account</h1>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form class="login-form" action="/jobpro/api/login_api.php" method="POST">
            <div class="social-login">
                <button type="button" class="google-login">
                    <i class="fa-brands fa-google" style="margin:10px"></i>
                    Login with Google
                </button>
            </div>
            <div class="separator">
                <span>OR</span>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="remember-me">
                    Remember me
                </label>
            </div>
            <button type="submit" class="login-button">Login</button>
            <p>Don't have an account? <a href="register.php">Register Now</a></p>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
