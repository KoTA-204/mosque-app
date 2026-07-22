/**
 * jurnal-shared.js
 *
 * Fungsi-fungsi reusable yang dipakai di seluruh halaman jurnal:
 *   - Drawer (showDrawer, closeDrawer)
 *   - Bulk selection (toggleAll, updateBulkBar, clearSelection, submitBulkPost)
 *   - Render helper (formatRp, statusBadge, buildDetailRows, drawerLoading, drawerError)
 *
 * Cara pakai di setiap halaman:
 *   1. Sertakan file ini via @vite atau <script src>
 *   2. Definisikan window.renderDrawerContent(data) yang berisi logika spesifik halaman
 *   3. Panggil showDrawer(id, '/dashboard/jurnal-xxx/'+id) dari template
 */

// ─── Drawer ──────────────────────────────────────────────────────────────────

/**
 * Buka drawer dan fetch data dari URL yang diberikan.
 *
 * @param {string} url - endpoint JSON untuk data detail
 */
function showDrawer(url) {
    const overlay = document.getElementById('drawerOverlay');
    const drawer  = document.getElementById('drawer');
    const content = document.getElementById('drawerContent');

    overlay.classList.remove('hidden');
    drawer.classList.remove('translate-x-full');
    drawer.classList.add('translate-x-0');
    content.innerHTML = drawerLoading();

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        // Setiap halaman mendefinisikan window.renderDrawerContent(data)
        if (typeof window.renderDrawerContent === 'function') {
            window.renderDrawerContent(data);
        }
    })
    .catch(err => {
        content.innerHTML = drawerError(err.message);
    });
}

function closeDrawer() {
    document.getElementById('drawerOverlay').classList.add('hidden');
    document.getElementById('drawer').classList.remove('translate-x-0');
    document.getElementById('drawer').classList.add('translate-x-full');
}

// Tutup drawer dengan tombol Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDrawer();
});

// ─── Render Helpers ───────────────────────────────────────────────────────────

/**
 * Format angka ke format Rupiah: "Rp 1.000.000"
 * @param {number|string} n
 * @returns {string}
 */
function formatRp(n) {
    return 'Rp ' + parseFloat(n || 0).toLocaleString('id-ID');
}

/**
 * Kembalikan HTML badge status Posted / Draft
 * @param {boolean} isPosted
 * @returns {string}
 */
function statusBadge(isPosted) {
    return isPosted
        ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-600">
               <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Posted
           </span>`
        : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-600">
               <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 inline-block"></span>Draft
           </span>`;
}

/**
 * Build baris-baris <tr> untuk tabel debit/kredit di drawer.
 * @param {Array} details - array detailJurnal dari response JSON
 * @returns {string} HTML string
 */
function buildDetailRows(details) {
    return details.map(d => `
        <tr class="border-b border-gray-50 dark:border-gray-800">
            <td class="py-2.5 text-sm text-gray-800 dark:text-gray-200">
                ${d.akun?.kode_akun
                    ? `<span class="text-xs text-gray-400 mr-1">${d.akun.kode_akun}</span>`
                    : ''}
                ${d.akun?.nama_akun ?? '—'}
            </td>
            <td class="py-2.5 text-center">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold
                    ${d.tipe === 'DEBIT' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600'}">
                    ${d.tipe === 'DEBIT' ? 'D' : 'K'}
                </span>
            </td>
            <td class="py-2.5 text-right text-sm ${d.tipe === 'DEBIT' ? 'text-red-600 font-medium' : 'text-gray-300 dark:text-gray-600'}">
                ${d.tipe === 'DEBIT' ? formatRp(d.nominal) : '—'}
            </td>
            <td class="py-2.5 text-right text-sm ${d.tipe === 'KREDIT' ? 'text-green-600 font-medium' : 'text-gray-300 dark:text-gray-600'}">
                ${d.tipe === 'KREDIT' ? formatRp(d.nominal) : '—'}
            </td>
        </tr>
    `).join('');
}

/**
 * HTML tabel debit/kredit lengkap dengan header dan footer total.
 * @param {Array}  details
 * @param {string} sectionTitle  (default: 'Entri Jurnal')
 * @returns {string}
 */
function buildDetailTable(details, sectionTitle = 'Entri Jurnal') {
    const totalDebit  = details
        .filter(d => d.tipe === 'DEBIT')
        .reduce((s, d) => s + parseFloat(d.nominal), 0);
    const totalKredit = details
        .filter(d => d.tipe === 'KREDIT')
        .reduce((s, d) => s + parseFloat(d.nominal), 0);

    return `
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">
                ${sectionTitle}
            </p>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-2 text-left   text-xs font-semibold text-gray-400">Akun</th>
                        <th class="pb-2 text-center text-xs font-semibold text-gray-400">Pos.</th>
                        <th class="pb-2 text-right  text-xs font-semibold text-gray-400">Debit</th>
                        <th class="pb-2 text-right  text-xs font-semibold text-gray-400">Kredit</th>
                    </tr>
                </thead>
                <tbody>${buildDetailRows(details)}</tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-100 dark:border-gray-800">
                        <td colspan="2" class="pt-3 text-sm font-semibold text-gray-800 dark:text-gray-200">
                            Total
                        </td>
                        <td class="pt-3 text-right text-sm font-bold text-red-600">
                            ${formatRp(totalDebit)}
                        </td>
                        <td class="pt-3 text-right text-sm font-bold text-green-600">
                            ${formatRp(totalKredit)}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>`;
}

