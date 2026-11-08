<?php
require_once jobproPath('src/views/auth-ui.php');jobproStartSession();
if(isset($_SESSION['user_id'])){jobproRequireUser();jobproRedirect('/pages/account.php');}
$role=$registrationRole;$company=$role==='company';$state=jobproFormState('register');$flash=jobproTakeFlash();
$pageTitle=($company?'Create a company account':'Create a personal account').' — JobPro';include jobproPath('src/views/header.php');
?>
<div class="jp-container jp-auth-layout">
    <?php jobproAuthAside($company?'GROW YOUR TEAM':'YOUR NEXT CHAPTER',$company?'Great teams start with an introduction.':'Your story belongs in the next chapter.',$company?'Introduce your company and connect your next opportunity with people ready to make a difference.':'Create your account, introduce your experience, and make room for what comes next.'); ?>
    <section class="jp-auth-card" aria-labelledby="auth-title">
        <p class="jp-eyebrow">LET’S GET STARTED</p><h1 id="auth-title"><?= $company?'Make your next hire.':'Make your next move.' ?></h1><p class="jp-auth-lead">Create a <?= $company?'company':'personal' ?> account. Complete the rest of your profile after signing in.</p>
        <nav class="jp-account-types" aria-label="Account type"><a href="/pages/register.php"<?= !$company?' aria-current="page"':'' ?>>Personal account</a><a href="/pages/comRegister.php"<?= $company?' aria-current="page"':'' ?>>Company account</a></nav>
        <?php jobproRenderFlash($flash);jobproErrors($state['errors']); ?>
        <form action="/api/auth_api.php" method="post" data-auth-form>
            <?= jobproCsrfField() ?><input type="hidden" name="registerType" value="<?= $role ?>">
            <?php jobproField($company?'companyName':'fullname',$company?'Company name':'Full name',$state,['required'=>true,'maxlength'=>255,'autocomplete'=>$company?'organization':'name']); ?>
            <?php jobproField('username','Username',$state,['required'=>true,'minlength'=>3,'maxlength'=>30,'pattern'=>'[A-Za-z0-9_][A-Za-z0-9_.-]{2,29}','autocomplete'=>'username','aria-describedby'=>'username-help']); ?>
            <p class="jp-auth-hint" id="username-help">3–30 letters, numbers, dots, hyphens, or underscores. You’ll use this to sign in.</p>
            <?php jobproField('email','Email address',$state,['type'=>'email','required'=>true,'maxlength'=>255,'autocomplete'=>'email']); ?>
            <?php jobproField('phone','Phone number (optional)',$state,['type'=>'tel','maxlength'=>20,'autocomplete'=>'tel']); ?>
            <?php jobproField('password','Password',$state,['type'=>'password','required'=>true,'minlength'=>15,'maxlength'=>128,'autocomplete'=>'new-password','aria-describedby'=>'password-policy']);jobproPasswordHint(); ?>
            <?php jobproField('confirm-password','Confirm password',$state,['type'=>'password','required'=>true,'autocomplete'=>'new-password','data-confirm-for'=>'password']); ?>
            <div class="jp-auth-consent" id="terms" tabindex="-1"><label><input type="checkbox" name="details_correct" value="1" required> <span>I confirm that these details are correct.</span></label><label><input type="checkbox" name="terms" value="1" required> <span>I agree to the <a href="/pages/terms.php" target="_blank" rel="noopener">Terms and Conditions</a> and have read the <a href="/pages/privacy.php" target="_blank" rel="noopener">Privacy Policy</a>.</span></label></div>
            <?php if(isset($state['errors']['terms'])): ?><p class="jp-field-error"><?= jobproEscape($state['errors']['terms']) ?></p><?php endif; ?>
            <button class="jp-button jp-auth-submit" type="submit" data-pending-label="Creating account…">Create <?= $company?'company':'personal' ?> account <span aria-hidden="true">→</span></button>
        </form>
        <p class="jp-auth-switch">Already have an account? <a href="/pages/login.php">Sign in</a></p>
    </section>
</div>
<script src="<?= $shellEscape($shellAsset('assets/js/auth.js')) ?>" defer></script>
<?php include jobproPath('src/views/footer.php'); ?>
