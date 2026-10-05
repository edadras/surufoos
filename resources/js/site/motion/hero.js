import { canHover, isDesktop, isRtl } from '../util.js';

/**
 * Hero product stage.
 * - Pointer: very small parallax per layer depth (data-depth).
 * - Scroll: product scales up, copy slides out, ingredient layers drift apart,
 *   and the shadow fades — the stage "morphs" into the next section.
 * Mobile gets only the scroll part with reduced distances.
 */
export function initHero(gsap, ScrollTrigger) {
    const hero = document.querySelector('[data-hero]');
    if (!hero) return;
    const stage = hero.querySelector('[data-hero-stage]');
    const product = hero.querySelector('[data-hero-product]');
    const shadow = hero.querySelector('[data-hero-shadow]');
    const copy = hero.querySelector('[data-hero-copy]');
    const layers = [...hero.querySelectorAll('[data-depth]')];
    const desktop = isDesktop();

    // Entrance
    const intro = gsap.timeline({ defaults: { duration: 1.4 } });
    if (product) intro.from(product, { y: 80, scale: 0.85, opacity: 0, ease: 'expo.out' });
    intro.from(hero.querySelectorAll('.hero__layer'), { scale: 0, opacity: 0, stagger: 0.07, ease: 'back.out(1.6)' }, '-=1.1');
    intro.from(hero.querySelector('.hero__tag'), { y: 20, opacity: 0 }, '-=0.8');

    // Idle float
    if (product) gsap.to(product, { y: -12, duration: 3.2, ease: 'sine.inOut', yoyo: true, repeat: -1 });
    if (shadow) gsap.to(shadow, { scaleX: 0.9, opacity: 0.7, duration: 3.2, ease: 'sine.inOut', yoyo: true, repeat: -1 });

    // Pointer parallax
    if (canHover() && stage) {
        const setters = layers.map((el) => ({
            depth: parseFloat(el.dataset.depth) || 0.2,
            x: gsap.quickTo(el, 'x', { duration: 1.2, ease: 'power3.out' }),
            y: gsap.quickTo(el, 'y', { duration: 1.2, ease: 'power3.out' }),
        }));
        hero.addEventListener('pointermove', (e) => {
            const r = hero.getBoundingClientRect();
            const nx = (e.clientX - r.left) / r.width - 0.5;
            const ny = (e.clientY - r.top) / r.height - 0.5;
            setters.forEach((s) => { s.x(nx * 60 * s.depth); s.y(ny * 40 * s.depth); });
        });
        hero.addEventListener('pointerleave', () => setters.forEach((s) => { s.x(0); s.y(0); }));
    }

    // Scroll morph
    const dir = isRtl() ? 1 : -1;
    const tl = gsap.timeline({
        defaults: { ease: 'none' },
        scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: 0.6 },
    });
    if (product) tl.to(product, { scale: desktop ? 1.18 : 1.08, yPercent: desktop ? 12 : 6 }, 0);
    if (shadow) tl.to(shadow, { opacity: 0, scaleX: 1.4 }, 0);
    if (copy) tl.to(copy, { xPercent: dir * (desktop ? 18 : 0), yPercent: desktop ? 0 : -10, opacity: 0 }, 0);
    hero.querySelectorAll('.hero__layer').forEach((layer, i) => {
        const spread = desktop ? 120 : 50;
        tl.to(layer, { y: -spread * (0.5 + (i % 3) * 0.4), x: (i % 2 ? 1 : -1) * spread * 0.4, rotate: (i % 2 ? 25 : -25), opacity: 0.2 }, 0);
    });
    const bg = hero.querySelector('.hero__bg');
    if (bg) tl.to(bg, { yPercent: 12, scale: 1.08 }, 0);
}
