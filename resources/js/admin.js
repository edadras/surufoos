/**
 * Admin panel behaviour: translation tabs + copy from Persian, repeaters,
 * drag & drop ordering (SortableJS), media picker modal with upload,
 * focal point picker, colour sync, delete confirmations and unsaved-change guard.
 */
import Sortable from 'sortablejs';

const cfg = JSON.parse(document.getElementById('admin-config')?.textContent || '{}');
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

// Sidebar toggle (mobile)
document.querySelector('[data-side-toggle]')?.addEventListener('click', () => document.querySelector('[data-side]').classList.toggle('is-open'));

// Delete confirmations
document.addEventListener('submit', (e) => {
    const msg = e.target.dataset?.confirm;
    if (msg && !confirm(msg)) e.preventDefault();
});

// ---------- Translation tabs ----------
document.addEventListener('click', (e) => {
    const tab = e.target.closest('[data-tr-tab]');
    if (tab) {
        const wrap = tab.closest('[data-tr]');
        wrap.querySelectorAll(':scope > .a-tr__tabs [data-tr-tab]').forEach((t) => {
            t.classList.toggle('is-active', t === tab);
            t.setAttribute('aria-selected', String(t === tab));
        });
        wrap.querySelectorAll(':scope > [data-tr-pane]').forEach((p) => { p.hidden = p.dataset.trPane !== tab.dataset.trTab; });
    }

    const copy = e.target.closest('[data-tr-copy]');
    if (copy) {
        const wrap = copy.closest('[data-tr]');
        const target = copy.closest('[data-tr-pane]');
        const source = wrap.querySelector(':scope > [data-tr-pane="fa"]');
        copyFields(source, target);
    }

    const copyColumn = e.target.closest('[data-tr-copy-column]');
    if (copyColumn) {
        const target = copyColumn.closest('[data-tr-column]');
        copyFields(document.querySelector('[data-tr-column="fa"]'), target);
    }
});

function copyFields(source, target) {
    if (!source || !target) return;
    let copied = 0;
    target.querySelectorAll('[data-tr-field]').forEach((field) => {
        const name = field.dataset.trField;
        const from = source.querySelector(`[data-tr-field="${name}"]`);
        // Slugs are per-language; never copy them, and never overwrite text a translator already wrote.
        if (!from || name === 'slug' || field.value.trim() !== '') return;
        field.value = from.value;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        copied++;
    });
    if (!copied) alert('فیلد خالی برای کپی وجود ندارد.');
}

// ---------- Repeaters ----------
document.addEventListener('click', (e) => {
    const add = e.target.closest('[data-repeater-add]');
    if (add) {
        const rep = add.closest('[data-repeater]');
        const tpl = rep.querySelector(':scope > template[data-repeater-template]');
        const list = rep.querySelector(':scope > [data-repeater-list]');
        const index = `n${Date.now()}${Math.floor(Math.random() * 1000)}`;
        list.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__INDEX__', index));
        list.lastElementChild?.querySelector('input:not([type=hidden]), select, textarea')?.focus();
        markDirty();
    }
    const remove = e.target.closest('[data-repeater-remove]');
    if (remove) {
        remove.closest('[data-repeater-row]').remove();
        markDirty();
    }
});

// ---------- Sortable ----------
document.querySelectorAll('[data-sortable]').forEach((el) => {
    Sortable.create(el, {
        handle: el.querySelector('.a-drag') ? '.a-drag' : undefined,
        animation: 160,
        ghostClass: 'sortable-ghost',
        onEnd: async () => {
            markDirty();
            const url = el.dataset.reorderUrl;
            if (!url) return;
            const ids = [...el.children].map((c) => c.dataset.id).filter(Boolean);
            await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' }, body: JSON.stringify({ ids }) });
            dirty = false;
        },
    });
});

// ---------- Media picker ----------
const modal = document.querySelector('[data-media-modal]');
const modalBody = modal?.querySelector('[data-media-modal-body]');
let pickerCallback = null;
let pickerMultiple = false;

