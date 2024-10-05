<?php
session_start();

if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    // Destroy the session
    session_destroy();

    // Unset all session variables
    $_SESSION = array();

    // Redirect to the login page
    header("Location: /jobpro/pages/login.php");
    exit();
}
