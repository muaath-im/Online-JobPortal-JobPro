    </main>
    <footer class="jp-footer">
        <div class="jp-container">
            <div class="jp-footer__top">
                <div class="jp-footer__intro">
                    <a class="jp-brand" href="/jobpro/index.php" aria-label="JobPro home">
                        <img src="/jobpro/assets/images/jobpro-mark.svg" width="38" height="38" alt="">
                        <span>JobPro<span class="jp-brand__dot">.</span></span>
                    </a>
                    <p>A little ambition.<br> A world of possibility.</p>
                </div>
                <div class="jp-footer__group">
                    <h2>Make your next move</h2>
                    <ul>
                        <li><a href="/jobpro/pages/seekJob.php">Find a job</a></li>
                        <li><a href="/jobpro/pages/register.php">Create a personal account</a></li>
                        <li><a href="/jobpro/pages/account.php">Your account</a></li>
                    </ul>
                </div>
                <div class="jp-footer__group">
                    <h2>Grow your team</h2>
                    <ul>
                        <li><a href="/jobpro/pages/createJob.php">Post a job</a></li>
                        <li><a href="/jobpro/pages/comRegister.php">Create a company account</a></li>
                    </ul>
                </div>
                <div class="jp-footer__group">
                    <h2>Get to know us</h2>
                    <ul>
                        <li><a href="/jobpro/pages/about.php">About JobPro</a></li>
                        <li><a href="/jobpro/pages/contact.php">Contact us</a></li>
                    </ul>
                </div>
            </div>
            <div class="jp-footer__bottom">
                <p>&copy; <?= date('Y') ?> JobPro. All rights reserved.</p>
                <ul aria-label="Legal">
                    <li><a href="/jobpro/pages/terms.php">Terms &amp; conditions</a></li>
                    <li><a href="/jobpro/pages/privacy.php">Privacy policy</a></li>
                </ul>
                <a class="jp-back-top" href="#main-content">Back to top <span aria-hidden="true">↑</span></a>
            </div>
        </div>
    </footer>
    <script src="<?= $shellEscape($shellAsset('assets/js/script.js')) ?>" defer></script>
</body>
</html>
