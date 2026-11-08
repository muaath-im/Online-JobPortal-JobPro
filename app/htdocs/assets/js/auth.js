(() => {
    'use strict';
    document.querySelectorAll('[data-password-toggle]').forEach(button => {
        const input=document.getElementById(button.dataset.passwordToggle);
        if(!input)return;
        button.hidden=false;
        button.addEventListener('click',()=>{
            const visible=input.type==='password';input.type=visible?'text':'password';
            button.textContent=visible?'Hide':'Show';button.setAttribute('aria-pressed',String(visible));
            button.setAttribute('aria-label',(visible?'Hide ':'Show ')+(input.labels?.[0]?.textContent||'password').toLowerCase());
        });
    });
    document.querySelectorAll('[data-confirm-for]').forEach(input=>{
        const password=document.getElementById(input.dataset.confirmFor);
        if(!password)return;
        const validate=()=>input.setCustomValidity(input.value&&input.value!==password.value?'The passwords do not match.':'');
        input.addEventListener('input',validate);password.addEventListener('input',validate);
    });
    document.querySelector('[data-error-summary]')?.focus();
    const avatar=document.querySelector('[data-avatar-input]');
    const preview=document.querySelector('[data-avatar-preview]');
    const feedback=document.querySelector('[data-avatar-feedback]');
    let previewUrl;
    preview?.addEventListener('error',()=>{
        preview.hidden=true;
        avatar.setCustomValidity('This image could not be previewed. Choose another image.');
        feedback.textContent='This image could not be previewed. Choose another image.';
    });
    avatar?.addEventListener('change',()=>{
        if(previewUrl)URL.revokeObjectURL(previewUrl);
        preview.hidden=true;avatar.setCustomValidity('');
        const file=avatar.files[0];
        if(!file){feedback.textContent='No new image selected.';return;}
        if(!['image/jpeg','image/png','image/webp'].includes(file.type)||file.size>2*1024*1024){avatar.setCustomValidity('Choose a JPG, PNG, or WebP image up to 2 MB.');feedback.textContent='Choose a JPG, PNG, or WebP image up to 2 MB.';return;}
        previewUrl=URL.createObjectURL(file);preview.src=previewUrl;preview.hidden=false;feedback.textContent=file.name+' selected. Save your profile to upload it.';
    });
    const restoreButtons=()=>document.querySelectorAll('[data-pending-label]').forEach(button=>{button.disabled=false;if(button.dataset.originalLabel)button.textContent=button.dataset.originalLabel;});
    window.addEventListener('pageshow',restoreButtons);
    document.querySelectorAll('[data-auth-form]').forEach(form=>form.addEventListener('submit',()=>{
        const button=form.querySelector('[data-pending-label]');
        if(button){button.dataset.originalLabel=button.textContent;button.textContent=button.dataset.pendingLabel;button.disabled=true;}
    }));
})();
