<?php 
// Include database connection
require_once '../includes/dbconnect.php';

// Check if the job_id is set and is a valid integer
if (isset($_POST['job_id']) && is_numeric($_POST['job_id'])) {
    $job_id = $_POST['job_id'];

    // Prepare the delete statement
    $delete_job = "DELETE FROM jobs WHERE id = ?";
    if ($stmt = $mysqli->prepare($delete_job)) {
        $stmt->bind_param('i', $job_id); // Bind the job_id as an integer parameter
        $stmt->execute();
        
        // Check if the job was deleted
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Job deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No job found with that ID or job already deleted']);
        }

        $stmt->close(); // Close the statement
    } else {
        echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $mysqli->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
}

$mysqli->close(); // Close the database connection
