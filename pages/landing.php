<?php
session_start();
$pageTitle = 'JobPro — Find your next opportunity';
$pageDescription = 'A clear next step for your career or your team. Explore opportunities, introduce yourself, and connect through JobPro.';
$pagename = 'landing';
include '../includes/header.php';
?>

<section class="jp-hero" aria-labelledby="hero-heading">
    <div class="jp-container">
        <p class="jp-hero__badge"><span aria-hidden="true"></span> Your next move starts here</p>
        <h1 id="hero-heading">Your ambition.<br><span>A world of opportunity.</span></h1>
        <p class="jp-hero__description">Find a role that fits your skills. Connect with people who move your team forward. Whatever comes next, start with JobPro.</p>
        <div class="jp-hero__actions">
            <a class="jp-button" href="<?= $shellEscape($shellUrl('pages/seekJob.php')) ?>">Explore opportunities <?= $shellIcon('arrow') ?></a>
            <a class="jp-button jp-button--outline" href="#how-it-works">See how it works</a>
        </div>
        <ul class="jp-hero__highlights" aria-label="Explore JobPro">
            <li><?= $shellIcon('check') ?> Personal &amp; company accounts</li>
            <li><?= $shellIcon('check') ?> Job opportunities</li>
            <li><?= $shellIcon('check') ?> Applications in one place</li>
        </ul>
    </div>
</section>

<section class="jp-start jp-container" aria-labelledby="start-heading" data-intent-chooser>
    <div class="jp-start__toolbar">
        <div class="jp-start__label"><span class="jp-start__mark" aria-hidden="true"><?= $shellIcon('briefcase') ?></span><div><h2 id="start-heading">A starting point for every ambition</h2><p>Choose the path that fits you.</p></div></div>
        <div class="jp-intent-switch" role="group" aria-label="Choose your starting point">
            <button type="button" data-intent="candidate" aria-pressed="true" aria-controls="candidate-path"><?= $shellIcon('person') ?> Job seekers</button>
            <button type="button" data-intent="employer" aria-pressed="false" aria-controls="employer-path"><?= $shellIcon('building') ?> Employers</button>
        </div>
    </div>
    <div class="jp-intent-panel" id="candidate-path" data-intent-panel="candidate">
        <div class="jp-intent-copy"><span class="jp-eyebrow">YOUR CAREER, YOUR DIRECTION</span><h3>Make room for<br>your next possibility.</h3><p>Bring your experience, curiosity, and ambition. Start a profile and explore the roles that could shape your next chapter.</p><a class="jp-text-link" href="<?= $shellEscape($shellUrl('pages/register.php')) ?>">Create your personal account <?= $shellIcon('arrow') ?></a></div>
        <ol class="jp-workflow" aria-label="Getting started as a job seeker">
            <li><span class="jp-workflow__icon"><?= $shellIcon('person') ?></span><div><h4>Introduce yourself</h4><p>Your qualifications, experience, and portfolio.</p></div><span class="jp-workflow__number" aria-hidden="true">01</span></li>
            <li><span class="jp-workflow__icon"><?= $shellIcon('briefcase') ?></span><div><h4>Explore your options</h4><p>Read roles, locations, and salary information.</p></div><span class="jp-workflow__number" aria-hidden="true">02</span></li>
            <li><span class="jp-workflow__icon"><?= $shellIcon('document') ?></span><div><h4>Take the next step</h4><p>Apply and revisit your applications.</p></div><span class="jp-workflow__number" aria-hidden="true">03</span></li>
        </ol>
    </div>
    <div class="jp-intent-panel" id="employer-path" data-intent-panel="employer">
        <div class="jp-intent-copy"><span class="jp-eyebrow">GOOD TEAMS START WITH CONNECTION</span><h3>Your next opportunity<br>is someone’s next move.</h3><p>Introduce your company, share the role you’re hiring for, and give your next team member a place to find you.</p><a class="jp-text-link" href="<?= $shellEscape($shellUrl('pages/comRegister.php')) ?>">Create your company account <?= $shellIcon('arrow') ?></a></div>
        <ol class="jp-workflow" aria-label="Getting started as an employer">
            <li><span class="jp-workflow__icon"><?= $shellIcon('building') ?></span><div><h4>Introduce your company</h4><p>Add your company name, website, and address.</p></div><span class="jp-workflow__number" aria-hidden="true">01</span></li>
            <li><span class="jp-workflow__icon"><?= $shellIcon('briefcase') ?></span><div><h4>Share an opportunity</h4><p>Describe the role, skills, and salary range.</p></div><span class="jp-workflow__number" aria-hidden="true">02</span></li>
            <li><span class="jp-workflow__icon"><?= $shellIcon('layers') ?></span><div><h4>Keep your listings up to date</h4><p>Review and edit the jobs you’ve posted.</p></div><span class="jp-workflow__number" aria-hidden="true">03</span></li>
        </ol>
    </div>
