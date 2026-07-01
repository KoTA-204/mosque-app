<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaksi;
use App\Models\Dompet;
use App\Models\User;
use App\Models\Kegiatan;
use App\Models\KategoriTransaksi;

class TransaksiKegiatanSeeder extends Seeder
{
    /**
     * Transaksi yang TERHUBUNG ke kegiatan khusus (kegiatan_id terisi).
     * Dicatat panitia khusus & memakai kategori_transaksi untuk memudahkan
     * pemetaan akun debit/kredit oleh bendahara 1.
     *
     * - Mayoritas lewat approval -> status_approval = APPROVED.
     * - Satu contoh masih menunggu persetujuan -> status_approval = PENDING
     *   (sesuai aturan: hanya yang APPROVED yang boleh dipetakan ke jurnal).
     */
    public function run(): void
    {
        $kasKecil = Dompet::where('nama_dompet', 'Kas Kecil')->value('id');
        $kasInfak = Dompet::where('nama_dompet', 'Kas Infak')->value('id');
        $panitia  = User::where('email', 'panitia@masjid.id')->value('id');

        $qurban = Kegiatan::where('nama_kegiatan', 'Qurban Idul Adha 1447H')->value('id');
        $kajian = Kegiatan::where('nama_kegiatan', 'Kajian Rutin & PHBI 2026')->value('id');
        $baksos = Kegiatan::where('nama_kegiatan', 'Bakti Sosial Ramadhan 2026')->value('id');

        $kat = fn (string $nama) => KategoriTransaksi::where('nama_kategori', $nama)->value('id');

        $data = [
            // ── QURBAN ────────────────────────────────────────────────────────────
            [
                'no_referensi'          => 'QRB-2026-05-001',
                'dompet_id'             => $kasKecil,
                'kegiatan_id'           => $qurban,
                'user_id'               => $panitia,
                'kategori_transaksi_id' => $kat('Penerimaan Dana Qurban'),
                'tanggal_transaksi'     => '2026-05-10',
                'jenis_transaksi'       => 'PEMASUKAN',
                'jumlah'                => 36000000,
                'deskripsi'             => 'Penerimaan dana qurban dari 24 shohibul qurban',
                'catatan'               => null,
                'status_approval'       => 'APPROVED',
                'status_jurnal'         => 'MAPPED',   // POSTED
            ],
            [
                'no_referensi'          => 'QRB-2026-06-001',
                'dompet_id'             => $kasKecil,
                'kegiatan_id'           => $qurban,
                'user_id'               => $panitia,
                'kategori_transaksi_id' => $kat('Operasional Qurban'),
                'tanggal_transaksi'     => '2026-06-05',
                'jenis_transaksi'       => 'PENGELUARAN',
                'jumlah'                => 33000000,
                'deskripsi'             => 'Pembelian 11 ekor hewan qurban',
                'catatan'               => null,
                'status_approval'       => 'APPROVED',
                'status_jurnal'         => 'MAPPED',   // DRAFT (belum diposting)
            ],
            [
                'no_referensi'          => 'QRB-2026-06-002',
                'dompet_id'             => $kasKecil,
                'kegiatan_id'           => $qurban,
                'user_id'               => $panitia,
                'kategori_transaksi_id' => $kat('Penerimaan Dana Qurban'),
                'tanggal_transaksi'     => '2026-06-22',
                'jenis_transaksi'       => 'PEMASUKAN',
                'jumlah'                => 4000000,
                'deskripsi'             => 'Tambahan dana qurban menyusul (menunggu persetujuan)',
                'catatan'               => 'Diajukan panitia, menunggu approval bendahara',
                'status_approval'       => 'PENDING',
                'status_jurnal'         => 'UNMAPPED', // belum approved -> belum bisa dijurnal
            ],

            // ── KAJIAN ────────────────────────────────────────────────────────────
            [
                'no_referensi'          => 'KJN-2026-02-001',
                'dompet_id'             => $kasKecil,
                'kegiatan_id'           => $kajian,
                'user_id'               => $panitia,
                'kategori_transaksi_id' => $kat('Honorarium Narasumber'),
                'tanggal_transaksi'     => '2026-02-20',
                'jenis_transaksi'       => 'PENGELUARAN',
                'jumlah'                => 2000000,
                'deskripsi'             => 'Honorarium narasumber kajian rutin Februari',
                'catatan'               => null,
                'status_approval'       => 'APPROVED',
                'status_jurnal'         => 'MAPPED',   // POSTED
            ],
            [
                'no_referensi'          => 'KJN-2026-06-001',
                'dompet_id'             => $kasKecil,
                'kegiatan_id'           => $kajian,
                'user_id'               => $panitia,
                'kategori_transaksi_id' => $kat('Konsumsi Kegiatan'),
                'tanggal_transaksi'     => '2026-06-08',
                'jenis_transaksi'       => 'PENGELUARAN',
                'jumlah'                => 1500000,
                'deskripsi'             => 'Konsumsi kajian rutin pekan ke-1 Juni',
                'catatan'               => 'Sudah disetujui, menunggu pemetaan akun bendahara',
                'status_approval'       => 'APPROVED',
                'status_jurnal'         => 'UNMAPPED', // belum ada jurnal
            ],

            // ── BAKTI SOSIAL ───────────────────────────────────────────────────────
            [
                'no_referensi'          => 'SOS-2026-03-001',
                'dompet_id'             => $kasInfak,
                'kegiatan_id'           => $baksos,
                'user_id'               => $panitia,
                'kategori_transaksi_id' => $kat('Beban Kegiatan Sosial'),
                'tanggal_transaksi'     => '2026-03-20',
                'jenis_transaksi'       => 'PENGELUARAN',
                'jumlah'                => 10000000,
                'deskripsi'             => 'Penyaluran paket bakti sosial Ramadhan',
                'catatan'               => null,
                'status_approval'       => 'APPROVED',
                'status_jurnal'         => 'MAPPED',   // POSTED
            ],
        ];

        foreach ($data as $row) {
            Transaksi::create($row);
        }
    }
}
