/** Product page sticky storytelling: each chapter sets the sticky pack's state. */
export function initChapters(ScrollTrigger) {
    const section = document.querySelector('[data-chapters]');
    if (!section) return;
    const visual = section.querySelector('.chapters__sticky');
    section.querySelectorAll('[data-chapter]').forEach((chapter) => {
        const set = () => visual?.setAttribute('data-state', chapter.dataset.chapter);
        if (ScrollTrigger) {
            ScrollTrigger.create({ trigger: chapter, start: 'top 55%', end: 'bottom 55%', onEnter: set, onEnterBack: set });
        } else {
            new IntersectionObserver(([e]) => e.isIntersecting && set(), { rootMargin: '-45% 0px -45% 0px' }).observe(chapter);
        }
    });
}
