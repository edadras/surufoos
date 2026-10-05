/**
 * Splits an element's text into visual lines (by measuring word positions).
 * Words are never broken apart, which keeps Persian/Arabic letter joining intact.
 * Returns the inner line spans to animate.
 */
export function splitLines(el) {
    if (el.dataset.splitDone) return [...el.querySelectorAll('.split-line > span')];
    const text = el.textContent.trim().replace(/\s+/g, ' ');
    if (!text) return [];
    el.setAttribute('aria-label', text);
    const words = text.split(' ');
    el.innerHTML = words.map((w) => `<span class="split-word" aria-hidden="true">${escapeHtml(w)}</span>`).join(' ');

    const lines = [];
    let currentTop = null;
    el.querySelectorAll('.split-word').forEach((word) => {
        const top = Math.round(word.offsetTop);
        if (currentTop === null || Math.abs(top - currentTop) > 4) {
            lines.push([]);
            currentTop = top;
        }
        lines[lines.length - 1].push(word.textContent);
    });

    el.innerHTML = lines.map((line) => `<span class="split-line" aria-hidden="true"><span>${line.map(escapeHtml).join(' ')}</span></span>`).join('');
    el.dataset.splitDone = '1';
    return [...el.querySelectorAll('.split-line > span')];
}

function escapeHtml(s) {
    return s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}
