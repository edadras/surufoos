/** Click-to-load video embeds (no third-party iframe until the visitor asks for it). */
export default function initVideo() {
    document.querySelectorAll('[data-video]').forEach((frame) => {
        const play = frame.querySelector('[data-video-play]');
        play?.addEventListener('click', () => {
            const iframe = document.createElement('iframe');
            iframe.src = frame.dataset.embed;
            iframe.allow = 'autoplay; encrypted-media; picture-in-picture';
            iframe.allowFullscreen = true;
            iframe.title = play.getAttribute('aria-label') || 'video';
            play.replaceWith(iframe);
        });
    });
}
