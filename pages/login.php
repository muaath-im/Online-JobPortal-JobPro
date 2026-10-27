<?php 
session_start();
$loginError = $_SESSION['login_error'] ?? '';
$loginUsername = $_SESSION['login_username'] ?? '';
unset($_SESSION['login_error'], $_SESSION['login_username']);
$pageTitle = "Login to Your Account";
$pagename = 'login';
include '../includes/header.php';
// var_dump($_SESSION['user_id']);
?>
<div class="container">
    <div class="login-container">
        <h1>Login to Your Account</h1>

        <?php if ($loginError !== ''): ?>
            <div class="jp-alert jp-alert--error" role="alert"><?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form class="login-form" action="/api/login_api.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Username" autocomplete="username" aria-describedby="username-hint" value="<?php echo htmlspecialchars($loginUsername, ENT_QUOTES, 'UTF-8'); ?>" required>
                <p id="username-hint">Use the username you registered with, rather than your email address.</p>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="login-button">Login</button>
            <p>Don't have an account? <a href="register.php">Register Now</a></p>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
