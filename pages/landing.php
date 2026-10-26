<?php
session_start();
$pageTitle = 'JobPro — Your next chapter starts here';
$pageDescription = 'Take the next step in your career. Explore opportunities, create your profile, or connect your company with new talent on JobPro.';
$pagename = 'landing';
include '../includes/header.php';
?>

<section class="jp-hero" aria-labelledby="hero-heading">
    <div class="jp-container jp-hero__grid">
        <div class="jp-hero__copy">
            <p class="jp-eyebrow"><span class="jp-status-dot" aria-hidden="true"></span> BIG AMBITION. FRESH BEGINNINGS.</p>
            <h1 id="hero-heading">Your next chapter<br>starts with<br><em>better work.</em></h1>
            <p class="jp-hero__description">A first role. A new direction. A team that feels right.<br class="jp-desktop-break"> Wherever you want to go, take your next step with JobPro.</p>
            <div class="jp-hero__actions">
                <a class="jp-button" href="/jobpro/pages/seekJob.php">Explore opportunities <span aria-hidden="true">↗</span></a>
                <a class="jp-text-link" href="#how-it-works">How it works <span aria-hidden="true">↓</span></a>
            </div>
            <div class="jp-hero__note"><span aria-hidden="true">✦</span> For the work you do. And the person you want to become.</div>
        </div>
        <div class="jp-hero__art" aria-hidden="true">
            <div class="jp-art__topline"><span>A WORLD OF POSSIBILITY</span><span>↗</span></div>
            <svg class="jp-career-art" viewBox="0 0 560 540" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="art-grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M40 0H0V40" stroke="var(--primary)" stroke-opacity=".07"/></pattern>
                    <clipPath id="art-arch"><path d="M111 510V226a169 169 0 0 1 338 0v284Z"/></clipPath>
                </defs>
                <path fill="url(#art-grid)" d="M0 0h560v540H0z"/>
                <circle cx="433" cy="129" r="73" fill="var(--accent)"/>
                <path d="M111 510V226a169 169 0 0 1 338 0v284Z" fill="var(--primary)"/>
                <g clip-path="url(#art-arch)">
                    <path d="M153 510V230a127 127 0 0 1 254 0v280M195 510V230a85 85 0 0 1 170 0v280M237 510V230a43 43 0 0 1 86 0v280" stroke="var(--primary-tint)" stroke-opacity=".3"/>
                    <path d="M72 489h105v-75h91v-75h91v-75h120" stroke="var(--surface)" stroke-width="46" stroke-linejoin="miter"/>
                    <path d="M76 514h102v-75h91v-75h91v-75h119" stroke="var(--border)" stroke-width="4"/>
                </g>
                <path d="m279 75 8 21 21 8-21 8-8 21-8-21-21-8 21-8 8-21Z" fill="var(--on-primary)"/>
                <path d="M411 211v-41m0 0h-41m41 0-57 57" stroke="var(--on-primary)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M70 292h47M93 269v47" stroke="var(--primary)" stroke-width="2"/>
                <circle cx="472" cy="397" r="7" fill="var(--primary)"/>
                <circle cx="77" cy="417" r="7" stroke="var(--primary)" stroke-width="2"/>
                <path d="M54 510h453" stroke="var(--primary)" stroke-width="2"/>
            </svg>
            <div class="jp-art__tag"><span class="jp-art__tag-icon">↗</span><span>Small steps.<br><strong>New possibilities.</strong></span></div>
            <div class="jp-art__caption"><span>YOUR FUTURE IS STILL UNWRITTEN.</span><span>LET’S GET STARTED.</span></div>
        </div>
    </div>
</section>

<div class="jp-promise-strip">
    <div class="jp-container jp-promise-strip__inner">
        <p>Different paths.<br><strong>The same possibility.</strong></p>
        <span>First-time job seekers</span><span aria-hidden="true">✳</span><span>Experienced professionals</span><span aria-hidden="true">✳</span><span>Growing teams</span>
    </div>
</div>

