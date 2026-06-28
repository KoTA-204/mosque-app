-- Global confirm modal + helper. Pengganti window.confirm() bawaan browser. --
{{-- Dipakai lewat: <form data-confirm="pesan"> ATAU JS confirmAction({...}) / await confirmAsync('pesan') --}}
<div id="globalConfirmModal" style="display: none;"
     class="fixed inset-0 z-[10000] items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-xl">
        <div class="flex items-start justify-between">
            <div>
                <h3 id="globalConfirmTitle" class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi</h3>
                <p id="globalConfirmMessage" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Apakah anda yakin?</p>
            </div>
            <button type="button" id="globalConfirmClose"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">✕</button>
        </div>
        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" id="globalConfirmCancel"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Batal
            </button>
            <button type="button" id="globalConfirmOk"
                    class="rounded-lg bg-red-600 hover:bg-red-700 px-4 py-2 text-sm font-medium text-white">
                Ya
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    let pendingConfirm = null;
    let pendingCancel  = null;

    const modal = () => document.getElementById('globalConfirmModal');

    function close(runCancel) {
        const m = modal();
        if (m) m.style.display = 'none';
        const c = pendingCancel;
        pendingConfirm = null;
        pendingCancel  = null;
        if (runCancel && typeof c === 'function') c();
    }

    // confirmAction({ title, message, confirmLabel, confirmClass, onConfirm, onCancel })
    window.confirmAction = function (opts) {
        opts = opts || {};
        const m = modal();
        if (!m) { // fallback super-defensif; seharusnya tidak terpakai
            if (window.confirm(opts.message || 'Apakah anda yakin?')) {
                if (typeof opts.onConfirm === 'function') opts.onConfirm();
            } else if (typeof opts.onCancel === 'function') {
                opts.onCancel();
            }
            return;
        }
        document.getElementById('globalConfirmTitle').textContent   = opts.title   || 'Konfirmasi';
        document.getElementById('globalConfirmMessage').textContent = opts.message || 'Apakah anda yakin?';
        const ok = document.getElementById('globalConfirmOk');
        ok.textContent = opts.confirmLabel || 'Ya';
        ok.className   = 'rounded-lg px-4 py-2 text-sm font-medium text-white ' +
                         (opts.confirmClass || 'bg-red-600 hover:bg-red-700');
        document.getElementById('globalConfirmCancel').textContent = opts.cancelLabel || 'Batal';

        pendingConfirm = (typeof opts.onConfirm === 'function') ? opts.onConfirm : null;
        pendingCancel  = (typeof opts.onCancel  === 'function') ? opts.onCancel  : null;
        m.style.display = 'flex';
    };

    // Versi Promise: `if (!await confirmAsync('pesan')) return;`
    window.confirmAsync = function (message, opts) {
        return new Promise((resolve) => {
            window.confirmAction(Object.assign({}, opts, {
                message: message,
                onConfirm: () => resolve(true),
                onCancel:  () => resolve(false),
            }));
        });
    };

    function wire() {
        const ok     = document.getElementById('globalConfirmOk');
        const cancel = document.getElementById('globalConfirmCancel');
        const x      = document.getElementById('globalConfirmClose');
        const m      = modal();
        if (ok) ok.addEventListener('click', function () {
            const cb = pendingConfirm;
            pendingConfirm = null;
            pendingCancel  = null;
            const mm = modal(); if (mm) mm.style.display = 'none';
            if (typeof cb === 'function') cb();
        });
        if (cancel) cancel.addEventListener('click', () => close(true));
        if (x)      x.addEventListener('click', () => close(true));
        if (m)      m.addEventListener('click', (e) => { if (e.target === m) close(true); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal() && modal().style.display === 'flex') close(true);
        });

        // Intersepsi semua <form data-confirm="...">
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.hasAttribute('data-confirm')) return;
            if (form.dataset.confirmed === '1') { form.dataset.confirmed = ''; return; }
            e.preventDefault();
            window.confirmAction({
                title:        form.getAttribute('data-confirm-title')  || 'Konfirmasi',
                message:      form.getAttribute('data-confirm')        || 'Apakah anda yakin?',
                confirmLabel: form.getAttribute('data-confirm-label')  || 'Ya',
                confirmClass: form.getAttribute('data-confirm-class')  || 'bg-red-600 hover:bg-red-700',
                onConfirm: function () {
                    form.dataset.confirmed = '1';
                    if (typeof form.requestSubmit === 'function') form.requestSubmit();
                    else form.submit();
                },
            });
        }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', wire);
    } else {
        wire();
    }
})();
</script>
