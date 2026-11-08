<?php
require_once jobproPath('src/Core/auth.php');
jobproStartSession();
$pageTitle = "Terms and Conditions — JobPro";
include jobproPath('src/views/header.php');
?>

<div class="jp-container jp-content-page">
    <p class="jp-eyebrow">TERMS OF SERVICE</p>
    <h1>Terms and Conditions</h1>

    <section class="jp-card" style="margin-bottom: 2rem;">
        <h2>1. Agreement to Terms</h2>
        <p>Welcome to JobPro. By creating an account, browsing listings, or submitting applications, you agree to be
            bound by these Terms and Conditions and our Privacy Policy.</p>
    </section>

    <section class="jp-card" style="margin-bottom: 2rem;">
        <h2>2. Permitted Use</h2>
        <p>JobPro is provided for lawful recruitment, career networking, and employment applications. Users agree not to
            post deceptive, discriminatory, or fraudulent opportunities or application materials.</p>
    </section>

    <section class="jp-card" style="margin-bottom: 2rem;">
        <h2>3. Account Responsibilities</h2>
        <p>You are responsible for maintaining the confidentiality of your login credentials and for all activities that
            occur under your account. You agree to notify JobPro immediately of any unauthorized access.</p>
    </section>

    <section class="jp-card" style="margin-bottom: 2rem;">
        <h2>4. Content Moderation & Termination</h2>
        <p>JobPro reserves the right to review, hide, or remove job listings that violate community standards, and to
            suspend or terminate accounts that engage in fraudulent behavior.</p>
    </section>

    <section class="jp-card">
        <h2>5. Limitation of Liability</h2>
        <p>JobPro does not guarantee employment offers or candidate availability. For inquiries regarding these terms,
            please contact our support team through our <a href="/pages/contact.php" class="jp-text-link">contact
                page</a>.</p>
    </section>
</div>

<?php include jobproPath('src/views/footer.php'); ?>
