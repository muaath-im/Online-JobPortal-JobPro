<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/dbconnect.php';

if (isset($_POST['update_info'])) {
    $userId = $_SESSION['user_id'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $stmt = $mysqli->prepare("UPDATE user SET phone_number = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param('ssi', $phone, $email, $userId);
    
    if ($stmt->execute()) {
        echo "Account info updated successfully.";
    } else {
        echo "Failed to update account info.";
    }

    $stmt->close();
    header('Location: ../pages/account.php');
}
