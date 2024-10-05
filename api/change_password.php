<?php
session_start();
include '../includes/dbconnect.php';

if (isset($_POST['change_password'])) {
    $userId = $_SESSION['user_id'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if new password matches confirm password
    if ($newPassword !== $confirmPassword) {
        echo "Passwords do not match.";
        exit();
    }

    // Fetch the current password from the database
    $stmt = $mysqli->prepare("SELECT password FROM user WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verify the current password
    if (!password_verify($currentPassword, $hashedPassword)) {
        echo "Incorrect current password.";
        exit();
    }

    // Hash the new password and update it in the database
    $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare("UPDATE user SET password = ? WHERE user_id = ?");
    $stmt->bind_param('si', $newHashedPassword, $userId);

    if ($stmt->execute()) {
        echo "Password changed successfully.";
    } else {
        echo "Failed to change password.";
    }

    $stmt->close();
    header('Location: ../pages/account.php');
}

