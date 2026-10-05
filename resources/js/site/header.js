import { prefersReducedMotion, canHover } from './util.js';

/**
 * Header: transparent over the hero, wipes into a solid strip once the hero is
 * passed, hides on fast downward scroll. Also mega menu, language switch and
 * the mobile menu.
 */
export function initHeader() {
    const header = document.querySelector('[data-header]');
    if (!header) return;

    const hero = document.querySelector('[data-hero]');
    if (hero) {
        // Light logo/text when the hero image is dark.
        if (hero.querySelector('.hero__bg')) header.classList.add('on-dark');
        new IntersectionObserver(([entry]) => header.classList.toggle('is-solid', !entry.isIntersecting), {
            rootMargin: `-${header.offsetHeight}px 0px 0px 0px`,
        }).observe(hero.querySelector('.hero__copy') || hero);
    } else {
        header.classList.add('is-solid');
    }

    let lastY = window.scrollY;
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            const y = window.scrollY;
            const open = header.querySelector('.is-open') || document.body.classList.contains('nav-open');
            header.classList.toggle('is-hidden', !open && y > 400 && y - lastY > 6);
            if (lastY - y > 4 || y < 200) header.classList.remove('is-hidden');
            lastY = y;
            ticking = false;
        });
    }, { passive: true });

    initMega(header);
    initLangSwitch(header);
    initMobileNav(header);
}

function initMega(header) {
    header.querySelectorAll('.has-mega').forEach((item) => {
        const trigger = item.querySelector('[data-mega-trigger]');
        let closeTimer;
        const open = () => { clearTimeout(closeTimer); item.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); };
        const close = () => { closeTimer = setTimeout(() => { item.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); }, 120); };
        if (canHover()) {
            item.addEventListener('pointerenter', open);
            item.addEventListener('pointerleave', close);
        }
        item.addEventListener('focusin', open);
        item.addEventListener('focusout', (e) => { if (!item.contains(e.relatedTarget)) close(); });
        item.addEventListener('keydown', (e) => { if (e.key === 'Escape') { close(); trigger.focus(); } });
    });
}

function initLangSwitch(header) {
    const wrap = header.querySelector('[data-lang-switch]');
    if (wrap) {
        const btn = wrap.querySelector('button');
        btn.addEventListener('click', () => {
            const open = wrap.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', String(open));
        });
        document.addEventListener('click', (e) => { if (!wrap.contains(e.target)) { wrap.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); } });
        wrap.addEventListener('keydown', (e) => { if (e.key === 'Escape') { wrap.classList.remove('is-open'); btn.focus(); } });
    }

    // Short veil transition when switching language (direction flips between RTL and LTR).
    document.querySelectorAll('[data-lang-link], .mobile-nav__langs a, .site-footer__langs a').forEach((link) => {
        link.addEventListener('click', (e) => {
            if (prefersReducedMotion() || e.metaKey || e.ctrlKey || link.classList.contains('is-active')) return;
            e.preventDefault();
            const veil = document.createElement('div');
            veil.className = 'lang-veil';
            document.body.append(veil);
            requestAnimationFrame(() => veil.classList.add('is-on'));
            setTimeout(() => { window.location.href = link.href; }, 380);
        });
    });
}

function initMobileNav(header) {
    const toggle = header.querySelector('[data-nav-toggle]');
    const nav = header.querySelector('[data-mobile-nav]');
    if (!toggle || !nav) return;
    const set = (open) => {
        nav.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('nav-open', open);
        header.classList.toggle('is-solid', open || header.classList.contains('is-solid'));
    };
    toggle.addEventListener('click', () => set(nav.hidden));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !nav.hidden) { set(false); toggle.focus(); } });
}
