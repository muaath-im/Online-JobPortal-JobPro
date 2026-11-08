<?php
require_once jobproPath('src/Core/auth.php');
jobproStartSession();
$pageTitle = "Privacy Policy — JobPro";
include jobproPath('src/views/header.php');
?>

<div class="jp-container jp-content-page">
    <p class="jp-eyebrow">LEGAL & PRIVACY</p>
    <h1>Privacy Policy</h1>

    <section class="jp-card" style="margin-bottom: 2rem;">
        <h2>1. Introduction</h2>
        <p>Your privacy is important to us. This Privacy Policy outlines how JobPro collects, uses, and protects your
            personal information when you use our web platform and services.</p>
    </section>

    <section class="jp-card" style="margin-bottom: 2rem;">
        <h2>2. Information We Collect</h2>
        <p>We collect information you provide directly to us when creating an account, posting an opportunity, or
            applying for a job:</p>
        <ul>
            <li><strong>Personal Accounts:</strong> Full name, email address, phone number, qualification, years of
                experience, location, CV/portfolio links, and optional profile image.</li>
            <li><strong>Company Accounts:</strong> Company name, contact email, phone number, address, website link, and
                job opportunity descriptions.</li>
            <li><strong>Automated Data:</strong> Timestamped authentication telemetry and security rate-limiting logs
                (stored without passwords or tokens).</li>
        </ul>
    </section>

    <section class="jp-card" style="margin-bottom: 2rem;">
        <h2>3. How We Use Your Information</h2>
        <p>We use your information strictly to:</p>
        <ul>
            <li>Facilitate applications between candidates and hiring employers.</li>
            <li>Authenticate user sessions and protect your account from unauthorized access.</li>
            <li>Allow employers to review applicants and communicate hiring status updates.</li>
        </ul>
    </section>

    <section class="jp-card" style="margin-bottom: 2rem;">
        <h2>4. Data Security & Storage</h2>
        <p>We implement strict session controls, salted password hashing (Argon2id / bcrypt), CSRF token verification,
            and isolated storage guards. Uploaded avatar images are resampled to non-executable 192×192 PNGs to protect
            system integrity.</p>
    </section>

    <section class="jp-card" style="margin-bottom: 2rem;">
        <h2>5. Your Rights & Account Deletion</h2>
        <p>You have the right to access, update, or permanently delete your account and associated personal data at any
            time via your <a href="/pages/account.php" class="jp-text-link">Account Settings</a>.</p>
    </section>

    <section class="jp-card">
        <h2>6. Policy Updates</h2>
        <p>We may update this Privacy Policy periodically. Significant changes will be announced on this page. For any
            questions regarding your data, please <a href="/pages/contact.php" class="jp-text-link">contact our
                team</a>.</p>
    </section>
</div>

<?php include jobproPath('src/views/footer.php'); ?>
