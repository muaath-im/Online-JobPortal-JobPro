<?php
require_once jobproPath('src/views/auth-ui.php');require_once jobproPath('src/Core/recovery.php');jobproStartSession();
header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');header('X-Robots-Tag: noindex, nofollow');
$token=is_string($_GET['token']??null)?$_GET['token']:'';$valid=jobproResetValid($token);$state=jobproFormState('reset');$flash=jobproTakeFlash();
$pageTitle='Choose a new password — JobPro';include jobproPath('src/views/header.php');
?>
<div class="jp-container jp-auth-single"><section class="jp-auth-card" aria-labelledby="auth-title"><p class="jp-eyebrow">A FRESH START</p><?php jobproRenderFlash($flash); ?>
<?php if($valid): ?><h1 id="auth-title">Choose your new password.</h1><p class="jp-auth-lead">Use a memorable passphrase that you don’t use elsewhere.</p><?php jobproErrors($state['errors']); ?><form action="/api/reset_password.php" method="post" data-auth-form><?= jobproCsrfField() ?><input type="hidden" name="token" value="<?= jobproEscape($token) ?>"><?php jobproField('password','New password',$state,['type'=>'password','required'=>true,'minlength'=>15,'maxlength'=>128,'autocomplete'=>'new-password','aria-describedby'=>'password-policy']);jobproPasswordHint();jobproField('confirm-password','Confirm new password',$state,['type'=>'password','required'=>true,'autocomplete'=>'new-password','data-confirm-for'=>'password']); ?><button class="jp-button jp-auth-submit" type="submit" data-pending-label="Resetting password…">Reset password</button></form>
<?php else: ?><h1 id="auth-title">This link is no longer available.</h1><p class="jp-auth-lead">It may have expired, already been used, or been replaced by a password change.</p><a class="jp-button jp-auth-submit" href="/pages/forgot-password.php">Request a new reset link</a><?php endif; ?>
<p class="jp-auth-switch"><a href="/pages/login.php">Back to sign in</a></p></section></div>
<script src="<?= $shellEscape($shellAsset('assets/js/auth.js')) ?>" defer></script>
<?php include jobproPath('src/views/footer.php'); ?>
