import { trapFocus } from './util.js';

/** Quick view: product details in a side panel without leaving the page. */
export function initDrawer() {
    const drawer = document.querySelector('[data-drawer]');
    if (!drawer) return;
    const panel = drawer.querySelector('.drawer__panel');
    const body = drawer.querySelector('[data-drawer-body]');
    let lastFocus = null;

    const close = () => {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        lastFocus?.focus();
    };

    document.addEventListener('click', async (e) => {
        const trigger = e.target.closest('[data-quick-view]');
        if (!trigger) return;
        e.preventDefault();
        lastFocus = trigger;
        body.innerHTML = '';
        body.classList.add('is-loading');
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        panel.focus();
        try {
            const res = await fetch(trigger.dataset.quickView, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            body.innerHTML = await res.text();
            body.querySelectorAll('[data-qv-thumb]').forEach((btn) => btn.addEventListener('click', () => {
                const img = body.querySelector('[data-qv-main] img');
                const sources = body.querySelectorAll('[data-qv-main] source');
                sources.forEach((s) => s.remove());
                img.srcset = '';
                img.src = btn.dataset.qvThumb;
            }));
        } catch {
            close();
        } finally {
            body.classList.remove('is-loading');
        }
    });

    drawer.querySelectorAll('[data-drawer-close]').forEach((el) => el.addEventListener('click', close));
    drawer.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
        trapFocus(panel, e);
    });
}