/**
 * Header drawer: nomor jurnal + tanggal + badge status
 * @param {string}  nomorJurnal
 * @param {string}  tanggal
 * @param {boolean} isPosted
 * @returns {string}
 */
function buildDrawerHeader(nomorJurnal, tanggal, isPosted) {
    return `
        <div class="mb-5">
            <p class="font-mono text-xl font-bold text-green-600 dark:text-green-400 mb-1">
                ${nomorJurnal ?? '—'}
            </p>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-400">${tanggal ?? '—'}</span>
                ${statusBadge(isPosted)}
            </div>
        </div>`;
}

/**
 * Info box generik (key-value list) di dalam drawer.
 * @param {string} sectionTitle
 * @param {Array}  rows  — array of { label, value }
 * @returns {string}
 */
function buildInfoBox(sectionTitle, rows) {
    const items = rows.map(r => `
        <div class="flex justify-between items-start gap-4">
            <span class="text-sm text-gray-400 shrink-0">${r.label}</span>
            <span class="text-sm font-medium text-gray-800 dark:text-gray-200 text-right max-w-[220px]">
                ${r.value ?? '—'}
            </span>
        </div>
    `).join('');

    return `
        <div class="mb-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">
                ${sectionTitle}
            </p>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl px-4 py-3 space-y-2.5">
                ${items}
            </div>
        </div>`;
}

/** Loading spinner HTML */
function drawerLoading() {
    return `
        <div class="flex items-center justify-center py-10 text-gray-400 dark:text-gray-600 gap-2">
            <svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            <span class="text-sm">Memuat...</span>
        </div>`;
}

/** Error state HTML */
function drawerError(msg = '') {
    return `<p class="text-center text-sm text-red-500 py-10">
                Gagal memuat data${msg ? ' (' + msg + ')' : ''}.
            </p>`;
}

// ─── Bulk Selection ───────────────────────────────────────────────────────────

function toggleAll(source) {
    document.querySelectorAll('.row-check').forEach(cb => {
        cb.checked = source.checked;
    });
    updateBulkBar();
}

function updateBulkBar() {
    const checked  = document.querySelectorAll('.row-check:checked');
    const allBoxes = document.querySelectorAll('.row-check');
    const bar      = document.getElementById('bulkActionBar');
    const badge    = document.getElementById('bulkCountBadge');
    const checkAll = document.getElementById('checkAll');

    if (checked.length > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
    } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
    }

    badge.textContent = checked.length;

    if (allBoxes.length > 0 && checked.length === allBoxes.length) {
        checkAll.checked       = true;
        checkAll.indeterminate = false;
    } else if (checked.length > 0) {
        checkAll.checked       = false;
        checkAll.indeterminate = true;
    } else {
        checkAll.checked       = false;
        checkAll.indeterminate = false;
    }
}

function clearSelection() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = false);
    updateBulkBar();
}

/**
 * Submit form bulk-post.
 * @param {string} formId      - id form (default: 'bulkForm')
 * @param {string} containerId - id container hidden inputs (default: 'bulkInputsContainer')
 */
async function submitBulkPost(formId = 'bulkForm', containerId = 'bulkInputsContainer') {
    const checked = document.querySelectorAll('.row-check:checked');
    if (checked.length === 0) return;
    if (!await confirmAsync(`Posting ${checked.length} jurnal yang dipilih? Aksi ini tidak dapat dibatalkan.`, { title: 'Posting Jurnal', confirmLabel: 'Posting', confirmClass: 'bg-green-600 hover:bg-green-700' })) return;

    const container = document.getElementById(containerId);
    container.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });

    document.getElementById(formId).submit();
}

/* ─── Auto-buka drawer via query ?buka= ────────────────────────────
   Halaman index men-set window.drawerDetailBase, mis. '/dashboard/jurnal-umum/'.
   Dipakai oleh tautan "Buka & posting" dari daftar draft penghambat penutupan. */
document.addEventListener('DOMContentLoaded', function () {
    try {
        var params = new URLSearchParams(window.location.search);
        var buka = params.get('buka');
        if (buka && window.drawerDetailBase && typeof showDrawer === 'function') {
            showDrawer(window.drawerDetailBase + encodeURIComponent(buka));
        }
    } catch (e) {}
});
