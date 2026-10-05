import { isDesktop } from '../util.js';

/**
 * New-products shelf: on desktop the section pins and vertical scroll moves
 * the shelf horizontally. The pinned distance equals the overflow width, so
 * the effect ends exactly when the last pack arrives — no dead scrolling.
 */
export function initShelf(gsap, ScrollTrigger) {
    document.querySelectorAll('[data-shelf]').forEach((section) => {
        const row = section.querySelector('[data-shelf-row]');
        const pin = section.querySelector('[data-shelf-pin]');
        if (!row) return;

        gsap.from(row.children, { y: 60, opacity: 0, stagger: 0.06, scrollTrigger: { trigger: section, start: 'top 80%' } });

        const mm = gsap.matchMedia();
        mm.add('(min-width: 960px)', () => {
            const distance = () => Math.max(0, row.scrollWidth - window.innerWidth);
            if (distance() < 80) return undefined;
            section.classList.add('is-pinning');
            const rtl = document.documentElement.dir === 'rtl';
            const tween = gsap.to(row, {
                x: () => (rtl ? distance() : -distance()),
                ease: 'none',
                scrollTrigger: {
                    trigger: pin,
                    start: 'top top+=40',
                    end: () => `+=${distance()}`,
                    pin: true,
                    scrub: 0.8,
                    invalidateOnRefresh: true,
                    anticipatePin: 1,
                },
            });
            return () => { section.classList.remove('is-pinning'); tween.kill(); };
        });
    });
    if (!isDesktop()) ScrollTrigger.refresh();
}