async function openPicker(multiple, callback, url = null) {
    pickerCallback = callback;
    pickerMultiple = multiple;
    const target = url || `${cfg.pickerUrl}?multiple=${multiple ? 1 : 0}`;
    modalBody.innerHTML = '<p class="a-muted">در حال بارگذاری…</p>';
    if (!modal.open) modal.showModal();
    const res = await fetch(target, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    modalBody.innerHTML = await res.text();
}

modal?.querySelector('[data-modal-close]').addEventListener('click', () => modal.close());
modal?.addEventListener('click', async (e) => {
    const pick = e.target.closest('[data-pick]');
    if (pick) {
        if (pickerMultiple) { pick.classList.toggle('is-selected'); return; }
        pickerCallback?.([{ id: pick.dataset.pick, thumb: pick.dataset.thumb }]);
        modal.close();
    }
    if (e.target.closest('[data-picker-done]')) {
        const items = [...modal.querySelectorAll('.is-selected')].map((el) => ({ id: el.dataset.pick, thumb: el.dataset.thumb }));
        pickerCallback?.(items);
        modal.close();
    }
    const page = e.target.closest('[data-picker-page]');
    if (page) { e.preventDefault(); openPicker(pickerMultiple, pickerCallback, page.href); }
});
modal?.addEventListener('submit', (e) => {
    if (!e.target.matches('[data-picker-search]')) return;
    e.preventDefault();
    const params = new URLSearchParams(new FormData(e.target));
    params.set('multiple', pickerMultiple ? 1 : 0);
    openPicker(pickerMultiple, pickerCallback, `${cfg.pickerUrl}?${params}`);
});
modal?.addEventListener('change', async (e) => {
    if (!e.target.matches('[data-picker-upload]')) return;
    const items = await upload(e.target.files);
    if (items.length) openPicker(pickerMultiple, pickerCallback);
});

async function upload(files) {
    const body = new FormData();
    [...files].forEach((f) => body.append('files[]', f));
    const res = await fetch(cfg.uploadUrl, { method: 'POST', body, headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
    const data = await res.json().catch(() => ({}));
    if (data.errors?.length) alert(data.errors.join('\n'));
    if (!res.ok && data.message) alert(data.message);
    return data.items || [];
}

document.addEventListener('click', (e) => {
    const choose = e.target.closest('[data-media-choose]');
    if (choose) {
        const field = choose.closest('[data-media-field]');
        openPicker(false, ([item]) => {
            field.querySelector('[data-media-input]').value = item.id;
            field.querySelector('[data-media-preview]').innerHTML = `<img src="${item.thumb}" alt="">`;
            field.querySelector('[data-media-clear]')?.removeAttribute('hidden');
            markDirty();
        });
    }
    const clear = e.target.closest('[data-media-clear]');
    if (clear) {
        const field = clear.closest('[data-media-field]');
        field.querySelector('[data-media-input]').value = '';
        field.querySelector('[data-media-preview]').innerHTML = '<span>بدون تصویر</span>';
        clear.hidden = true;
        markDirty();
    }
    const add = e.target.closest('[data-media-add]');
    if (add) {
        const wrap = add.closest('[data-media-multi]');
        const list = wrap.querySelector('.a-thumbs');
        openPicker(true, (items) => {
            items.forEach((item) => list.insertAdjacentHTML('beforeend',
                `<li class="a-thumb" data-id="${item.id}"><img src="${item.thumb}" alt=""><input type="hidden" name="${wrap.dataset.name}[]" value="${item.id}"><button type="button" class="a-thumb__x" data-thumb-remove aria-label="حذف">×</button></li>`));
            markDirty();
        });
    }
    if (e.target.closest('[data-thumb-remove]')) {
        e.target.closest('.a-thumb').remove();
        markDirty();
    }
});

// Drag & drop upload on the media library page
const dz = document.querySelector('[data-dropzone]');
if (dz) {
    ['dragenter', 'dragover'].forEach((ev) => dz.addEventListener(ev, (e) => { e.preventDefault(); dz.classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach((ev) => dz.addEventListener(ev, () => dz.classList.remove('is-over')));
    dz.addEventListener('drop', async (e) => {
        e.preventDefault();
        if (!e.dataTransfer.files.length) return;
        const items = await upload(e.dataTransfer.files);
        if (items.length) location.reload();
    });
}

// ---------- Focal point ----------
document.querySelectorAll('[data-focal]').forEach((box) => {
    const img = box.querySelector('img');
    const dot = box.querySelector('[data-focal-dot]');
    box.addEventListener('click', (e) => {
        const r = img.getBoundingClientRect();
        const x = Math.round(((e.clientX - r.left) / r.width) * 1000) / 10;
        const y = Math.round(((e.clientY - r.top) / r.height) * 1000) / 10;
        document.querySelector('[data-focal-x]').value = x;
        document.querySelector('[data-focal-y]').value = y;
        // Dot uses physical left so it lines up with the image regardless of direction.
        dot.style.insetInlineStart = '';
        dot.style.left = `${x}%`;
        dot.style.top = `${y}%`;
        markDirty();
    });
});

// ---------- Colour pickers mirror their text field ----------
document.querySelectorAll('[data-color-sync]').forEach((picker) => {
    const text = document.getElementById(picker.dataset.colorSync);
    picker.addEventListener('input', () => { text.value = picker.value; });
    text?.addEventListener('input', () => { if (/^#[0-9a-f]{6}$/i.test(text.value)) picker.value = text.value; });
});

// ---------- Menu item type switch ----------
document.querySelectorAll('.a-menu-form').forEach((form) => {
    const select = form.querySelector('[data-menu-type]');
    const sync = () => form.querySelectorAll('[data-menu-for]').forEach((el) => {
        const on = el.dataset.menuFor === select.value;
        el.hidden = !on;
        el.querySelectorAll('select, input').forEach((i) => { i.disabled = !on; });
    });
    select?.addEventListener('change', sync);
    if (select) sync();
});

// ---------- Unsaved changes guard ----------
let dirty = false;
function markDirty() { if (document.querySelector('[data-dirty-guard]')) dirty = true; }
document.querySelectorAll('[data-dirty-guard]').forEach((form) => {
    form.addEventListener('input', markDirty);
    form.addEventListener('submit', () => { dirty = false; });
});
window.addEventListener('beforeunload', (e) => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
