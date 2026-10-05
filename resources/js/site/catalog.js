import { debounce, localizeDigits } from './util.js';

/**
 * AJAX product filtering. Every change rewrites the URL (shareable, indexable
 * listing URLs) and swaps only the result grid; back/forward restore state.
 */
export default function initCatalog() {
    const root = document.querySelector('[data-catalog]');
    const form = root?.querySelector('[data-filter-form]');
    if (!form) return;
    const results = root.querySelector('[data-results]');
    const count = root.querySelector('[data-result-count]');
    const sortSelect = root.querySelector('[data-sort-select]');
    const sortMirror = form.querySelector('[data-sort-mirror]');
    const panel = root.querySelector('[data-filters-panel]');
    let controller;

    const buildUrl = () => {
        const params = new URLSearchParams();
        for (const [key, value] of new FormData(form)) {
            if (value !== '' && !(key === 'sort' && value === 'featured')) params.append(key, value);
        }
        const qs = params.toString();
        return `${form.action}${qs ? `?${qs}` : ''}`;
    };

    const load = async (url, push = true) => {
        controller?.abort();
        controller = new AbortController();
        results.querySelector('[data-pgrid]')?.classList.add('is-loading');
        try {
            const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: controller.signal });
            const data = await res.json();
            results.innerHTML = data.html;
            if (count) count.textContent = count.textContent.replace(/[\d۰-۹٠-٩,٬]+/, localizeDigits(data.total));
            if (push) history.pushState({ catalog: true }, '', url);
            if (push && results.getBoundingClientRect().top < 0) results.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.dispatchEvent(new CustomEvent('catalog:updated'));
        } catch (err) {
            if (err.name !== 'AbortError') window.location.href = url;
        }
    };

    const onChange = debounce(() => load(buildUrl()), 250);
    form.addEventListener('change', onChange);
    form.addEventListener('input', (e) => { if (e.target.type === 'search' || e.target.type === 'number') onChange(); });
    form.addEventListener('submit', (e) => { e.preventDefault(); load(buildUrl()); });

    sortSelect?.addEventListener('change', () => {
        sortMirror.value = sortSelect.value;
        load(buildUrl());
    });

    root.addEventListener('click', (e) => {
        const page = e.target.closest('[data-page-link]');
        if (page) { e.preventDefault(); load(page.href); }
        const reset = e.target.closest('[data-filter-reset]');
        if (reset) {
            e.preventDefault();
            form.reset();
            form.querySelectorAll('input[type=checkbox]').forEach((c) => { c.checked = false; });
            form.querySelectorAll('input[type=search], input[type=number]').forEach((i) => { i.value = ''; });
            const all = form.querySelector('input[name=category][value=""]');
            if (all) all.checked = true;
            load(form.action);
        }
        if (e.target.closest('[data-filters-open]')) panel.classList.add('is-open');
        if (e.target.closest('[data-filters-close]')) panel.classList.remove('is-open');
    });

    window.addEventListener('popstate', () => load(location.href, false));
}
