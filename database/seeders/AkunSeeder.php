<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriAkun;
use App\Models\Akun;

/**
 * Chart of Accounts (CoA) - PENOMORAN 4 DIGIT: "K-CFUU".
 *
 *   K  = kategori akun (1 Aset, 2 Liabilitas, 3 Aset Neto, 4 Pendapatan, 5 Beban)
 *   C  = KELAS pembatasan (digit ribuan):
 *          1 = Tanpa Pembatasan (Dana Umum / operasional)
 *          2 = Dengan Pembatasan (SEMUA dana terikat berkumpul di sini)
 *        Untuk kategori 1 & 2 (Aset & Liabilitas) yang tak mengenal pembatasan,
 *        C hanya dipakai sebagai nomor kelompok biasa (1, 2, ...).
 *   F  = INDEKS dana terikat (digit ratusan), hanya bermakna saat C = 2:
 *          1 = Zakat Maal   2 = Zakat Fitrah   3 = Wakaf
 *          4 = Pembangunan  5 = Qurban         6 = Program Terikat
 *        Untuk beban Tanpa Pembatasan (C = 1), F dipakai sebagai sub-grup
 *        fungsional (operasional, kegiatan, pemeliharaan, dst).
 *   UU = nomor urut akun dalam kelompoknya.
 *
 * Kenapa C dipisah dari F?
 *   - Modul LAPORAN mengklasifikasi lewat digit KELAS: "x-1..." = Tanpa
 *     Pembatasan, "x-2..." = Dengan Pembatasan. Seluruh dana terikat berkumpul
 *     di "x-2..." sehingga laporan (posisi keuangan, penghasilan komprehensif,
 *     perubahan aset neto, arus kas, dashboard) tetap benar tanpa diubah.
 *   - JurnalPenutupService MENURUNKAN dana tujuan penutupan dari kode akun:
 *     kelas (C) menentukan terikat/tidak, indeks dana (F) menentukan dana mana.
 *     Tidak ada peta hardcode.
 *
 * Akun saldo aset neto tiap dana:
 *   Dana Umum (Tanpa Pembatasan) -> 3-1001 (Surplus/Defisit Tahun Berjalan)
 *   Dana terikat F               -> 3-2F01  (mis. Zakat Maal = 3-2101)
 */
class AkunSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            '1' => KategoriAkun::where('kode_kategori', '1')->first(), // Aset
            '2' => KategoriAkun::where('kode_kategori', '2')->first(), // Liabilitas
            '3' => KategoriAkun::where('kode_kategori', '3')->first(), // Aset Neto
            '4' => KategoriAkun::where('kode_kategori', '4')->first(), // Pendapatan
            '5' => KategoriAkun::where('kode_kategori', '5')->first(), // Beban
        ];

        // Struktur CoA: setiap grup = [katKode, headerKode, headerNama, normalHeader, [leaf...]]
        // leaf = [kode, nama, saldo_normal]
        $struktur = [
            // ── 1. ASET (C = nomor kelompok; tanpa konsep pembatasan) ──────
            ['1', '1-1000', 'Aset Lancar', 'DEBIT', [
                ['1-1001', 'Kas Kecil', 'DEBIT'],
                ['1-1002', 'Kas Infak', 'DEBIT'],
                ['1-1003', 'Kas Zakat', 'DEBIT'],
                ['1-1004', 'Piutang', 'DEBIT'],
                ['1-1005', 'Beban Dibayar Dimuka', 'DEBIT'],
                ['1-1006', 'Perlengkapan Masjid', 'DEBIT'],
            ]],
            ['1', '1-2000', 'Aset Tetap', 'DEBIT', [
                ['1-2001', 'Tanah Masjid', 'DEBIT'],
                ['1-2002', 'Bangunan Masjid', 'DEBIT'],
                ['1-2003', 'Akumulasi Penyusutan Bangunan', 'KREDIT'],
                ['1-2004', 'Aset Dalam Pembangunan', 'DEBIT'],
                ['1-2005', 'Investasi Jangka Panjang', 'DEBIT'],
                ['1-2006', 'Peralatan Masjid', 'DEBIT'],
                ['1-2007', 'Akumulasi Penyusutan Peralatan Masjid', 'KREDIT'],
            ]],

            // ── 2. LIABILITAS (C = nomor kelompok) ────────────
            ['2', '2-1000', 'Liabilitas Jangka Pendek', 'KREDIT', [
                ['2-1001', 'Utang Operasional', 'KREDIT'],
                ['2-1002', 'Utang Listrik', 'KREDIT'],
                ['2-1003', 'Utang Air', 'KREDIT'],
                ['2-1004', 'Utang Honorarium', 'KREDIT'],
                ['2-1005', 'Utang Kegiatan', 'KREDIT'],
                ['2-1006', 'Dana Titipan Zakat Maal', 'KREDIT'],
                ['2-1007', 'Dana Titipan Zakat Fitrah', 'KREDIT'],
                ['2-1008', 'Dana Titipan Qurban', 'KREDIT'],
            ]],
            ['2', '2-2000', 'Liabilitas Jangka Panjang', 'KREDIT', [
                ['2-2001', 'Utang Jangka Panjang', 'KREDIT'],
            ]],

            // ── 3. ASET NETO (C=1 Tanpa Pembatasan; C=2 Dengan Pembatasan) ─
            ['3', '3-1000', 'Aset Neto Tanpa Pembatasan', 'KREDIT', [
                ['3-1001', 'Surplus/Defisit Tahun Berjalan', 'KREDIT'], // target penutupan Dana Umum
                ['3-1002', 'Saldo Awal Aset Neto', 'KREDIT'],
            ]],
            ['3', '3-2000', 'Aset Neto Dengan Pembatasan', 'KREDIT', [
                ['3-2101', 'Dana Zakat Maal', 'KREDIT'],       // F=1
                ['3-2201', 'Dana Zakat Fitrah', 'KREDIT'],     // F=2
                ['3-2301', 'Dana Wakaf', 'KREDIT'],            // F=3
                ['3-2401', 'Dana Pembangunan', 'KREDIT'],      // F=4
                ['3-2501', 'Dana Qurban', 'KREDIT'],           // F=5
                ['3-2601', 'Dana Program Terikat', 'KREDIT'],  // F=6
            ]],

            // ── 4. PENDAPATAN (C=1 Tanpa Pembatasan; C=2 Dengan Pembatasan) ─
            ['4', '4-1000', 'Pendapatan Tanpa Pembatasan', 'KREDIT', [
                ['4-1001', 'Infak Tunai', 'KREDIT'],
                ['4-1002', 'Infak Kotak Amal', 'KREDIT'],
                ['4-1003', 'Infak Online', 'KREDIT'],
                ['4-1004', 'Donasi Umum', 'KREDIT'],
                ['4-1005', 'Pendapatan Lain-lain', 'KREDIT'],
                ['4-1006', 'Keuntungan Pelepasan Aset Tetap', 'KREDIT'],
            ]],
            ['4', '4-2000', 'Pendapatan Dengan Pembatasan', 'KREDIT', [
                // Zakat Maal (F=1)
                ['4-2101', 'Zakat Maal Emas & Perak', 'KREDIT'],
                ['4-2102', 'Zakat Maal Uang & Tabungan', 'KREDIT'],
                ['4-2103', 'Zakat Maal Perdagangan', 'KREDIT'],
                ['4-2104', 'Zakat Maal Pertanian', 'KREDIT'],
                ['4-2105', 'Zakat Maal Profesi', 'KREDIT'],
                ['4-2106', 'Zakat Maal Ternak', 'KREDIT'],
                ['4-2107', 'Zakat Maal Investasi Syariah', 'KREDIT'],
                // Zakat Fitrah (F=2)
                ['4-2201', 'Zakat Fitrah Beras', 'KREDIT'],
                ['4-2202', 'Zakat Fitrah Uang', 'KREDIT'],
                // Wakaf (F=3)
                ['4-2301', 'Wakaf Tunai', 'KREDIT'],
                ['4-2302', 'Wakaf Aset', 'KREDIT'],
                // Pembangunan (F=4)
                ['4-2401', 'Dana Pembangunan', 'KREDIT'],
                // Qurban (F=5)
                ['4-2501', 'Dana Qurban', 'KREDIT'],
                // Program Terikat (F=6)
                ['4-2601', 'Donasi Terikat Program', 'KREDIT'],
            ]],

            // ── 5. BEBAN ───────────────────────
            // C=1 Tanpa Pembatasan (F = sub-grup fungsional).
            ['5', '5-1100', 'Beban Operasional', 'DEBIT', [
                ['5-1101', 'Listrik', 'DEBIT'],
                ['5-1102', 'Air', 'DEBIT'],
                ['5-1103', 'Internet', 'DEBIT'],
                ['5-1104', 'Kebersihan', 'DEBIT'],
                ['5-1105', 'Perlengkapan Masjid', 'DEBIT'],
                ['5-1106', 'Honor Imam', 'DEBIT'],
                ['5-1107', 'Honor Muadzin', 'DEBIT'],
                ['5-1108', 'Honor Marbot', 'DEBIT'],
            ]],
            ['5', '5-1200', 'Beban Kegiatan', 'DEBIT', [
                ['5-1201', 'Kajian', 'DEBIT'],
                ['5-1202', 'Pengajian', 'DEBIT'],
                ['5-1203', 'PHBI', 'DEBIT'],
                ['5-1204', 'Konsumsi Kegiatan', 'DEBIT'],
                ['5-1205', 'Kegiatan Sosial', 'DEBIT'],
            ]],
            ['5', '5-1300', 'Beban Pemeliharaan', 'DEBIT', [
                ['5-1301', 'Perawatan Bangunan', 'DEBIT'],
                ['5-1302', 'Perawatan Peralatan Masjid', 'DEBIT'],
                ['5-1303', 'Perbaikan Fasilitas', 'DEBIT'],
            ]],
            ['5', '5-1400', 'Beban Penyusutan', 'DEBIT', [
                ['5-1401', 'Penyusutan Bangunan', 'DEBIT'],
                ['5-1402', 'Penyusutan Peralatan Masjid', 'DEBIT'],
            ]],
            ['5', '5-1500', 'Beban Lain Tanpa Pembatasan', 'DEBIT', [
                ['5-1501', 'Bantuan Sosial', 'DEBIT'],
                ['5-1502', 'Kerugian Pelepasan Aset Tetap', 'DEBIT'],
            ]],
            // C=2 Dengan Pembatasan: beban penyaluran per dana (F). Dilepas dari 3-2F01.
            ['5', '5-2100', 'Beban Dana Zakat Maal', 'DEBIT', [
                ['5-2101', 'Penyaluran Zakat Fakir', 'DEBIT'],
                ['5-2102', 'Penyaluran Zakat Miskin', 'DEBIT'],
                ['5-2103', 'Penyaluran Zakat Amil', 'DEBIT'],
                ['5-2104', 'Penyaluran Zakat Muallaf', 'DEBIT'],
                ['5-2105', 'Penyaluran Zakat Gharimin', 'DEBIT'],
                ['5-2106', 'Penyaluran Zakat Fisabilillah', 'DEBIT'],
                ['5-2107', 'Penyaluran Zakat Ibnu Sabil', 'DEBIT'],
            ]],
            ['5', '5-2200', 'Beban Dana Zakat Fitrah', 'DEBIT', [
                ['5-2201', 'Penyaluran Zakat Fitrah', 'DEBIT'],
            ]],
            ['5', '5-2300', 'Beban Dana Wakaf', 'DEBIT', [
                ['5-2301', 'Penyaluran Wakaf', 'DEBIT'],
            ]],
            ['5', '5-2400', 'Beban Dana Pembangunan', 'DEBIT', [
                ['5-2401', 'Beban Pembangunan Masjid', 'DEBIT'],
            ]],
            ['5', '5-2500', 'Beban Dana Qurban', 'DEBIT', [
                ['5-2501', 'Beban Qurban', 'DEBIT'],
            ]],
        ];

        foreach ($struktur as [$katKode, $headerKode, $headerNama, $normalHeader, $leaves]) {
            $header = Akun::create([
                'kategori_akun_id' => $kategori[$katKode]->id,
                'parent_id'        => null,
                'kode_akun'        => $headerKode,
                'nama_akun'        => $headerNama,
                'saldo_normal'     => $normalHeader,
            ]);

            foreach ($leaves as [$kode, $nama, $normal]) {
                Akun::create([
                    'kategori_akun_id' => $kategori[$katKode]->id,
                    'parent_id'        => $header->id,
                    'kode_akun'        => $kode,
                    'nama_akun'        => $nama,
                    'saldo_normal'     => $normal,
                ]);
            }
        }
    }
}
