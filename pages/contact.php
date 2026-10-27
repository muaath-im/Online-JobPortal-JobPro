<?php 
session_start();
$pageTitle = "Contact Us";
include '../includes/header.php'; 
?>

<div class="container contact-page">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']); // Clear message after displaying
            ?>
        </div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']); // Clear message after displaying
            ?>
        </div>
    <?php endif; ?>
    
    <div class="contact-form-section">
        <h2>LEAVE A MESSAGE</h2>
        <form class="contact-form" action="/api/process_contact.php" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>
            </div>
            <div class="form-group">
                <label for="message">Type Your Message</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="submit-button">Submit</button>
        </form>

        <section class="faq-section">
            <h2>FAQ</h2>
            <div class="faq-item">
                <h3>Question 01</h3>
                <p>Answer to question 01 goes here.</p>
            </div>
            <div class="faq-item">
                <h3>Question 02</h3>
                <p>Answer to question 02 goes here.</p>
            </div>
            <!-- Add FAQ items -->

        </section>
    </div>

    <aside class="contact-info-sidebar">
        <h2>CONTACT INFO:</h2>
        <ul>
            <li><i class="icon-location"></i> Colombo - 00600, Western Province, Sri Lanka</li>
            <li><i class="icon-phone"></i> +94 779999999</li>
            <li><i class="icon-email"></i> jobpro@exaple.com</li>
        </ul>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc maximus, nulla ut commodo sagittis.</p>
        <div class="social-links">
            <i class="fa-brands fa-facebook"></i>
            <i class="fa-brands fa-instagram"></i>
            <i class="fa-brands fa-x-twitter"></i>
            <i class="fa-brands fa-linkedin"></i>
        </div>
    </aside>
</div>

<?php include '../includes/footer.php'; ?>
