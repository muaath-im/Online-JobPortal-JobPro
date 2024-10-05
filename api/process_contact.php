<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form input
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Validate the input
    if (!empty($username) && !empty($email) && !empty($message)) {
        // Check if the email is valid
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Prepare the email
            $to = 'miraahanafee@gmail.com'; // Change this to the recipient's email
            $subject = "Contact Form Submission from $username";
            $body = "Username: $username\nEmail: $email\n\nMessage:\n$message";
            $headers = "From: $email";

            // Send the email
            if (mail($to, $subject, $body, $headers)) {
                $_SESSION['success'] = "Your message has been sent successfully!";
            } else {
                $_SESSION['error'] = "Failed to send your message. Please try again later.";
            }
        } else {
            $_SESSION['error'] = "Invalid email address.";
        }
    } else {
        $_SESSION['error'] = "All fields are required.";
    }

    // Redirect back to the contact page
    header('Location: ../pages/contact.php');
    exit();
} else {
    // If the request method is not POST, redirect to the contact page
    header('Location: ../pages/contact.php');
    exit();
}
