<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Akun;
use App\Models\Periode;
use App\Models\Jurnal;
use App\Models\DetailJurnal;
use App\Models\Transaksi;

class JurnalUmumSeeder extends Seeder
{
    /**
     * Membuat jurnal umum HANYA untuk transaksi yang sudah dipetakan (status_jurnal = MAPPED).
     * Memperlihatkan keterhubungan transaksi -> jurnal umum:
     *  - status DRAFT  : sudah dipetakan bendahara, belum diposting.
     *  - status POSTED : sudah final (tidak dapat diubah/dihapus per aturan).
     *
     * Aturan pemetaan:
     *  - PEMASUKAN  : DEBIT kas, KREDIT akun pendapatan.
     *  - PENGELUARAN: DEBIT akun beban, KREDIT kas.
     */
    public function run(): void
    {
        // 'kode_akun' => id
        $akun = Akun::pluck('id', 'kode_akun');

        // no_referensi => [debit_kode, kredit_kode, status_jurnal, keterangan]
        $map = [
            // ── Infak kencleng (POSTED) ──────────────────────────────────────
            'INF-KCL-2026-03-001' => ['1-102', '4-102', 'POSTED', 'Penerimaan infak kotak amal (kencleng) Maret'],
            // ── Infak tunai bendahara (POSTED) ───────────────────────────────
            'INF-2026-01-001'     => ['1-102', '4-101', 'POSTED', 'Penerimaan infak tunai jamaah Januari'],
            // ── Operasional (POSTED & DRAFT) ─────────────────────────────────
            'OPS-2026-04-001'     => ['5-104', '1-101', 'POSTED', 'Beban kebersihan masjid April'],
            'OPS-2026-06-001'     => ['5-101', '1-101', 'DRAFT',  'Beban listrik masjid Mei (menunggu posting)'],
            // ── Zakat maal - pendapatan dengan pembatasan (DRAFT) ──────────────────
            'ZKT-2026-06-001'     => ['1-103', '4-202', 'DRAFT',  'Penerimaan zakat maal uang & tabungan Juni'],
            // ── Kegiatan: Qurban ───────────────────────────────────────────
            'QRB-2026-05-001'     => ['1-101', '4-213', 'POSTED', 'Penerimaan dana qurban 1447H'],
            'QRB-2026-06-001'     => ['5-403', '1-101', 'DRAFT',  'Pembelian hewan qurban (menunggu posting)'],
            // ── Kegiatan: Kajian (POSTED) ──────────────────────────────────
            'KJN-2026-02-001'     => ['5-201', '1-101', 'POSTED', 'Honorarium narasumber kajian Februari'],
            // ── Kegiatan: Bakti sosial (POSTED) ──────────────────────────────
            'SOS-2026-03-001'     => ['5-205', '1-102', 'POSTED', 'Penyaluran bakti sosial Ramadhan'],
        ];

        foreach ($map as $noReferensi => [$debit, $kredit, $status, $keterangan]) {
            $transaksi = Transaksi::where('no_referensi', $noReferensi)->first();
            if (! $transaksi) {
                continue;
            }

            // Cari periode bulanan yang memuat tanggal transaksi.
            $periode = Periode::where('tipe', 'bulanan')
                ->whereDate('tanggal_awal', '<=', $transaksi->tanggal_transaksi)
                ->whereDate('tanggal_akhir', '>=', $transaksi->tanggal_transaksi)
                ->first();

            if (! $periode) {
                $this->command->warn("Periode tidak ditemukan untuk {$noReferensi}, jurnal dilewati.");
                continue;
            }

            $jurnal = Jurnal::create([
                'periode_id'   => $periode->id,
                'transaksi_id' => $transaksi->id,
                'tanggal'      => $transaksi->tanggal_transaksi,
                'jenis_jurnal' => 'UMUM',
                'keterangan'   => $keterangan,
                'status'       => $status,
            ]);

            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id'   => $akun[$debit],
                'tipe'      => 'DEBIT',
                'nominal'   => $transaksi->jumlah,
            ]);

            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id'   => $akun[$kredit],
                'tipe'      => 'KREDIT',
                'nominal'   => $transaksi->jumlah,
            ]);

            // Pastikan status_jurnal konsisten.
            $transaksi->update(['status_jurnal' => 'MAPPED']);
        }
    }
}
