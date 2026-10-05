import { request, toast, config } from './util.js';

/** Small form helpers: AJAX newsletter, share button, flash auto-hide. */
export function initForms() {
    document.querySelectorAll('[data-ajax-form]').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('[type=submit]');
            btn.disabled = true;
            try {
                const data = await request(form.action, { method: 'POST', body: new FormData(form) });
                toast(data.message);
                form.reset();
            } catch (err) {
                toast(err.message);
            } finally {
                btn.disabled = false;
            }
        });
    });

    document.addEventListener('click', async (e) => {
        const share = e.target.closest('[data-share]');
        if (!share) return;
        const data = { title: share.dataset.shareTitle || document.title, url: location.href };
        if (navigator.share) {
            try { await navigator.share(data); } catch { /* dismissed */ }
        } else {
            await navigator.clipboard?.writeText(data.url);
            toast(config.i18n.copied);
        }
    });

    const flash = document.querySelector('[data-flash]');
    if (flash) setTimeout(() => flash.remove(), 6000);
}
