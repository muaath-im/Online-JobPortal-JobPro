<?php
require_once jobproPath('src/views/auth-ui.php');jobproStartSession();$pageTitle='Sign out — JobPro';include jobproPath('src/views/header.php');
?>
<div class="jp-container jp-auth-single"><section class="jp-auth-card"><p class="jp-eyebrow">UNTIL NEXT TIME</p><h1>Ready to sign out?</h1><p class="jp-auth-lead">You can sign back in whenever you’re ready.</p><?php jobproRenderFlash(jobproTakeFlash()); ?><form method="post" action="/api/logout.php"><?= jobproCsrfField() ?><button class="jp-button jp-auth-submit" type="submit">Sign out</button></form><p class="jp-auth-switch"><a href="/pages/account.php">Return to your account</a></p></section></div>
<?php include jobproPath('src/views/footer.php'); ?>
