(() => {
    'use strict';
    const root = document.querySelector('[data-discovery]');
    if (!root) return;
    const core = globalThis.JobProDiscovery;
    const $ = selector => root.querySelector(selector);
    const listView = root.dataset.view === 'list';
    let state = core.readState(location.search);
    let jobs = [];
    let loaded = false;
    const element = (tag, className, content) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (content !== undefined) node.textContent = String(content);
        return node;
    };
    const detailLink = job => {
        const p = new URLSearchParams(core.params(state));
        p.set('id',job.id);
        return '/pages/job.php?' + p.toString();
    };
    const initials = company => String(company || 'JP').trim().split(/\s+/).slice(0,2).map(word=>Array.from(word)[0]).join('').toUpperCase();
    const dateLabel = job => {
        const time = core.timestamp(job.posted);
        return time ? new Intl.DateTimeFormat('en',{month:'short',day:'numeric',year:'numeric'}).format(new Date(time)) : 'Date not specified';
    };
    const createLink = (label, href, className) => {
        const a = element('a',className,label);
        a.href = href;
        return a;
    };
    const meta = (job, target) => {
        target.replaceChildren(...[job.location,job.type,job.category].filter(Boolean).map(value=>element('span','',value)));
    };
    const currencyNote = job => !job.currency || !job.period ? 'Currency / pay period not provided' : '';
    const makeCard = job => {
        const card = element('article','jp-opportunity');
        const heading = element('div','jp-opportunity-heading');
        const mark = element('div','jp-employer-mark',initials(job.company));
        mark.setAttribute('aria-hidden','true');
        const titleWrap = element('div','jp-opportunity-title');
        const title = element('h3');
        title.append(createLink(job.title,detailLink(job)));
        titleWrap.append(title,element('p','jp-company',job.company));
        const posted = element('span','jp-posted','Posted '+dateLabel(job));
        heading.append(mark,titleWrap,posted);
        const metadata = element('div','jp-job-meta');
        meta(job,metadata);
        const foot = element('div','jp-opportunity-footer');
        const pay = element('div','jp-salary',core.salary(job));
        const note = currencyNote(job);
        if (note) pay.append(element('small','',note));
        const link = createLink('View opportunity →',detailLink(job),'jp-text-link');
        link.setAttribute('aria-label','View '+job.title+' at '+job.company);
        foot.append(pay,link);
        card.append(heading,metadata,element('p','jp-opportunity-description',job.description || 'Open this opportunity for more information.'),foot);
        return card;
    };
    const syncForm = () => {
        if (!listView) return;
        $('#job-keyword').value = state.q;
        $('#job-location').value = state.location;
        $('#job-category').value = state.category;
        $('#posted-within').value = state.posted;
        $('[data-sort]').value = state.sort;
        root.querySelectorAll('[name="employment-type"]').forEach(input => {input.checked = state.types.includes(input.value);});
        root.querySelectorAll('[data-search-suggestion]').forEach(link=>{
            const suggested = {...state,q:link.dataset.searchSuggestion,page:1};
            link.href = '/pages/seekJob.php?'+core.params(suggested);
        });
    };
    const writeUrl = () => {
        const query = core.params(state);
        const url = location.pathname + (query ? '?'+query : '');
        if (url !== location.pathname+location.search) history.pushState(null,'',url);
    };
    const chips = () => {
        const target = $('[data-active-filters]');
        target.replaceChildren();
        const values = [['q',state.q],['location',state.location],['category',state.category],
            ...state.types.map(type=>['type',type]), ...(state.posted !== 'any' ? [['posted','Last '+state.posted+' days']] : [])];
        for (const [key,value] of values) {
            if (!value) continue;
            const button = element('button','jp-filter-chip',value+' ×');
            button.type = 'button';
            button.setAttribute('aria-label','Remove filter: '+value);
            button.addEventListener('click',()=>{
                if (key === 'type') state.types = state.types.filter(type=>type !== value);
                else state[key] = key === 'posted' ? 'any' : '';
                state.page = 1;
                renderList(true);
                $('#results-heading').focus();
            });
            target.append(button);
        }
    };
    const renderList = (updateUrl = false, focus = false) => {
        if (!loaded) return;
        const selection = core.select(jobs,state);
        state.page = selection.page;
        if (updateUrl) writeUrl();
        syncForm();
        chips();
        $('[data-jobs]').replaceChildren(...selection.items.map(makeCard));
        $('[data-empty]').hidden = selection.total !== 0;
        $('[data-empty] h3').textContent = jobs.length ? 'No matches this time.' : 'The next opportunity is on its way.';
        $('[data-empty] p').textContent = jobs.length ? 'Try a broader keyword or remove a filter to see more opportunities.' : 'There are no job listings yet. Check back after an employer posts a role.';
        $('[data-count]').textContent = selection.total ? `${selection.start}–${selection.end} of ${selection.total} ${selection.total === 1 ? 'opportunity' : 'opportunities'}` : '0 opportunities';
        const pagination = $('[data-pagination]');
        pagination.replaceChildren();
        if (selection.pages > 1) {
            const pageLink = (page,label,current=false) => {
                const link = createLink(label,'/pages/seekJob.php?'+core.params({...state,page}),'jp-page-link');
                link.setAttribute('aria-label',current ? `Page ${page}, current page` : `Go to page ${page}`);
                if (current) link.setAttribute('aria-current','page');
                link.addEventListener('click',event=>{
                    if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
                    event.preventDefault(); state.page=page; renderList(true,true);
                });
                pagination.append(link);
            };
            if (selection.page > 1) pageLink(selection.page-1,'← Previous');
            for (let page=1;page<=selection.pages;page++) {
                if (page===1 || page===selection.pages || Math.abs(page-selection.page)<=1) pageLink(page,String(page),page===selection.page);
                else if (Math.abs(page-selection.page)===2) pagination.append(element('span','jp-page-link','…'));
            }
            if (selection.page<selection.pages) pageLink(selection.page+1,'Next →');
        }
        $('[data-results]').setAttribute('aria-busy','false');
        if (focus) $('#results-heading').focus();
    };
    const populateFilters = () => {
        const category = $('#job-category');
        category.replaceChildren(element('option','','All categories'));
        category.firstElementChild.value = '';
        [...new Set(jobs.map(job=>job.category).filter(Boolean))].sort().forEach(value=>{
            const option = element('option','',value); option.value=value; category.append(option);
        });
        // Preserve unfamiliar deep-linked values so the empty state explains them.
        if (state.category && ![...category.options].some(option=>option.value===state.category)) {
            const option = element('option','',state.category); option.value=state.category; category.append(option);
        }
        const types = $('[data-type-options]');
        types.replaceChildren();
        [...new Set([...jobs.map(job=>job.type),...state.types].filter(Boolean))].sort().forEach(value=>{
            const label = element('label','jp-check');
            const input = element('input'); input.type='checkbox'; input.name='employment-type'; input.value=value;
            input.addEventListener('change',()=>{
                state.types = [...root.querySelectorAll('[name="employment-type"]:checked')].map(input=>input.value);
                state.page=1; renderList(true);
            });
            label.append(input,element('span','',value)); types.append(label);
        });
    };
    const addFact = (target,label,value) => {
        const item = element('div'); item.append(element('dt','',label),element('dd','',value || 'Not specified')); target.append(item);
    };
    const renderDetail = () => {
        const rawId = new URLSearchParams(location.search).get('id');
        const job = /^\d+$/.test(rawId || '') && Number(rawId)>0 ? jobs.find(job=>job.id===Number(rawId)) : null;
        $('[data-detail-status]').textContent = '';
        if (!job) { $('[data-not-found]').hidden=false; document.title='Opportunity unavailable — JobPro'; return; }
        document.title=job.title+' at '+job.company+' — JobPro';
        $('[data-company-mark]').textContent=initials(job.company);
        $('[data-company]').textContent=job.company;
        $('[data-title]').textContent=job.title;
        meta(job,$('[data-meta]'));
        $('[data-description]').textContent=job.description || 'The employer has not provided a description. Confirm the role details before applying.';
        const facts = $('[data-facts]'); facts.replaceChildren();
        addFact(facts,'Required skills',job.skills);
        addFact(facts,'Notice period',job.notice);
        addFact(facts,'Facilities / benefits',job.benefits);
        addFact(facts,'Job category',job.category);
        $('[data-employer-name]').textContent=job.company;
        if (job.website) {
            try {
                const website = new URL(/^https?:\/\//i.test(job.website) ? job.website : 'https://'+job.website);
                if (['http:','https:'].includes(website.protocol) && !website.username && !website.password && !/[\s<>]/.test(job.website)) {
                    $('[data-website]').href=website.href; $('[data-website]').hidden=false;
                }
            } catch (error) { /* A malformed employer URL is never used as a link. */ }
        }
        const overview = $('[data-overview]'); overview.replaceChildren();
        addFact(overview,'Salary range',core.salary(job));
        if (currencyNote(job)) addFact(overview,'Salary information',currencyNote(job));
        addFact(overview,'Location',job.location);
        addFact(overview,'Employment type',job.type);
        addFact(overview,'Posted on',dateLabel(job));
        const apply = $('[data-apply]');
        if (state.demo) {
            apply.textContent='Browse real opportunities'; apply.href='/pages/seekJob.php';
            $('[data-apply-copy]').textContent='This is an illustrative role for reviewing the new design.';
            $('[data-apply-hint]').textContent='No application will be submitted for this sample job.';
        } else if (root.dataset.accountRole === 'personal') {
            apply.textContent='Continue to application →'; apply.href='/pages/applications.php#job-'+job.id;
            $('[data-apply-copy]').textContent='Continue to the existing application page to apply for this role.';
            $('[data-apply-hint]').textContent='Opening the application page does not submit an application.';
        } else if (root.dataset.accountRole === 'company') {
            apply.textContent='Manage your posted jobs'; apply.href='/pages/jobs.php';
            $('[data-apply-copy]').textContent='You’re browsing with a company account. Applications are for personal accounts.';
            $('[data-apply-hint]').textContent='Your employer tools are available on your posted jobs page.';
        } else {
            apply.textContent='Log in to apply →'; apply.href='/pages/login.php';
            $('[data-apply-copy]').textContent='Sign in with a personal account to take the next step.';
            $('[data-apply-hint]').textContent='After signing in, return to this role and continue to the application page.';
        }
        $('[data-share]').hidden=!navigator.clipboard?.writeText;
        $('[data-detail]').hidden=false;
        $('[data-detail]').setAttribute('aria-busy','false');
    };
    const load = async () => {
        if (!listView) {
            const rawId = new URLSearchParams(location.search).get('id');
            if (!/^\d+$/.test(rawId || '') || Number(rawId) < 1) {
                renderDetail();
                $('[data-loading]').hidden=true;
                return;
            }
        }
        $('[data-loading]').hidden=false;
        $('[data-error]').hidden=true;
        if (listView) { $('[data-results]').setAttribute('aria-busy','true'); $('[data-count]').textContent='Loading job listings…'; }
        const controller = new AbortController();
        const timer = setTimeout(()=>controller.abort(),10000);
        try {
            const id = new URLSearchParams(location.search).get('id');
            const endpoint = state.demo ? '/assets/data/discovery-demo.json' : '/api/discovery.php'+(!listView ? '?id='+encodeURIComponent(id || '') : '');
            const response = await fetch(endpoint,{signal:controller.signal,headers:{Accept:'application/json'}});
            if (!response.ok && !(response.status===404 && !listView)) throw new Error('Unable to load jobs');
            const payload = await response.json();
            if (!Array.isArray(payload.jobs) || !payload.jobs.every(job=>Number.isInteger(job.id) && typeof job.title==='string' && typeof job.company==='string')) throw new Error('Invalid catalog');
            jobs=payload.jobs;
            loaded=true;
            if (listView) { populateFilters(); renderList(); }
            else renderDetail();
        } catch (error) {
            $('[data-error]').hidden=false;
            if (listView) { $('[data-count]').textContent='Job listings could not be loaded.'; $('[data-results]').setAttribute('aria-busy','false'); }
            else $('[data-detail-status]').textContent='Role details could not be loaded.';
        } finally { clearTimeout(timer); $('[data-loading]').hidden=true; }
    };
    $('[data-demo-note]').hidden=!state.demo;
    root.querySelectorAll('[data-back]').forEach(link=>{ const query=core.params(state); link.href='/pages/seekJob.php'+(query ? '?'+query : ''); });
    if (listView) {
        if (window.matchMedia('(max-width: 700px)').matches) $('.jp-filter-panel').open=false;
        $('[data-source-label]').textContent=state.demo ? 'Sample jobs · design preview' : 'Existing job listings';
        syncForm();
        $('[data-search-form]').addEventListener('submit',event=>{
            event.preventDefault(); state.q=$('#job-keyword').value.trim(); state.location=$('#job-location').value.trim(); state.page=1; renderList(true);
        });
        root.querySelectorAll('[data-filter]').forEach(input=>input.addEventListener('change',()=>{state[input.dataset.filter]=input.value;state.page=1;renderList(true);}));
        $('[data-sort]').addEventListener('change',event=>{state.sort=event.target.value;state.page=1;renderList(true);});
        root.querySelectorAll('[data-search-suggestion]').forEach(link=>link.addEventListener('click',event=>{
            if(event.ctrlKey||event.metaKey||event.shiftKey||event.altKey||event.button!==0) return;
            event.preventDefault();state.q=link.dataset.searchSuggestion;state.page=1;renderList(true);
        }));
        root.querySelectorAll('[data-clear]').forEach(button=>button.addEventListener('click',()=>{state=core.readState(state.demo?'?demo=1':'');renderList(true);$('#job-keyword').focus();}));
        window.addEventListener('popstate',()=>{
            const next=core.readState(location.search);
            if(next.demo!==state.demo) {location.reload();return;}
            state=next;populateFilters();renderList();
        });
    } else {
        $('[data-share]').addEventListener('click',async()=>{
            try {await navigator.clipboard.writeText(location.href);$('[data-share-status]').textContent='Job link copied.';}
            catch(error) {$('[data-share-status]').textContent='Copy the link from your browser’s address bar.';}
        });
    }
    $('[data-retry]').addEventListener('click',load);
    load();
})();
