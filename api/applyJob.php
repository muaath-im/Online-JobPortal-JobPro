<?php
session_start();
require_once '../includes/dbconnect.php';

// Enable errors for debugging
ini_set('display_errors', 1);

// Check if the request is a POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $job_id = $data['job_id'];
    $user_id = $data['user_id'];

    // Validate input
    if (!empty($job_id) && !empty($user_id)) {
        // Check if the user has already applied for this job
        $checkQuery = "SELECT * FROM applications WHERE job_id = ? AND user_id = ?";
        $checkStmt = $mysqli->prepare($checkQuery);
        $checkStmt->bind_param("ii", $job_id, $user_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            // User has already applied for this job
            echo json_encode(['success' => false, 'message' => 'You have already applied for this job']);
        } else {
            // Proceed to insert the new application
            $query = "INSERT INTO applications (job_id, user_id) VALUES (?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ii", $job_id, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error applying for job']);
            }
            $stmt->close();
        }
        $checkStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid job or user ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$mysqli->close();