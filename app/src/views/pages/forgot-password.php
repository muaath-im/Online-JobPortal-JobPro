<?php
require_once jobproPath('src/views/auth-ui.php');require_once jobproPath('src/Core/recovery.php');jobproStartSession();
$state=jobproFormState('recovery');$flash=jobproTakeFlash();$config=jobproMailConfig();
$pageTitle='Recover your account — JobPro';include jobproPath('src/views/header.php');
?>
<div class="jp-container jp-auth-single"><section class="jp-auth-card" aria-labelledby="auth-title"><a class="jp-text-link" href="/pages/login.php">← Back to sign in</a><p class="jp-eyebrow">A FRESH START</p><h1 id="auth-title">Let’s get you back in.</h1><p class="jp-auth-lead">Enter the email address connected to your account. Reset links expire in 30 minutes and can be used once.</p>
<?php jobproRenderFlash($flash);jobproErrors($state['errors']); ?>
<?php if(!jobproMailReady()): ?><div class="jp-alert jp-alert--warning">Email recovery is not enabled on this installation yet. Contact the project owner for account-recovery help.</div><?php endif; ?>
<?php if($config['transport']==='log'): ?><div class="jp-alert jp-alert--warning">Local email preview is active. Recovery messages are saved privately on this computer; they are not delivered to an inbox.</div><?php endif; ?>
<form action="/api/request_reset.php" method="post" data-auth-form><?= jobproCsrfField() ?><?php jobproField('email','Email address',$state,['type'=>'email','required'=>true,'maxlength'=>255,'autocomplete'=>'email']); ?><button class="jp-button jp-auth-submit" type="submit" data-pending-label="Requesting link…">Request reset link <span aria-hidden="true">→</span></button></form><p class="jp-auth-switch">Remembered your password? <a href="/pages/login.php">Sign in</a></p></section></div>
<script src="<?= $shellEscape($shellAsset('assets/js/auth.js')) ?>" defer></script>
<?php include jobproPath('src/views/footer.php'); ?>
