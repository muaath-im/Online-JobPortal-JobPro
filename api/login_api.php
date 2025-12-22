<?php
session_start();
require_once '../includes/dbconnect.php'; // Ensure you have the database connection

$error = ''; // Initialize an error variable to hold error messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect input data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare SQL statement to fetch user data
    $stmt = $mysqli->prepare("SELECT user_id, password, account_type FROM user WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if a user with the provided username exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $hashedPassword, $accountType);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashedPassword)) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['account_type'] = $accountType;

            if ($accountType == "company") {
                // get the company_id from the company table and store it in session
                $stmt = $mysqli->prepare("SELECT company_id FROM company WHERE user_id = ?");
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($companyId);
                $stmt->fetch();
                $_SESSION['company_id'] = $companyId;
            } elseif ($accountType == "personal") {
                // get the person_id from the person table and store it in session
                $stmt = $mysqli->prepare("SELECT person_id FROM person WHERE user_id = ?");
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($personId);
                $stmt->fetch();
                $_SESSION['person_id'] = $personId;
            }

            // Redirect to the account page
            header('Location: ../pages/account.php');
            exit();
        } else {
            // Password is incorrect
            $error = "Invalid password. Please try again.";
        }
    } else {
        // No user found with the provided username
        $error = "No user found with that username.";
    }

    // Close the statement
    $stmt->close();
    // Close the database connection
    $mysqli->close();
}

// If there's an error, use JavaScript to alert and reload the login page
if (!empty($error)) {
    echo '<script type="text/javascript">
            alert("' . addslashes($error) . '");
            window.location.href = "/jobpro/pages/login.php"; // Adjust the path if needed
          </script>';
    exit();
}
