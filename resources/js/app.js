/**
 * Suru Food — public site entry.
 * Core interactions load eagerly (small); motion and page-specific modules are
 * code-split and only fetched when their markup is on the page.
 */
import { config, prefersReducedMotion } from './site/util.js';
import { initHeader } from './site/header.js';
import { initSearch } from './site/search.js';
import { initDrawer } from './site/drawer.js';
import { initCommerce } from './site/commerce.js';
import { initForms } from './site/forms.js';

const root = document.documentElement;
if (!prefersReducedMotion()) root.classList.add('motion-ok');

initHeader();
initSearch(config.searchUrl);
initDrawer();
initCommerce();
initForms();

const lazy = [
    ['[data-filter-form]', () => import('./site/catalog.js')],
    ['[data-gallery]', () => import('./site/gallery.js')],
    ['[data-taste-form]', () => import('./site/taste.js')],
    ['[data-inside]', () => import('./site/inside.js')],
    ['[data-video]', () => import('./site/video.js')],
];
for (const [selector, load] of lazy) {
    if (document.querySelector(selector)) load().then((m) => m.default());
}

// Motion system (GSAP + ScrollTrigger + optional Lenis) only when motion is allowed.
if (!prefersReducedMotion()) {
    const start = () => import('./site/motion/index.js').then((m) => m.default());
    if ('requestIdleCallback' in window) requestIdleCallback(start, { timeout: 1200 });
    else setTimeout(start, 200);
} else {
    // Static fallbacks: the journey and chapters still need to show state without animation.
    import('./site/motion/static.js').then((m) => m.default());
}
