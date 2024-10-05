<?php
session_start();
require_once '../includes/dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $registerType = $_POST['registerType']; // 'Person' or 'Company'
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = $_POST['phone'];
    $profileImage = $_FILES['profile-image']['name']; // Handling file upload
    $uploadDir = '/uploads/';
    
    // Move uploaded file to the desired directory
    if (!empty($profileImage)) {
        move_uploaded_file($_FILES['profile-image']['tmp_name'], $uploadDir . basename($profileImage));
    }

    // Start a transaction
    $mysqli->begin_transaction();

    try {
        // Insert into user table
        $stmt = $mysqli->prepare("INSERT INTO user (username, email, password, phone_number, account_type, profile_image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssssss', $username, $email, $password, $phone, $registerType, $profileImage);
        $stmt->execute();
        
        $user_id = $stmt->insert_id; // Get the last inserted user ID

        if ($registerType == 'Personal') {
            // Insert into person table
            $fullname = $_POST['fullname'];
            $qualification = $_POST['qualification'];
            $experience = $_POST['experience'];
            $location = $_POST['location'];
            $city = $_POST['city'];
            $zipcode = $_POST['zipcode'];
            $country = $_POST['country'];
            $portfolio = $_POST['portfolio'];
            $cv = $_POST['cv'];

            $stmt = $mysqli->prepare("INSERT INTO person (user_id, full_name, qualification, year_of_experience, location, city, zip_code, country, portfolio_link, cv_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ississssss', $user_id, $fullname, $qualification, $experience, $location, $city, $zipcode, $country, $portfolio, $cv);
            $stmt->execute();
            
        } elseif ($registerType == 'Company') {
            // Insert into company table
            $companyName = $_POST['companyName'];
            $website = $_POST['website'];
            $location = $_POST['location'];

            $stmt = $mysqli->prepare("INSERT INTO company (user_id, company_name, website_link, address) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isss', $user_id, $companyName, $website, $location);
            $stmt->execute();
        }

        // Commit the transaction
        $mysqli->commit();
        echo "Registration successful!";
        header('Location: ../pages/account.php');
    } catch (Exception $e) {
        // Rollback transaction if an error occurs
        $mysqli->rollback();
        die("Error: " . $e->getMessage());
    }

    // Close the statement and connection
    $stmt->close();
}

// Function to fetch user data
function getUserData($userId) {
    global $mysqli; // Use the global mysqli connection
    $stmt = $mysqli->prepare("SELECT username, email, phone, profile_image, bio, achievement FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc(); // Return user data as an associative array
}

// Handling GET request for user data
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $userData = getUserData($userId);
    echo json_encode($userData); // Return user data as JSON
}

// Handling POST request for updating account information
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_info'])) {
    $userId = $_SESSION['user_id'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    // Update user info in the database
    $stmt = $mysqli->prepare("UPDATE users SET phone = ?, email = ? WHERE id = ?");
    $stmt->bind_param('ssi', $phone, $email, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Account information updated successfully!"]);
    } else {
        echo json_encode(["message" => "No changes made or update failed."]);
    }

    $stmt->close();
}

// Handling POST request for changing password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $userId = $_SESSION['user_id'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Fetch the existing password from the database
    $stmt = $mysqli->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Verify current password
    if (password_verify($currentPassword, $row['password'])) {
        if ($newPassword === $confirmPassword) {
            $newPasswordHashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $newPasswordHashed, $userId);
            $stmt->execute();
            echo json_encode(["message" => "Password changed successfully!"]);
        } else {
            echo json_encode(["message" => "New passwords do not match."]);
        }
    } else {
        echo json_encode(["message" => "Current password is incorrect."]);
    }

    $stmt->close();
}

// Close the database connection
$mysqli->close();

