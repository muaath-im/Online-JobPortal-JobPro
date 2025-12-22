<?php
// Start session if needed
session_start();

// Include database connection
include_once '../includes/dbconnect.php'; // Assuming this file contains the database connection

// Check if application ID is provided
if (isset($_GET['application_id'])) {
    $applicationId = intval($_GET['application_id']); // Ensure it's an integer to prevent SQL injection

    // Prepare the SQL query to fetch the application details
    $stmt = $mysqli->prepare("SELECT * FROM applications WHERE id = ?");
    $stmt->bind_param("i", $applicationId); // Bind the application ID to the query
    $stmt->execute();

    // Fetch result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Convert result to an associative array
        $application = $result->fetch_assoc();

        // Return application details as JSON
        echo json_encode([
            'success' => true,
            'application' => $application
        ]);
    } else {
        // No application found with the given ID
        echo json_encode([
            'success' => false,
            'message' => 'Application not found'
        ]);
    }

    // Close the statement
    $stmt->close();
} else {
    // If no application ID is provided, return an error
    echo json_encode([
        'success' => false,
        'message' => 'Application ID not provided'
    ]);
}

// Close the database connection
$mysqli->close();

