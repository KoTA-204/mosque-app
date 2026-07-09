/**
 * jurnal-helpers.js
 * Fungsi-fungsi JS yang dipakai bersama oleh modul jurnal:
 *   - jurnal-penyesuaian
 *   - jurnal-koreksi
 *   - jurnal-penutup
 *
 * Cara pakai:
 *   import { parseNominal, formatRp, formatInput, makeStepperController, makeBalanceController } from './jurnal-helpers.js';
 *
 * Atau jika tidak pakai bundler (Vite/Mix), include via <script type="module"> dan import langsung.
 */

// ─────────────────────────────────────────────
// Format helpers
// ─────────────────────────────────────────────

/**
 * Parsing string nominal "1.500.000" → number 1500000.
 * Aman untuk string kosong / undefined.
 */
export function parseNominal(val) {
    if (typeof val === 'number') return Math.round(val * 100) / 100; 
    const cleaned = String(val || '0')
        .replace(/\./g, '')       
        .replace(',', '.')       
        .replace(/[^\d.]/g, '');   
    const n = parseFloat(cleaned);
    return isNaN(n) ? 0 : Math.round(n * 100) / 100;
}

/**
 * Format angka ke "Rp 1.500.000" (locale id-ID).
 */
export function formatRp(n) {
    return 'Rp ' + parseNominal(n).toLocaleString('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

/**
 * Format <input> nominal saat oninput:
 *   "1500000" → "1.500.000"
 * Gunakan: oninput="formatInput(this)"
 */
export function formatInput(input) {
    let v = input.value.replace(/[^\d,]/g, ''); // hanya digit & koma

    const i = v.indexOf(',');
    if (i !== -1) {
        v = v.slice(0, i + 1) + v.slice(i + 1).replace(/,/g, '');
    }

    let [intPart, decPart] = v.split(',');
    const intFmt = intPart ? parseInt(intPart, 10).toLocaleString('id-ID') : '';

    input.value = decPart !== undefined
        ? intFmt + ',' + decPart.slice(0, 2)   // maks 2 desimal
        : intFmt;
}

// ─────────────────────────────────────────────
// Stepper controller
// ─────────────────────────────────────────────

/**
 * makeStepperController(totalSteps)
 *
 * Membuat controller stepper yang mengelola:
 *   - show/hide div#step{n}
 *   - update #step-circle-{n} dan #step-line-{n}
 *
 * @param {number} totalSteps — jumlah langkah (mis. 2 atau 3)
 * @returns {{ currentStep: number, goToStep: function, current: function }}
 *
 * Contoh:
 *   const stepper = makeStepperController(3);
 *   stepper.goToStep(2);
 */
export function makeStepperController(totalSteps) {
    let currentStep = 1;

    function updateUI() {
        for (let i = 1; i <= totalSteps; i++) {
            const circle = document.getElementById('step-circle-' + i);
            if (!circle) continue;

            if (i < currentStep) {
                circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-green-600 text-white transition-colors';
                circle.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
            } else if (i === currentStep) {
                circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-green-600 text-white transition-colors';
                circle.textContent = i;
            } else {
                circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 transition-colors';
                circle.textContent = i;
            }

            if (i < totalSteps) {
                const line = document.getElementById('step-line-' + i);
                if (line) {
                    line.className = i < currentStep
                        ? 'mx-4 flex-1 border-t-2 border-green-500 transition-colors'
                        : 'mx-4 flex-1 border-t-2 border-gray-100 dark:border-gray-800 transition-colors';
                }
            }
        }
    }

    function goToStep(n) {
        const from = document.getElementById('step' + currentStep);
        const to   = document.getElementById('step' + n);
        if (from) from.classList.add('hidden');
        if (to)   to.classList.remove('hidden');
        currentStep = n;
        updateUI();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    return {
        get currentStep() { return currentStep; },
        goToStep,
    };
}

// ─────────────────────────────────────────────
// Balance controller
// ─────────────────────────────────────────────

/**
 * makeBalanceController(getRowsFn, options)
 *
 * Membuat controller yang menghitung dan menampilkan balance debit/kredit.
 *
 * @param {function} getRowsFn
 *   Fungsi yang mengembalikan array row saat ini.
 *   Tiap row harus punya: { tipe: 'DEBIT'|'KREDIT', nominal: string|number }
 *
 * @param {object} options
 *   prefix      — prefix id elemen (default '')
 *                 '' → #totalDebit, #totalKredit, #balanceStatus
 *                 'tahap' → #tahapTotalDebit, ...
 *
 * @returns {{ recalc: function, calcTotal: function, isBalanced: function }}
 *
 * Contoh:
 *   const balance = makeBalanceController(() => detailRows);
 *   balance.recalc();
 *   if (balance.isBalanced()) { ... }
 */
export function makeBalanceController(getRowsFn, { prefix = '' } = {}) {
    const idDebit  = prefix + 'TotalDebit';
    const idKredit = prefix + 'TotalKredit';
    const idStatus = prefix + 'BalanceStatus';

    function calcTotal() {
        let debitCents = 0, kreditCents = 0;
        for (const row of getRowsFn()) {
            const c = Math.round(parseNominal(row.nominal) * 100); // → sen
            if (row.tipe === 'DEBIT')  debitCents  += c;
            if (row.tipe === 'KREDIT') kreditCents += c;
        }
        return {
            debit:  debitCents / 100,
            kredit: kreditCents / 100,
            debitCents,
            kreditCents,
        };
    }

    function isBalanced() {
        const { debitCents, kreditCents } = calcTotal();
        return debitCents > 0 && debitCents === kreditCents; // perbandingan eksak
    }

    function recalc() {
        const { debit, kredit } = calcTotal();

        const elDebit  = document.getElementById(idDebit);
        const elKredit = document.getElementById(idKredit);
        const elStatus = document.getElementById(idStatus);

        if (elDebit)  elDebit.textContent  = formatRp(debit);
        if (elKredit) elKredit.textContent = formatRp(kredit);

        if (!elStatus) return;

        if (isBalanced()) {
            elStatus.className = 'flex items-center gap-1.5 text-xs font-medium text-green-600';
            elStatus.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Seimbang`;
        } else {
            elStatus.className = 'flex items-center gap-1.5 text-xs font-medium text-yellow-600';
            elStatus.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Belum seimbang`;
        }
    }

    return { calcTotal, isBalanced, recalc };
}

// ─────────────────────────────────────────────
// Review table renderer
// ─────────────────────────────────────────────

/**
 * renderReviewRows(rows, getAkunLabelFn, options)
 *
 * Mengisi <tbody> tabel review dengan baris akun/debit/kredit,
 * dan update elemen total debit/kredit serta btnPosting.
 *
 * @param {Array}    rows            — array detailRows
 * @param {function} getAkunLabelFn  — fn(akunId) → string nama akun
 * @param {object}   options
 *   bodyId        — id <tbody>. Default: 'reviewBody'
 *   totalDebitId  — id elemen total debit. Default: 'review_total_debit'
 *   totalKreditId — id elemen total kredit. Default: 'review_total_kredit'
 *   postingBtnId  — id tombol posting. Default: 'btnPosting'
 */
export function renderReviewRows(rows, getAkunLabelFn, {
    bodyId        = 'reviewBody',
    totalDebitId  = 'review_total_debit',
    totalKreditId = 'review_total_kredit',
    postingBtnId  = 'btnPosting',
} = {}) {
    let totalDCents = 0, totalKCents = 0;

    const tbody = document.getElementById(bodyId);
    if (!tbody) return;

    tbody.innerHTML = rows.map(row => {
        const n = parseNominal(row.nominal);      // number, sudah dibulatkan 2 desimal
        const c = Math.round(n * 100);            // → sen
        if (row.tipe === 'DEBIT')  totalDCents += c;
        if (row.tipe === 'KREDIT') totalKCents += c;

        return `
            <tr class="border-b border-gray-50 dark:border-gray-800">
                <td class="py-2 text-gray-700 dark:text-gray-300 text-sm">${getAkunLabelFn(row.akun_id)}</td>
                <td class="py-2 text-right text-sm ${row.tipe === 'DEBIT'  ? 'text-red-500 font-medium'   : 'text-gray-300'}">${row.tipe === 'DEBIT'  ? formatRp(n) : '—'}</td>
                <td class="py-2 text-right text-sm ${row.tipe === 'KREDIT' ? 'text-green-600 font-medium' : 'text-gray-300'}">${row.tipe === 'KREDIT' ? formatRp(n) : '—'}</td>
            </tr>`;
    }).join('');

    const elD = document.getElementById(totalDebitId);
    const elK = document.getElementById(totalKreditId);
    const btn = document.getElementById(postingBtnId);

    if (elD) elD.textContent = formatRp(totalDCents / 100);
    if (elK) elK.textContent = formatRp(totalKCents / 100);
    if (btn) btn.disabled = totalDCents !== totalKCents;   // perbandingan eksak dalam sen
}