import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { canHover, isDesktop } from '../util.js';
import { splitLines } from './split.js';
import { initHero } from './hero.js';
import { initShelf } from './shelf.js';
import { initChapters } from './chapters.js';

gsap.registerPlugin(ScrollTrigger);

/**
 * Motion system. Only transform/opacity/clip-path are animated. Persian and
 * Arabic text is split by words and lines only — never by letters — so
 * cursive shaping is preserved.
 */
export default async function initMotion() {
    gsap.defaults({ ease: 'expo.out', duration: 1.1 });

    if (isDesktop() && canHover()) {
        const { default: Lenis } = await import('lenis');
        const lenis = new Lenis({ lerp: 0.11, smoothWheel: true });
        lenis.on('scroll', ScrollTrigger.update);
        gsap.ticker.add((time) => lenis.raf(time * 1000));
        gsap.ticker.lagSmoothing(0);
        document.addEventListener('click', (e) => {
            const a = e.target.closest('a[href^="#"]');
            if (a && a.getAttribute('href').length > 1) { e.preventDefault(); lenis.scrollTo(a.getAttribute('href'), { offset: -90 }); }
        });
        // Overlays manage their own scrolling.
        const observer = new MutationObserver(() => (document.body.style.overflow === 'hidden' || document.body.classList.contains('nav-open') ? lenis.stop() : lenis.start()));
        observer.observe(document.body, { attributes: true, attributeFilter: ['style', 'class'] });
    }

    textReveals();
    blockReveals();
    maskReveals();
    parallax();
    initHero(gsap, ScrollTrigger);
    storyWords();
    universe();
    journey();
    initShelf(gsap, ScrollTrigger);
    initChapters(ScrollTrigger);
    magnetic();
    cardTilt();
    document.addEventListener('catalog:updated', () => { cardEntrance(); ScrollTrigger.refresh(); });
    cardEntrance();

    window.addEventListener('load', () => ScrollTrigger.refresh());
}

function textReveals() {
    document.querySelectorAll('[data-split]').forEach((el) => {
        const lines = splitLines(el);
        gsap.from(lines, {
            yPercent: 110,
            rotate: 2,
            stagger: 0.08,
            scrollTrigger: { trigger: el, start: 'top 88%' },
        });
    });
}

function blockReveals() {
    ScrollTrigger.batch('[data-reveal]', {
        start: 'top 90%',
        onEnter: (batch) => gsap.to(batch, { opacity: 1, y: 0, stagger: 0.1, overwrite: true }),
    });
}

function maskReveals() {
    document.querySelectorAll('[data-reveal-mask]').forEach((el) => {
        const img = el.querySelector('img');
        const tl = gsap.timeline({ scrollTrigger: { trigger: el, start: 'top 85%' } });
        tl.to(el, { clipPath: 'inset(0% 0% 0% 0% round 14px)', duration: 1.4, ease: 'expo.inOut' });
        if (img) tl.to(img, { scale: 1, duration: 1.6, ease: 'expo.out' }, 0);
    });
}

function parallax() {
    document.querySelectorAll('[data-parallax]').forEach((el) => {
        const speed = parseFloat(el.dataset.parallax) || 0.1;
        gsap.fromTo(el, { yPercent: -speed * 100 }, {
            yPercent: speed * 100,
            ease: 'none',
            scrollTrigger: { trigger: el.closest('section') || el, start: 'top bottom', end: 'bottom top', scrub: true },
        });
    });
}

function storyWords() {
    document.querySelectorAll('[data-story-words]').forEach((wrap) => {
        const words = wrap.querySelectorAll('span');
        gsap.fromTo(words, { xPercent: (i) => (i % 2 ? 40 : -40), opacity: 0 }, {
            xPercent: 0, opacity: 1, stagger: 0.06, ease: 'none',
            scrollTrigger: { trigger: wrap, start: 'top 95%', end: 'bottom 55%', scrub: 0.6 },
        });
    });
}

function universe() {
    document.querySelectorAll('[data-universe] [data-world]').forEach((world, i) => {
        gsap.from(world, { y: 80, opacity: 0, delay: i * 0.06, scrollTrigger: { trigger: world.parentElement, start: 'top 85%' } });
        const img = world.querySelector('[data-world-img]');
        if (!img || !canHover()) return;
        world.addEventListener('pointermove', (e) => {
            const r = world.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width - 0.5;
            const y = (e.clientY - r.top) / r.height - 0.5;
            gsap.to(img, { x: x * 24, y: y * 18, duration: 0.8, overwrite: 'auto' });
        });
        world.addEventListener('pointerleave', () => gsap.to(img, { x: 0, y: 0, duration: 1 }));
    });
}

function journey() {
    document.querySelectorAll('[data-journey]').forEach((section) => {
        const path = section.querySelector('[data-journey-path]');
        const steps = [...section.querySelectorAll('[data-journey-step]')];
        if (path) {
            const length = path.getTotalLength();
            gsap.set(path, { strokeDasharray: length, strokeDashoffset: length });
            gsap.to(path, {
                strokeDashoffset: 0, ease: 'none',
                scrollTrigger: { trigger: section, start: 'top 70%', end: 'bottom 70%', scrub: 0.5 },
            });
        }
        steps.forEach((step, i) => ScrollTrigger.create({
            trigger: section,
            start: () => `top+=${(section.offsetHeight * 0.6 * i) / steps.length} 70%`,
            onEnter: () => step.classList.add('is-active'),
            onLeaveBack: () => step.classList.remove('is-active'),
        }));
    });
}

/** Buttons drift towards the pointer a little. */
function magnetic() {
    if (!canHover()) return;
    document.querySelectorAll('[data-magnetic]').forEach((el) => {
        const xTo = gsap.quickTo(el, 'x', { duration: 0.6, ease: 'elastic.out(1, 0.4)' });
        const yTo = gsap.quickTo(el, 'y', { duration: 0.6, ease: 'elastic.out(1, 0.4)' });
        el.addEventListener('pointermove', (e) => {
            const r = el.getBoundingClientRect();
            xTo((e.clientX - r.left - r.width / 2) * 0.25);
            yTo((e.clientY - r.top - r.height / 2) * 0.35);
        });
        el.addEventListener('pointerleave', () => { xTo(0); yTo(0); });
    });
}

/** Pack follows the pointer inside its tile; shadow (plinth) moves the opposite way. */
function cardTilt() {
    if (!canHover()) return;
    document.addEventListener('pointermove', (e) => {
        const card = e.target.closest?.('.pcard.has-cutout');
        if (!card) return;
        const media = card.querySelector('.pcard__media');
        const r = media.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width - 0.5;
        gsap.to(card.querySelector('.pcard__plinth'), { x: -x * 18, duration: 0.6, overwrite: 'auto' });
    }, { passive: true });
}

function cardEntrance() {
    ScrollTrigger.batch('.pgrid .pcard:not(.is-in), .showcase__grid .pcard:not(.is-in), .rgrid .rcard:not(.is-in)', {
        start: 'top 92%',
        onEnter: (batch) => {
            batch.forEach((el) => el.classList.add('is-in'));
            gsap.from(batch, { y: 50, opacity: 0, stagger: 0.07, duration: 1 });
        },
    });
}
