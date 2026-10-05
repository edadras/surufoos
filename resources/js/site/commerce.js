import { request, toast, config, localizeDigits, prefersReducedMotion } from './util.js';

/** Favourites (heart pop + burst), add to cart (thumbnail flies to the cart), quantity steppers. */
export function initCommerce() {
    document.addEventListener('click', async (e) => {
        const fav = e.target.closest('[data-favorite]');
        if (fav) {
            e.preventDefault();
            fav.disabled = true;
            try {
                const data = await request(fav.dataset.favorite, { method: 'POST' });
                document.querySelectorAll(`[data-favorite="${CSS.escape(fav.dataset.favorite)}"]`).forEach((btn) => setFav(btn, data.active));
                if (data.active) burst(fav);
                updateCount('[data-fav-count]', data.count);
                toast(data.active ? config.i18n.favAdded : config.i18n.favRemoved);
            } catch (err) {
                toast(err.message);
            } finally {
                fav.disabled = false;
            }
        }

        const step = e.target.closest('[data-qty-step]');
        if (step) {
            const input = step.parentElement.querySelector('input');
            const min = Number(input.min || 0);
            input.value = Math.min(99, Math.max(min, Number(input.value || 0) + Number(step.dataset.qtyStep)));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('[data-add-to-cart]');
        if (form && form.getAttribute('action') !== '#') {
            e.preventDefault();
            const btn = form.querySelector('[type=submit]');
            btn?.classList.add('is-loading');
            try {
                const data = await request(form.action, { method: 'POST', body: new FormData(form) });
                flyToCart(form);
                updateCount('[data-cart-count]', data.count);
                toast(data.message || config.i18n.cartAdded);
            } catch (err) {
                toast(err.message);
            } finally {
                btn?.classList.remove('is-loading');
            }
        }

        const update = e.target.closest('[data-cart-update]');
        if (update) {
            e.preventDefault();
            await submitCart(update);
        }
    });

    document.addEventListener('change', (e) => {
        const form = e.target.closest('[data-cart-update]');
        if (form && e.target.name === 'quantity') submitCart(form);
    });
}

async function submitCart(form) {
    try {
        const data = await request(form.action, { method: 'POST', body: new FormData(form) });
        const body = document.querySelector('[data-cart-body]');
        if (body && data.html !== undefined) body.innerHTML = data.html;
        updateCount('[data-cart-count]', data.count);
    } catch (err) {
        toast(err.message);
    }
}

function setFav(btn, active) {
    btn.classList.toggle('is-active', active);
    btn.setAttribute('aria-pressed', String(active));
    btn.setAttribute('aria-label', active ? btn.dataset.labelOn : btn.dataset.labelOff);
}

function burst(btn) {
    if (prefersReducedMotion()) return;
    btn.classList.remove('is-pop');
    void btn.offsetWidth;
    btn.classList.add('is-pop');
    const wrap = document.createElement('span');
    wrap.className = 'fav-burst';
    for (let i = 0; i < 8; i++) {
        const dot = document.createElement('i');
        const angle = (Math.PI * 2 * i) / 8;
        dot.style.setProperty('--bx', `${Math.cos(angle) * 26}px`);
        dot.style.setProperty('--by', `${Math.sin(angle) * 26}px`);
        wrap.append(dot);
    }
    btn.append(wrap);
    setTimeout(() => wrap.remove(), 700);
}

function updateCount(selector, count) {
    document.querySelectorAll(selector).forEach((el) => {
        el.textContent = localizeDigits(count);
        el.hidden = !count;
        el.classList.remove('is-bump');
        void el.offsetWidth;
        el.classList.add('is-bump');
    });
}

/** Clones the product image and flies it along an arc into the header/bottom-nav cart icon. */
function flyToCart(form) {
    if (prefersReducedMotion()) return;
    const scope = form.closest('[data-product], .qv, [data-pcard]') || document;
    const img = scope.querySelector('.product__shot.is-active img, .qv__main img, .pcard__img img');
    const targets = [...document.querySelectorAll('[data-cart-link], .bottom-nav a[href*="/cart"]')].filter((el) => el.offsetParent !== null);
    const target = targets[0];
    if (!img || !target) return;

    const from = img.getBoundingClientRect();
    const to = target.getBoundingClientRect();
    const fly = document.createElement('div');
    fly.className = 'fly-thumb';
    fly.innerHTML = `<img src="${img.currentSrc || img.src}" alt="">`;
    document.body.append(fly);

    const sx = from.left + from.width / 2 - 36;
    const sy = from.top + from.height / 2 - 36;
    const ex = to.left + to.width / 2 - 36;
    const ey = to.top + to.height / 2 - 36;
    fly.animate([
        { transform: `translate(${sx}px, ${sy}px) scale(1.4)`, opacity: 1 },
        { transform: `translate(${(sx + ex) / 2}px, ${Math.min(sy, ey) - 120}px) scale(1)`, opacity: 1, offset: 0.5 },
        { transform: `translate(${ex}px, ${ey}px) scale(.2)`, opacity: 0.4 },
    ], { duration: 850, easing: 'cubic-bezier(.5,0,.2,1)' }).onfinish = () => fly.remove();
}
