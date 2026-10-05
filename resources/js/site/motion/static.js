import { initChapters } from './chapters.js';

/** Reduced-motion mode: no animation, but state-bearing UI still updates. */
export default function initStatic() {
    document.querySelectorAll('[data-journey-step]').forEach((s) => s.classList.add('is-active'));
    initChapters(null);
}