<section class="jp-section jp-pathways" aria-labelledby="pathways-heading">
    <div class="jp-container">
        <div class="jp-section-heading">
            <div><p class="jp-eyebrow">FIND YOUR WAY FORWARD</p><h2 id="pathways-heading">Two ambitions.<br><em>One place to begin.</em></h2></div>
            <p>Good work starts with a connection.<br>Let’s help you make yours.</p>
        </div>
        <div class="jp-pathway-grid">
            <article class="jp-pathway jp-pathway--candidate">
                <div class="jp-pathway__top"><span class="jp-pill">FOR JOB SEEKERS</span><span class="jp-pathway__symbol" aria-hidden="true">↗</span></div>
                <h3>Bring your ambition.<br>Find your next opportunity.</h3>
                <p>Explore available roles, introduce yourself, and take the next step in your career.</p>
                <ul class="jp-check-list"><li>Create your personal profile</li><li>Explore roles from employers</li><li>Apply and revisit your applications</li></ul>
                <a class="jp-button jp-button--outline" href="/jobpro/pages/register.php">Start your journey <span aria-hidden="true">↗</span></a>
            </article>
            <article class="jp-pathway jp-pathway--employer">
                <div class="jp-pathway__top"><span class="jp-pill">FOR EMPLOYERS</span><span class="jp-pathway__symbol" aria-hidden="true">✳</span></div>
                <h3>Your next chapter<br>starts with your next hire.</h3>
                <p>Give your opportunities a home. Share a role and introduce job seekers to your company.</p>
                <ul class="jp-check-list"><li>Create your company account</li><li>Publish your job opportunities</li><li>Manage and update your listings</li></ul>
                <a class="jp-button" href="/jobpro/pages/comRegister.php">Get started as an employer <span aria-hidden="true">↗</span></a>
            </article>
        </div>
    </div>
</section>

<section id="how-it-works" class="jp-section jp-how" aria-labelledby="how-heading">
    <div class="jp-container">
        <div class="jp-section-heading">
            <div><p class="jp-eyebrow">A LITTLE DIRECTION GOES A LONG WAY</p><h2 id="how-heading">Your next move,<br><em>made a little simpler.</em></h2></div>
            <a class="jp-text-link" href="/jobpro/pages/register.php">Take the first step <span aria-hidden="true">↗</span></a>
        </div>
        <ol class="jp-steps">
            <li><span class="jp-step-number" aria-hidden="true">01</span><h3>Introduce yourself.</h3><p>Create a personal account and add your experience, qualifications, and portfolio link.</p></li>
            <li><span class="jp-step-number" aria-hidden="true">02</span><h3>See what’s out there.</h3><p>Log in to explore available roles, read the descriptions, and find an opportunity worth a closer look.</p></li>
            <li><span class="jp-step-number" aria-hidden="true">03</span><h3>Make your move.</h3><p>Apply to a role and revisit your applications. Add a cover letter to tell more of your story.</p></li>
        </ol>
    </div>
</section>

<section class="jp-section jp-faq" aria-labelledby="faq-heading">
    <div class="jp-container jp-faq__grid">
        <div><p class="jp-eyebrow">BEFORE YOU BEGIN</p><h2 id="faq-heading">A few things<br><em>you might wonder.</em></h2><p>Have another question?</p><a class="jp-text-link" href="/jobpro/pages/contact.php">Let’s talk <span aria-hidden="true">↗</span></a></div>
        <div class="jp-faq__items">
            <details><summary>How do I explore jobs?<span aria-hidden="true">+</span></summary><p>Create a personal account and log in, then visit Find a job to explore the available listings.</p></details>
            <details><summary>Which account should I create?<span aria-hidden="true">+</span></summary><p>Choose a personal account if you’re looking for work. Choose a company account if you want to publish and manage job opportunities.</p></details>
            <details><summary>Can I update an application?<span aria-hidden="true">+</span></summary><p>Yes. After applying, visit Your Applications on the Find a job page. You can edit your cover letter or delete an application.</p></details>
            <details><summary>How do I post a job?<span aria-hidden="true">+</span></summary><p>Register a company account and log in. Select For employers to add a role, then manage your listings from Your Posted Jobs.</p></details>
        </div>
    </div>
</section>

<section class="jp-closing" aria-labelledby="closing-heading">
    <div class="jp-container jp-closing__inner">
        <div><p class="jp-eyebrow">THE NEXT CHAPTER IS YOURS</p><h2 id="closing-heading">Let’s see where<br><em>your ambition takes you.</em></h2></div>
        <a class="jp-button jp-button--light" href="/jobpro/pages/register.php">Make your next move <span aria-hidden="true">↗</span></a>
        <span class="jp-closing__star" aria-hidden="true">✳</span>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
