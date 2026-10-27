    </main>
    <footer class="jp-footer">
        <div class="jp-container">
            <div class="jp-footer__top">
                <div class="jp-footer__intro">
                    <a class="jp-brand" href="<?= $shellEscape($shellUrl('index.php')) ?>" aria-label="JobPro home">
                        <img src="<?= $shellEscape($shellAsset('assets/images/jobpro-mark.svg')) ?>" width="38" height="38" alt="">
                        <span>JobPro</span>
                    </a>
                    <p>Connecting people with possibilities.<br> Your next move starts here.</p>
                </div>
                <div class="jp-footer__group">
                    <h2>For job seekers</h2>
                    <ul>
                        <li><a href="<?= $shellEscape($shellUrl('pages/seekJob.php')) ?>">Find a job</a></li>
                        <li><a href="<?= $shellEscape($shellUrl('pages/applications.php')) ?>">Your applications</a></li>
                        <li><a href="<?= $shellEscape($shellUrl('pages/register.php')) ?>">Create a personal account</a></li>
                        <li><a href="<?= $shellEscape($shellUrl('pages/account.php')) ?>">Your account</a></li>
                    </ul>
                </div>
                <div class="jp-footer__group">
                    <h2>For employers</h2>
                    <ul>
                        <li><a href="<?= $shellEscape($shellUrl('pages/createJob.php')) ?>">Post a job</a></li>
                        <li><a href="<?= $shellEscape($shellUrl('pages/comRegister.php')) ?>">Create a company account</a></li>
                    </ul>
                </div>
                <div class="jp-footer__group">
                    <h2>JobPro</h2>
                    <ul>
                        <li><a href="<?= $shellEscape($shellUrl('pages/about.php')) ?>">About JobPro</a></li>
                        <li><a href="<?= $shellEscape($shellUrl('pages/contact.php')) ?>">Contact us</a></li>
                    </ul>
                </div>
            </div>
            <div class="jp-footer__bottom">
                <p>&copy; <?= date('Y') ?> JobPro. All rights reserved.</p>
                <ul aria-label="Legal">
                    <li><a href="<?= $shellEscape($shellUrl('pages/terms.php')) ?>">Terms &amp; conditions</a></li>
                    <li><a href="<?= $shellEscape($shellUrl('pages/privacy.php')) ?>">Privacy policy</a></li>
                </ul>
                <a class="jp-back-top" href="#main-content">Back to top <span aria-hidden="true">↑</span></a>
            </div>
        </div>
    </footer>
    <script src="<?= $shellEscape($shellAsset('assets/js/script.js')) ?>" defer></script>
</body>
</html>
