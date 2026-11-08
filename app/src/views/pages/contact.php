<?php
require_once jobproPath('src/Core/auth.php');
jobproStartSession();
$pageTitle = "Contact Us — JobPro";
include jobproPath('src/views/header.php');
?>

<div class="jp-container jp-contact-layout">
    <div class="jp-card">
        <p class="jp-eyebrow">GET IN TOUCH</p>
        <h1 style="font-size: 2rem; font-weight: 800; margin: 0 0 1.5rem; color: var(--text);">Send our team a message
        </h1>

        <?php $contactFlash = jobproTakeFlash();
        if ($contactFlash): ?>
            <div class="jp-alert <?= ($contactFlash['type'] ?? '') === 'error' ? 'jp-alert--error' : 'jp-alert--success' ?>"
                role="alert" style="margin-bottom: 1.5rem;">
                <?= jobproEscape($contactFlash['message']) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="jp-alert jp-alert--success" role="status" style="margin-bottom: 1.5rem;">
                <?= jobproEscape($_SESSION['success']);
                unset($_SESSION['success']); ?>
            </div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="jp-alert jp-alert--error" role="alert" style="margin-bottom: 1.5rem;">
                <?= jobproEscape($_SESSION['error']);
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/api/process_contact.php" method="POST" style="display: grid; gap: 1.25rem;">
            <?= jobproCsrfField() ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="jp-field">
                    <label for="username">Your name or username</label>
                    <input class="jp-input" type="text" id="username" name="username" placeholder="Alex Morgan" required
                        maxlength="100">
                </div>
                <div class="jp-field">
                    <label for="email">Email address</label>
                    <input class="jp-input" type="email" id="email" name="email" placeholder="alex@example.com" required
                        maxlength="255">
                </div>
            </div>
            <div class="jp-field">
                <label for="message">Message</label>
                <textarea class="jp-input" id="message" name="message" rows="5" placeholder="How can we help you?"
                    required maxlength="10000" style="resize: vertical;"></textarea>
            </div>
            <button type="submit" class="jp-button" style="justify-self: start;">Send message <span
                    aria-hidden="true">→</span></button>
        </form>

        <section style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border);">
            <h2 style="font-size: 1.2rem; font-weight: 700; margin: 0 0 1rem; color: var(--text);">Frequently Asked
                Questions</h2>
            <div style="display: grid; gap: 1rem;">
                <div class="jp-card" style="padding: 1rem 1.25rem;">
                    <h3 style="font-size: 1rem; font-weight: 650; margin: 0 0 .35rem; color: var(--text);">How do I post
                        a job as an employer?</h3>
                    <p style="margin: 0; font-size: .88rem; color: var(--text-muted); line-height: 1.6;">Register a
                        Company account from the top navigation. Once signed in, you can post open roles, review
                        candidates, and manage your hiring pipeline.</p>
                </div>
                <div class="jp-card" style="padding: 1rem 1.25rem;">
                    <h3 style="font-size: 1rem; font-weight: 650; margin: 0 0 .35rem; color: var(--text);">Is there a
                        fee to search and apply for jobs?</h3>
                    <p style="margin: 0; font-size: .88rem; color: var(--text-muted); line-height: 1.6;">No, searching,
                        discovering, saving, and submitting applications on JobPro is completely free for candidates.
                    </p>
                </div>
            </div>
        </section>
    </div>

    <aside class="jp-contact-sidebar">
        <h2>Contact Details</h2>
        <ul>
            <li>
                <span class="jp-icon" style="color: var(--primary);">📍</span>
                <span>Colombo, Western Province, Sri Lanka</span>
            </li>
            <li>
                <span class="jp-icon" style="color: var(--primary);">📞</span>
                <span>+94 11 234 5678</span>
            </li>
            <li>
                <span class="jp-icon" style="color: var(--primary);">✉️</span>
                <span>support@jobpro.test</span>
            </li>
        </ul>
        <p style="font-size: .85rem; color: var(--text-muted); line-height: 1.6;">Our support team operates Monday
            through Friday, 9:00 AM – 5:00 PM (GMT+5:30). We aim to respond to all inquiries within 24 business hours.
        </p>
        <div class="jp-social-links" aria-label="Social media links">
            <svg class="jp-social-icon" viewBox="0 0 24 24" role="img" aria-label="Facebook">
                <path d="M14 22v-9h3l.5-4H14V7c0-1.1.3-2 2-2h2V1.4A24 24 0 0 0 15 1c-3 0-5 1.8-5 5v3H7v4h3v9z" />
            </svg>
            <svg class="jp-social-icon" viewBox="0 0 24 24" fill="none" role="img" aria-label="Instagram">
                <rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="2" />
                <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2" />
                <circle cx="17.5" cy="6.5" r="1" fill="currentColor" />
            </svg>
            <svg class="jp-social-icon" viewBox="0 0 24 24" role="img" aria-label="LinkedIn">
                <circle cx="4" cy="4" r="2" />
                <path d="M2 8h4v14H2zm7 0h4v2c1-2 3-2 4-2 4 0 5 2 5 6v8h-4v-8c0-2-1-3-2-3s-3 1-3 3v8H9z" />
            </svg>
        </div>
    </aside>
</div>

<?php include jobproPath('src/views/footer.php'); ?>
