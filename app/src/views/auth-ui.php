<?php
require_once jobproPath('src/Core/auth.php');
function jobproRenderFlash(?array $flash): void {
    if(!$flash)return;
    $error=($flash['type']??'')==='error';
    echo '<div class="jp-alert '.($error?'jp-alert--error':'jp-alert--success').'" role="'.($error?'alert':'status').'" data-page-flash="'.jobproEscape($flash['message']).'" data-page-flash-type="'.($error?'error':'success').'">'.jobproEscape($flash['message']).'</div>';
}
function jobproErrors(array $errors): void {
    if(!$errors)return;
    echo '<div class="jp-alert jp-alert--error jp-error-summary" role="alert" tabindex="-1" data-error-summary><h2>Please check the following</h2><ul>';
    foreach($errors as $field=>$message)echo '<li>'.($field!=='form'?'<a href="#'.jobproEscape($field).'">'.jobproEscape($message).'</a>':jobproEscape($message)).'</li>';
    echo '</ul></div>';
}
function jobproField(string $name,string $label,array $state,array $attributes=[]): void {
    $type=$attributes['type']??'text';unset($attributes['type']);
    $error=$state['errors'][$name]??null;$value=$type==='password'?'':($state['old'][$name]??'');
    if($error)$attributes['aria-describedby']=trim(($attributes['aria-describedby']??'').' '.$name.'-error');
    echo '<div class="jp-auth-field"><label for="'.jobproEscape($name).'">'.jobproEscape($label).'</label>';
    if($type==='password')echo '<div class="jp-password-control">';
    echo '<input class="jp-input" type="'.jobproEscape($type).'" id="'.jobproEscape($name).'" name="'.jobproEscape($name).'" value="'.jobproEscape($value).'"';
    foreach($attributes as $key=>$val){if(is_bool($val)){if($val)echo ' '.jobproEscape($key);}else echo ' '.jobproEscape($key).'="'.jobproEscape($val).'"';}
    if($error)echo ' aria-invalid="true"';
    echo '>';
    if($type==='password')echo '<button type="button" class="jp-password-toggle" data-password-toggle="'.jobproEscape($name).'" aria-controls="'.jobproEscape($name).'" aria-pressed="false" aria-label="Show '.jobproEscape(strtolower($label)).'" hidden>Show</button></div>';
    if($error)echo '<p class="jp-field-error" id="'.jobproEscape($name).'-error">'.jobproEscape($error).'</p>';
    echo '</div>';
}
function jobproPasswordHint(): void { echo '<p class="jp-auth-hint" id="password-policy">Use a passphrase of 15–128 characters. Spaces are welcome; avoid reusing passwords.</p>'; }
function jobproAuthAside(string $eyebrow,string $title,string $description): void {
    echo '<aside class="jp-auth-story"><span class="jp-badge">'.jobproEscape($eyebrow).'</span><h2>'.jobproEscape($title).'</h2><p>'.jobproEscape($description).'</p><div class="jp-auth-story-steps"><div><span>01</span><p><strong>Make your introduction.</strong><br>A personal profile or a company story.</p></div><div><span>02</span><p><strong>Find your next connection.</strong><br>Explore roles or share an opportunity.</p></div><div><span>03</span><p><strong>Keep moving forward.</strong><br>Come back to manage your next steps.</p></div></div><a href="/pages/seekJob.php">Explore opportunities first <span aria-hidden="true">→</span></a></aside>';
}
