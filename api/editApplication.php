<?php
session_start();
require_once '../includes/dbconnect.php'; // Ensure database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $applicationId = $_POST['application_id'];
    $coverLetter = $_POST['cover_letter'];

    // Prepare SQL statement to update application details
    $stmt = $mysqli->prepare("UPDATE applications SET cover_letter = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param('sii', $coverLetter, $applicationId, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Application updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update application']);
    }

    $stmt->close();
    $mysqli->close();
}
