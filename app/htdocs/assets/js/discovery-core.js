/* Pure catalog operations, shared by the view and dependency-free Node tests. */
(() => {
    'use strict';
    const text = value => String(value ?? '').normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const readState = search => {
        const p = new URLSearchParams(search);
        return {q:(p.get('q') || '').slice(0,150), location:(p.get('location') || '').slice(0,100), category:p.get('category') || '',
            types:[...new Set(p.getAll('type'))], posted:['7','30'].includes(p.get('posted')) ? p.get('posted') : 'any',
            sort:['oldest','title'].includes(p.get('sort')) ? p.get('sort') : 'newest', page:Math.max(1, Number.parseInt(p.get('page'),10) || 1), demo:p.get('demo') === '1'};
    };
    const params = state => {
        const p = new URLSearchParams();
        for (const name of ['q','location','category']) if (state[name]) p.set(name,state[name]);
        for (const type of state.types) p.append('type',type);
        if (state.posted !== 'any') p.set('posted',state.posted);
        if (state.sort !== 'newest') p.set('sort',state.sort);
        if (state.page > 1) p.set('page',state.page);
        if (state.demo) p.set('demo','1');
        return p.toString();
    };
    const timestamp = value => {
        const date = Date.parse(String(value || '').replace(' ', 'T'));
        return Number.isFinite(date) ? date : 0;
    };
    const select = (jobs, state, now = Date.now(), size = 6) => {
        const words = text(state.q).trim().split(/\s+/).filter(Boolean);
        const matched = jobs.filter(job => {
            const haystack = text([job.title,job.company,job.skills,job.description].join(' '));
            const age = now - timestamp(job.posted);
            return words.every(word => haystack.includes(word)) && text(job.location).includes(text(state.location).trim())
                && (!state.category || job.category === state.category) && (!state.types.length || state.types.includes(job.type))
                && (state.posted === 'any' || (timestamp(job.posted) > 0 && age >= 0 && age <= Number(state.posted)*86400000));
        });
        matched.sort((a,b) => state.sort === 'title' ? String(a.title).localeCompare(String(b.title)) || a.id-b.id
            : (state.sort === 'oldest' ? timestamp(a.posted)-timestamp(b.posted) : timestamp(b.posted)-timestamp(a.posted)) || b.id-a.id);
        const pages = Math.max(1,Math.ceil(matched.length/size));
        const page = Math.min(state.page,pages);
        return {items:matched.slice((page-1)*size,page*size), total:matched.length, pages, page, start:matched.length ? (page-1)*size+1 : 0, end:Math.min(page*size,matched.length)};
    };
    const salary = job => {
        const min = Number(job.salaryMin), max = Number(job.salaryMax);
        if (!Number.isFinite(min) || !Number.isFinite(max) || (min === 0 && max === 0)) return 'Salary not specified';
        const number = new Intl.NumberFormat('en',{maximumFractionDigits:2});
        return `${job.currency ? job.currency+' ' : ''}${number.format(min)}${max !== min ? '–'+number.format(max) : ''}${job.period ? ' / '+job.period : ''}`;
    };
    globalThis.JobProDiscovery = Object.freeze({readState,params,select,salary,timestamp});
})();