</section>

<section class="jp-section jp-paths" aria-labelledby="paths-heading">
    <div class="jp-container">
        <div class="jp-section-heading"><div><p class="jp-eyebrow">BUILT AROUND YOUR NEXT STEP</p><h2 id="paths-heading">Different goals. A clear way forward.</h2></div><p>Where you are today is just the beginning.</p></div>
        <div class="jp-path-grid">
            <a class="jp-path-card" href="<?= $shellEscape($shellUrl('pages/register.php')) ?>"><span class="jp-path-card__icon"><?= $shellIcon('person') ?></span><h3>Starting your career</h3><p>Your experience is only part of your story. Introduce your skills, education, and potential.</p><span class="jp-path-card__link">Build your profile <?= $shellIcon('arrow') ?></span></a>
            <a class="jp-path-card" href="<?= $shellEscape($shellUrl('pages/seekJob.php')) ?>"><span class="jp-path-card__icon"><?= $shellIcon('briefcase') ?></span><h3>Ready for a change</h3><p>See what’s out there. Explore opportunities that could take your career in a new direction.</p><span class="jp-path-card__link">Explore opportunities <?= $shellIcon('arrow') ?></span></a>
            <a class="jp-path-card" href="<?= $shellEscape($shellUrl('pages/createJob.php')) ?>"><span class="jp-path-card__icon jp-path-card__icon--accent"><?= $shellIcon('building') ?></span><h3>Growing your team</h3><p>Make your next role visible. Give job seekers a clear picture of the opportunity you offer.</p><span class="jp-path-card__link">Post a job <?= $shellIcon('arrow') ?></span></a>
        </div>
    </div>
</section>

<section id="how-it-works" class="jp-section jp-process" aria-labelledby="how-heading">
    <div class="jp-container">
        <div class="jp-section-heading jp-section-heading--center"><p class="jp-eyebrow">SIMPLE STEPS. REAL DIRECTION.</p><h2 id="how-heading">From introduction to opportunity.</h2><p>A straightforward way to get your next chapter moving.</p></div>
        <ol class="jp-steps">
            <li><span class="jp-step-number">01</span><h3>Create an account</h3><p>Choose a personal or company account to get started.</p></li>
            <li><span class="jp-step-number">02</span><h3>Make your introduction</h3><p>Tell your story through a personal or company profile.</p></li>
            <li><span class="jp-step-number">03</span><h3>Make a connection</h3><p>Apply to an opportunity, or publish the role you’re hiring for.</p></li>
        </ol>
    </div>
</section>

<section class="jp-section jp-faq" aria-labelledby="faq-heading">
    <div class="jp-container jp-faq__grid">
        <div><p class="jp-eyebrow">A LITTLE CLARITY</p><h2 id="faq-heading">Questions?<br>Start here.</h2><p>Everything you need to take your first step.</p><a class="jp-text-link" href="<?= $shellEscape($shellUrl('pages/contact.php')) ?>">Contact us <?= $shellIcon('arrow') ?></a></div>
        <div class="jp-faq__items">
            <details><summary>How do I explore jobs?<span aria-hidden="true">+</span></summary><p>Create a personal account and log in. Select Find a job to explore the available listings and read the role descriptions.</p></details>
            <details><summary>Which account should I create?<span aria-hidden="true">+</span></summary><p>A personal account is for finding and applying to jobs. A company account is for publishing and managing your job opportunities.</p></details>
            <details><summary>Can I update an application?<span aria-hidden="true">+</span></summary><p>On the Find a job page, Your Applications lets you edit your cover letter or delete an application you’ve submitted.</p></details>
            <details><summary>How do I post a job?<span aria-hidden="true">+</span></summary><p>Create a company account and log in. Select For employers to add a role, then manage your listings from Your Posted Jobs.</p></details>
        </div>
    </div>
</section>

<section class="jp-container jp-closing" aria-labelledby="closing-heading">
    <div class="jp-closing__copy"><span class="jp-closing__label">YOUR NEXT CHAPTER</span><h2 id="closing-heading">Ready when you are.</h2><p>A new role or your next hire. Let’s make the introduction.</p></div>
    <a class="jp-button jp-button--light" href="<?= $shellEscape($shellUrl('pages/register.php')) ?>">Get started with JobPro <?= $shellIcon('arrow') ?></a>
</section>

<?php include '../includes/footer.php'; ?>
