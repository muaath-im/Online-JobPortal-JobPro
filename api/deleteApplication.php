<?php
session_start();
require_once '../includes/dbconnect.php';

// Check if the request is a POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $application_id = $data['application_id'];

    // Validate input
    if (!empty($application_id)) {
        $query = "DELETE FROM applications WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $application_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting application']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
