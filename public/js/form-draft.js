/**
 * form-draft.js
 * Auto-save & restore isi form ke localStorage.
 * File upload TIDAK ikut disimpan (keterbatasan browser).
 *
 * Dasar (field statis saja):
 *   FormDraft.init({ formId: 'myForm', storageKey: 'draft_x' });
 *
 * Lanjutan (form dengan state dinamis, misal array baris jurnal):
 *   FormDraft.init({
 *       formId: 'myForm',
 *       storageKey: 'draft_x',
 *       getExtraData: () => ({ detailRows, rowCounter }),
 *       setExtraData: (extra) => { detailRows = extra.detailRows; rowCounter = extra.rowCounter; },
 *       onRestore: (data, extra) => { renderDetailRows(); },
 *   });
 */
(function (window) {
    function serializeForm(form, excludeNames) {
        const data = {};
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            const name = el.name;
            if (!name || excludeNames.includes(name) || el.type === 'file') return;

            if (el.type === 'checkbox' || el.type === 'radio') {
                if (!el.checked) return;
                if (name.endsWith('[]')) {
                    data[name] = data[name] || [];
                    data[name].push(el.value);
                } else {
                    data[name] = el.value;
                }
                return;
            }

            data[name] = el.value;
        });
        return data;
    }

    function applyToForm(form, data) {
        Object.keys(data).forEach(function (name) {
            const value = data[name];
            form.querySelectorAll('[name="' + CSS.escape(name) + '"]').forEach(function (el) {
                if (el.type === 'file') return;

                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = Array.isArray(value) ? value.includes(el.value) : el.value === value;
                    return;
                }

                el.value = value;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }

    function hasMeaningfulData(data, extra) {
        const fieldsHaveData = Object.values(data).some(function (v) {
            return Array.isArray(v) ? v.length > 0 : (v !== '' && v !== '0' && v != null);
        });
        if (fieldsHaveData) return true;

        if (extra && typeof extra === 'object') {
            return Object.values(extra).some(function (v) {
                if (Array.isArray(v)) return v.length > 0;
                return !!v;
            });
        }
        return false;
    }

    function init(options) {
        const opts = Object.assign({
            formId: null,
            storageKey: null,
            excludeNames: ['_token', '_method'],
            expireMinutes: 1440,
            onRestore: null,
            noticeContainer: null,
            getExtraData: null,
            setExtraData: null,
        }, options);

        if (!opts.formId || !opts.storageKey) {
            console.error('FormDraft: formId dan storageKey wajib diisi.');
            return { save: function () {}, clear: function () {}, restore: function () {} };
        }

        const form = document.getElementById(opts.formId);
        if (!form) return { save: function () {}, clear: function () {}, restore: function () {} };

        let cleared = false;

        function save() {
            if (cleared) return;

            const payload = {
                data: serializeForm(form, opts.excludeNames),
                savedAt: Date.now(),
            };
            if (typeof opts.getExtraData === 'function') {
                payload.extra = opts.getExtraData();
            }
            localStorage.setItem(opts.storageKey, JSON.stringify(payload));
        }

        function clear() {
            cleared = true;
            localStorage.removeItem(opts.storageKey);
        }

        function restore() {
            const raw = localStorage.getItem(opts.storageKey);
            if (!raw) return;

            let parsed;
            try { parsed = JSON.parse(raw); } catch (e) { return clear(); }

            const ageMinutes = (Date.now() - (parsed.savedAt || 0)) / 60000;
            if (ageMinutes > opts.expireMinutes || !parsed.data || !hasMeaningfulData(parsed.data, parsed.extra)) {
                return clear();
            }

            applyToForm(form, parsed.data);

            if (parsed.extra !== undefined && typeof opts.setExtraData === 'function') {
                opts.setExtraData(parsed.extra);
            }

            if (typeof opts.onRestore === 'function') opts.onRestore(parsed.data, parsed.extra);
            showNotice();
        }

        function showNotice() {
            const hasFile = !!form.querySelector('input[type="file"]');
            const notice = document.createElement('div');
            notice.className = 'mb-4 flex items-center justify-between rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-3 text-sm text-blue-700 dark:text-blue-300';
            notice.innerHTML =
                '<span>Data yang belum tersimpan sebelumnya berhasil dipulihkan' + (hasFile ? '. File perlu diunggah ulang.' : '.') + '</span>' +
                '<button type="button" class="ml-3 text-xs font-medium underline shrink-0">Hapus draft</button>';
            notice.querySelector('button').addEventListener('click', function () { clear(); location.reload(); });

            const container = opts.noticeContainer ? document.querySelector(opts.noticeContainer) : form.parentNode;
            if (container) container.insertBefore(notice, form);
        }

        restore();

        // 'click' ditambahkan supaya tombol tambah/hapus baris (yang tidak selalu memicu
        // event input/change langsung di form) tetap memicu penyimpanan draft.
        form.addEventListener('input', save);
        form.addEventListener('change', save);
        form.addEventListener('click', save);
        form.addEventListener('submit', clear);

        return { save: save, clear: clear, restore: restore };
    }

    window.FormDraft = { init: init };
})(window);