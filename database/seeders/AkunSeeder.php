<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriAkun;
use App\Models\Akun;

/**
 * Chart of Accounts (CoA) sesuai Laporan Tugas Akhir — Tabel IV.7.
 *
 * Struktur:
 *  - Kode header  : X-Y00  (parent_id = null)
 *  - Kode leaf    : X-Y0Z  (parent_id = header)
 *
 * Tambahan dari CoA laporan: rincian Aset Neto Dengan Pembatasan (3-2xx)
 * dipecah per jenis dana agar dana terikat tetap terinci.
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
            // ── 1. ASET ────────────────────────────────────────────────
            ['1', '1-100', 'Aset Lancar', 'DEBIT', [
                ['1-101', 'Kas Kecil', 'DEBIT'],
                ['1-102', 'Kas Infak', 'DEBIT'],
                ['1-103', 'Kas Zakat', 'DEBIT'],
                ['1-104', 'Piutang', 'DEBIT'],
                ['1-105', 'Beban Dibayar Dimuka', 'DEBIT'],
                ['1-106', 'Perlengkapan Masjid', 'DEBIT'],
            ]],
            ['1', '1-200', 'Aset Tetap', 'DEBIT', [
                ['1-201', 'Tanah Masjid', 'DEBIT'],
                ['1-202', 'Bangunan Masjid', 'DEBIT'],
                ['1-203', 'Akumulasi Penyusutan Bangunan', 'KREDIT'],
                ['1-204', 'Aset Dalam Pembangunan', 'DEBIT'],
                ['1-205', 'Investasi Jangka Panjang', 'DEBIT'],
                ['1-206', 'Peralatan Masjid', 'DEBIT'],
                ['1-207', 'Akumulasi Penyusutan Peralatan Masjid', 'KREDIT'],
            ]],

            // ── 2. LIABILITAS ──────────────────────────────────────────
            ['2', '2-100', 'Liabilitas Jangka Pendek', 'KREDIT', [
                ['2-101', 'Utang Operasional', 'KREDIT'],
                ['2-102', 'Utang Listrik', 'KREDIT'],
                ['2-103', 'Utang Air', 'KREDIT'],
                ['2-104', 'Utang Honorarium', 'KREDIT'],
                ['2-105', 'Utang Kegiatan', 'KREDIT'],
                ['2-106', 'Dana Titipan Zakat Maal', 'KREDIT'],
                ['2-107', 'Dana Titipan Zakat Fitrah', 'KREDIT'],
                ['2-108', 'Dana Titipan Qurban', 'KREDIT'],
            ]],
            ['2', '2-200', 'Liabilitas Jangka Panjang', 'KREDIT', [
                ['2-201', 'Utang Jangka Panjang', 'KREDIT'],
            ]],

            // ── 3. ASET NETO ───────────────────────────────────────────
            ['3', '3-100', 'Aset Neto Tanpa Pembatasan', 'KREDIT', [
                ['3-101', 'Saldo Awal Aset Neto', 'KREDIT'],
                ['3-102', 'Surplus/Defisit Tahun Berjalan', 'KREDIT'],
            ]],
            ['3', '3-200', 'Aset Neto Dengan Pembatasan', 'KREDIT', [
                ['3-201', 'Dana Zakat Maal', 'KREDIT'],
                ['3-202', 'Dana Zakat Fitrah', 'KREDIT'],
                ['3-203', 'Dana Wakaf', 'KREDIT'],
                ['3-204', 'Dana Pembangunan', 'KREDIT'],
                ['3-205', 'Dana Qurban', 'KREDIT'],
                ['3-206', 'Dana Program Terikat', 'KREDIT'],
            ]],

            // ── 4. PENDAPATAN ──────────────────────────────────────────
            ['4', '4-100', 'Pendapatan Tanpa Pembatasan', 'KREDIT', [
                ['4-101', 'Infak Tunai', 'KREDIT'],
                ['4-102', 'Infak Kotak Amal', 'KREDIT'],
                ['4-103', 'Infak Online', 'KREDIT'],
                ['4-104', 'Donasi Umum', 'KREDIT'],
                ['4-105', 'Pendapatan Lain-lain', 'KREDIT'],
                ['4-106', 'Keuntungan Pelepasan Aset Tetap', 'KREDIT'],
            ]],
            ['4', '4-200', 'Pendapatan Dengan Pembatasan', 'KREDIT', [
                ['4-201', 'Zakat Maal Emas & Perak', 'KREDIT'],
                ['4-202', 'Zakat Maal Uang & Tabungan', 'KREDIT'],
                ['4-203', 'Zakat Maal Perdagangan', 'KREDIT'],
                ['4-204', 'Zakat Maal Pertanian', 'KREDIT'],
                ['4-205', 'Zakat Maal Profesi', 'KREDIT'],
                ['4-206', 'Zakat Maal Ternak', 'KREDIT'],
                ['4-207', 'Zakat Maal Investasi Syariah', 'KREDIT'],
                ['4-208', 'Zakat Fitrah Beras', 'KREDIT'],
                ['4-209', 'Zakat Fitrah Uang', 'KREDIT'],
                ['4-210', 'Wakaf Tunai', 'KREDIT'],
                ['4-211', 'Wakaf Aset', 'KREDIT'],
                ['4-212', 'Dana Pembangunan', 'KREDIT'],
                ['4-213', 'Dana Qurban', 'KREDIT'],
                ['4-214', 'Donasi Terikat Program', 'KREDIT'],
            ]],

            // ── 5. BEBAN ───────────────────────────────────────────────
            ['5', '5-100', 'Beban Operasional', 'DEBIT', [
                ['5-101', 'Listrik', 'DEBIT'],
                ['5-102', 'Air', 'DEBIT'],
                ['5-103', 'Internet', 'DEBIT'],
                ['5-104', 'Kebersihan', 'DEBIT'],
                ['5-105', 'Perlengkapan Masjid', 'DEBIT'],
                ['5-106', 'Honor Imam', 'DEBIT'],
                ['5-107', 'Honor Muadzin', 'DEBIT'],
                ['5-108', 'Honor Marbot', 'DEBIT'],
            ]],
            ['5', '5-200', 'Beban Kegiatan', 'DEBIT', [
                ['5-201', 'Kajian', 'DEBIT'],
                ['5-202', 'Pengajian', 'DEBIT'],
                ['5-203', 'PHBI', 'DEBIT'],
                ['5-204', 'Konsumsi Kegiatan', 'DEBIT'],
                ['5-205', 'Kegiatan Sosial', 'DEBIT'],
            ]],
            ['5', '5-300', 'Beban Penyaluran Zakat', 'DEBIT', [
                ['5-301', 'Fakir', 'DEBIT'],
                ['5-302', 'Miskin', 'DEBIT'],
                ['5-303', 'Amil', 'DEBIT'],
                ['5-304', 'Muallaf', 'DEBIT'],
                ['5-305', 'Gharimin', 'DEBIT'],
                ['5-306', 'Fisabilillah', 'DEBIT'],
                ['5-307', 'Ibnu Sabil', 'DEBIT'],
                ['5-308', 'Penyaluran Zakat Fitrah', 'DEBIT'],
            ]],
            ['5', '5-400', 'Beban Lainnya', 'DEBIT', [
                ['5-401', 'Penyaluran Wakaf', 'DEBIT'],
                ['5-402', 'Beban Pembangunan Masjid', 'DEBIT'],
                ['5-403', 'Beban Qurban', 'DEBIT'],
                ['5-404', 'Bantuan Sosial', 'DEBIT'],
                ['5-405', 'Kerugian Pelepasan Aset Tetap', 'DEBIT'],
            ]],
            ['5', '5-500', 'Beban Pemeliharaan', 'DEBIT', [
                ['5-501', 'Perawatan Bangunan', 'DEBIT'],
                ['5-502', 'Perawatan Peralatan Masjid', 'DEBIT'],
                ['5-503', 'Perbaikan Fasilitas', 'DEBIT'],
            ]],
            ['5', '5-600', 'Beban Penyusutan', 'DEBIT', [
                ['5-601', 'Penyusutan Bangunan', 'DEBIT'],
                ['5-602', 'Penyusutan Peralatan Masjid', 'DEBIT'],
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
