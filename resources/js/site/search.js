import { debounce, trapFocus, config } from './util.js';

/** Full-screen search overlay with live results (products, categories, articles, recipes). */
export function initSearch(endpoint) {
    const overlay = document.querySelector('[data-search]');
    if (!overlay) return;
    const input = overlay.querySelector('[data-search-input]');
    const results = overlay.querySelector('[data-search-results]');
    let lastFocus = null;
    let controller = null;

    const open = () => {
        lastFocus = document.activeElement;
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => input.focus());
    };
    const close = () => {
        overlay.hidden = true;
        document.body.style.overflow = '';
        lastFocus?.focus();
    };

    document.querySelectorAll('[data-search-open]').forEach((btn) => btn.addEventListener('click', open));
    overlay.querySelector('[data-search-close]').addEventListener('click', close);
    overlay.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
        trapFocus(overlay, e);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && overlay.hidden && !/input|textarea|select/i.test(document.activeElement.tagName)) {
            e.preventDefault();
            open();
        }
    });

    const run = debounce(async () => {
        const q = input.value.trim();
        if (q.length < 2) { results.innerHTML = q ? `<p class="search-empty">${config.i18n.hint}</p>` : ''; return; }
        controller?.abort();
        controller = new AbortController();
        try {
            const res = await fetch(`${endpoint}?q=${encodeURIComponent(q)}`, { headers: { Accept: 'application/json' }, signal: controller.signal });
            const data = await res.json();
            results.innerHTML = data.html;
        } catch (err) {
            if (err.name !== 'AbortError') results.innerHTML = '';
        }
    }, 220);
    input.addEventListener('input', run);
}
