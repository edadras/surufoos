/** Product gallery thumbnails. */
export default function initGallery() {
    document.querySelectorAll('[data-gallery]').forEach((gallery) => {
        const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');
        thumbs.forEach((thumb) => thumb.addEventListener('click', () => {
            const index = thumb.dataset.galleryThumb;
            gallery.querySelectorAll('[data-gallery-item]').forEach((item) => {
                const active = item.dataset.galleryItem === index;
                item.hidden = !active;
                item.classList.toggle('is-active', active);
            });
            thumbs.forEach((t) => {
                t.classList.toggle('is-active', t === thumb);
                t.setAttribute('aria-selected', String(t === thumb));
            });
        }));
    });
}
