<?php
require_once jobproPath('src/views/auth-ui.php');jobproStartSession();
$next=jobproSafeNext($_GET['next']??null);
if(isset($_SESSION['user_id'])){jobproRequireUser();jobproRedirect($next);}
$state=jobproFormState('login');$next=jobproSafeNext($state['old']['next']??$next);$flash=jobproTakeFlash();
if(isset($_GET['logged_out']))$flash=['type'=>'success','message'=>'You have been signed out.'];
if(isset($_GET['deleted']))$flash=['type'=>'success','message'=>'Your account has been deleted.'];
$pageTitle='Welcome back — JobPro';include jobproPath('src/views/header.php');
?>
<div class="jp-container jp-auth-layout">
    <?php jobproAuthAside('YOUR NEXT CHAPTER','A familiar place. A new possibility.','Sign in to pick up where you left off. Your next move is waiting.'); ?>
    <section class="jp-auth-card" aria-labelledby="auth-title">
        <p class="jp-eyebrow">WELCOME BACK</p><h1 id="auth-title">Good to see you again.</h1><p class="jp-auth-lead">Sign in to your JobPro account.</p>
        <?php jobproRenderFlash($flash);jobproErrors($state['errors']); ?>
        <form action="/api/login_api.php" method="post" data-auth-form>
            <?= jobproCsrfField() ?><input type="hidden" name="next" value="<?= jobproEscape($next) ?>">
            <?php jobproField('username','Username',$state,['required'=>true,'autocomplete'=>'username','maxlength'=>255,'aria-describedby'=>'username-help']); ?>
            <p class="jp-auth-hint" id="username-help">Use the username you chose when registering, rather than your email address.</p>
            <?php jobproField('password','Password',$state,['type'=>'password','required'=>true,'autocomplete'=>'current-password']); ?>
            <div class="jp-auth-form-links"><span>Personal &amp; company accounts</span><a href="/pages/forgot-password.php">Forgot password?</a></div>
            <button class="jp-button jp-auth-submit" type="submit" data-pending-label="Signing in…">Sign in <span aria-hidden="true">→</span></button>
        </form>
        <p class="jp-auth-switch">New to JobPro? <a href="/pages/register.php">Create an account</a></p>
    </section>
</div>
<script src="<?= $shellEscape($shellAsset('assets/js/auth.js')) ?>" defer></script>
<?php include jobproPath('src/views/footer.php'); ?>
