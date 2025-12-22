<?php
session_start();
include '../includes/dbconnect.php'; // Include your database connection

if (isset($_POST['delete_account'])) {
    $user_id = $_SESSION['user_id']; // Assuming the user's ID is stored in the session
    
    // First, delete any related records in the applications table
    $deleteApplicationsQuery = "DELETE FROM applications WHERE user_id = ?";
    $stmt = $mysqli->prepare($deleteApplicationsQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Then delete the user record
    $deleteUserQuery = "DELETE FROM user WHERE user_id = ?";
    $stmt = $mysqli->prepare($deleteUserQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Successfully deleted the user and their related data
        header("Location: /jobpro/pages/register.php");
        exit();
    } else {
        echo "Error deleting account.";
    }
} else {
    echo "Invalid request.";
}
