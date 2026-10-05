export const config = JSON.parse(document.getElementById('app-config')?.textContent || '{}');
export const isRtl = () => document.documentElement.dir === 'rtl';
export const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
export const isDesktop = () => window.matchMedia('(min-width: 960px)').matches;
export const canHover = () => window.matchMedia('(hover: hover) and (pointer: fine)').matches;
export const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

export async function request(url, { method = 'GET', body = null, headers = {} } = {}) {
    const res = await fetch(url, {
        method,
        body,
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf(), ...headers },
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        const message = data.message || Object.values(data.errors || {}).flat()[0] || res.statusText;
        throw Object.assign(new Error(message), { status: res.status, data });
    }
    return data;
}

let toastTimer;
export function toast(message) {
    const el = document.querySelector('[data-toast]');
    if (!el || !message) return;
    el.textContent = message;
    el.classList.add('is-visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('is-visible'), 2600);
}

export function debounce(fn, wait = 250) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
}

export function localizeDigits(value) {
    const s = String(value);
    if (config.locale === 'fa') return s.replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
    if (config.locale === 'ar') return s.replace(/\d/g, (d) => '٠١٢٣٤٥٦٧٨٩'[d]);
    return s;
}

/** Keeps keyboard focus inside a dialog while it is open. */
export function trapFocus(container, event) {
    if (event.key !== 'Tab') return;
    const items = [...container.querySelectorAll('a[href], button:not([disabled]), input:not([type=hidden]), select, textarea, [tabindex]:not([tabindex="-1"])')]
        .filter((el) => el.offsetParent !== null);
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) { last.focus(); event.preventDefault(); }
    else if (!event.shiftKey && document.activeElement === last) { first.focus(); event.preventDefault(); }
}
