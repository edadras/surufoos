/** Inside Suru: product tabs and tap-to-open ingredient cards on touch devices. */
export default function initInside() {
    document.querySelectorAll('[data-inside]').forEach((section) => {
        const tabs = section.querySelectorAll('[data-inside-tab]');
        tabs.forEach((tab) => tab.addEventListener('click', () => {
            tabs.forEach((t) => { t.classList.toggle('is-active', t === tab); t.setAttribute('aria-selected', String(t === tab)); });
            section.querySelectorAll('[data-inside-stage]').forEach((stage) => { stage.hidden = stage.id !== `inside-${tab.dataset.insideTab}`; });
        }));

        section.querySelectorAll('.inside__orbit').forEach((orbit) => {
            orbit.addEventListener('pointerover', (e) => orbit.classList.toggle('has-focus', !!e.target.closest('[data-ingredient]')));
            orbit.addEventListener('pointerleave', () => orbit.classList.remove('has-focus'));
            orbit.addEventListener('click', (e) => {
                const ing = e.target.closest('[data-ingredient]');
                if (!ing) return;
                const wasOpen = ing.classList.contains('is-open');
                orbit.querySelectorAll('.is-open').forEach((el) => el.classList.remove('is-open'));
                ing.classList.toggle('is-open', !wasOpen);
            });
        });
    });
}
