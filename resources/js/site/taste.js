import { request, toast, config, prefersReducedMotion } from './util.js';

/** "What do you feel like?" — posts chosen attribute values, shows matched products. */
export default function initTaste() {
    document.querySelectorAll('[data-taste]').forEach((section) => {
        const form = section.querySelector('[data-taste-form]');
        const out = section.querySelector('[data-taste-results]');
        const submit = async () => {
            const body = new FormData(form);
            if (!body.getAll('values[]').length) { out.innerHTML = ''; return; }
            try {
                const data = await request(config.tasteUrl, { method: 'POST', body });
                out.innerHTML = data.html;
                if (!prefersReducedMotion()) {
                    out.querySelectorAll('.taste__result').forEach((el, i) => el.animate(
                        [{ opacity: 0, transform: 'translateY(24px) rotate(-2deg)' }, { opacity: 1, transform: 'none' }],
                        { duration: 600, delay: i * 70, easing: 'cubic-bezier(.16,1,.3,1)', fill: 'backwards' },
                    ));
                }
                out.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth', block: 'nearest' });
            } catch (err) {
                toast(err.message);
            }
        };
        form.addEventListener('submit', (e) => { e.preventDefault(); submit(); });
        form.addEventListener('reset', () => { out.innerHTML = ''; });
    });
}
