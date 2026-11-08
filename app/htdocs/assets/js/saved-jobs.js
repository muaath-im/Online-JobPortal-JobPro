(() => {
    'use strict';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrf) return;
    const ids = new Set();
    let loaded = false;
    let loadFailed = false;
    let loading;
    const buttons = new Set();
    const busyIds = new Set();
    const message = (text, type = 'success') => {
        let output = document.querySelector('[data-save-message]');
        if (!output) {
            output = document.createElement('p'); output.className = 'jp-save-message';
            output.dataset.saveMessage = ''; output.setAttribute('role', 'status');
            const root = document.querySelector('[data-discovery]');
            (root?.querySelector('.jp-discovery-body') || root || document.querySelector('main')).prepend(output);
        }
        output.textContent = text;
        if (globalThis.JobProToast) {
            globalThis.JobProToast.show(text, type);
        }
    };
    const sync = () => buttons.forEach(button => {
        const id = Number(button.dataset.saveJob);
        const saved = ids.has(id);
        if (loaded) button.setAttribute('aria-pressed', String(saved));
        else button.removeAttribute('aria-pressed');
        button.disabled = busyIds.has(id) || (!loaded && !loadFailed);
        button.querySelector('span').textContent = busyIds.has(id) ? 'Updating…' : !loaded ? (loadFailed ? 'Retry saved state' : 'Loading…') : saved ? 'Saved job' : 'Save job';
        button.setAttribute('aria-label', !loaded ? `Load saved state for ${button.dataset.jobTitle}` : `${saved ? 'Remove saved' : 'Save'} ${button.dataset.jobTitle}`);
    });
    const request = async (options = {}) => {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 12000);
        try {
            const response = await fetch('/api/candidate.php', {credentials: 'same-origin', ...options,
                headers: {Accept: 'application/json', ...options.headers}, signal: controller.signal});
            if (!(response.headers.get('content-type') || '').includes('application/json')) throw new Error('Your session may have changed. Reload this page and try again.');
            const body = await response.json();
            if (!response.ok || !body.success) throw new Error(body.message || (response.status === 401 ? 'Sign in again to manage saved jobs.' : 'We could not update your saved jobs.'));
            return body;
        } finally { clearTimeout(timer); }
    };
    const load = () => {
        if (loaded) return Promise.resolve();
        if (!loading) loading = request().then(body => {
            if (!Array.isArray(body.saved_ids)) throw new Error('Saved jobs could not be loaded.');
            ids.clear();
            body.saved_ids.forEach(id => { if (Number.isSafeInteger(Number(id)) && Number(id) > 0) ids.add(Number(id)); });
            loaded = true; loadFailed = false;
        }).catch(error => { loadFailed = true; throw error; }).finally(() => { loading = null; sync(); });
        return loading;
    };
    globalThis.JobProSavedJobs = {
        button(id, title) {
            const button = document.createElement('button');
            button.type = 'button'; button.className = 'jp-save-button';
            button.dataset.saveJob = String(id); button.dataset.jobTitle = String(title);
            const icon = document.createElementNS('http://www.w3.org/2000/svg','svg');
            icon.setAttribute('width','16'); icon.setAttribute('height','16'); icon.setAttribute('viewBox','0 0 24 24');
            icon.setAttribute('fill','none'); icon.setAttribute('stroke','currentColor'); icon.setAttribute('stroke-width','1.8'); icon.setAttribute('aria-hidden','true');
            const path = document.createElementNS(icon.namespaceURI,'path'); path.setAttribute('d','M6 3h12v18l-6-4-6 4Z'); icon.append(path);
            button.append(icon, document.createElement('span')); buttons.add(button);
            sync(); load().catch(() => message('Your saved jobs could not be loaded. Try Save job again or open your saved opportunities page.'));
            button.addEventListener('click', async () => {
                const jobId = Number(id);
                if (busyIds.has(jobId)) return;
                busyIds.add(jobId); sync();
                try {
                    if (!loaded) {
                        await load();
                        message('Saved jobs refreshed. Select Save job or Saved job to make a change.');
                        return;
                    }
                    const save = !ids.has(jobId);
                    const body = new URLSearchParams({action: save ? 'save' : 'unsave',job_id: String(jobId),csrf_token: csrf});
                    await request({method:'POST',body});
                    save ? ids.add(jobId) : ids.delete(jobId);
                    message(save ? 'Opportunity saved to your account.' : 'Opportunity removed from your saved jobs.');
                } catch (error) {
                    message(error.name === 'AbortError' ? 'The request timed out. Try again; your change may already have been saved.' : error.message || 'Please try again.');
                    // Re-read after an uncertain network outcome rather than guessing saved state.
                    loaded = false; loadFailed = true;
                } finally { busyIds.delete(jobId); sync(); }
            });
            return button;
        }
    };
})();
