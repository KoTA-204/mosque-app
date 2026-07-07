<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaksi;
use App\Models\Dompet;
use App\Models\User;
use App\Models\KategoriTransaksi;

class TransaksiSeeder extends Seeder
{
    /**
     * Transaksi NON-kegiatan:
     *  (A) Infak kencleng  -> lewat approval (status_approval = APPROVED), punya record kencleng.
     *  (B) Pencatatan bendahara langsung -> status_approval = null (tidak butuh approval).
     *
     * Kondisi mapping akun:
     *  - status_jurnal = MAPPED   -> sudah dipetakan, ada jurnal umum (lihat JurnalUmumSeeder).
     *  - status_jurnal = UNMAPPED -> belum dipetakan bendahara, belum punya jurnal.
     *
     * no_referensi dipakai sebagai kunci stabil oleh seeder lain (Kencleng, Bukti, JurnalUmum).
     */
    public function run(): void
    {
        $kasKecil = Dompet::where('nama_dompet', 'Kas Kecil')->value('id');
        $kasInfak = Dompet::where('nama_dompet', 'Kas Infak')->value('id');
        $kasZakat = Dompet::where('nama_dompet', 'Kas Zakat')->value('id');

        $phm        = User::where('email', 'phm@masjid.id')->value('id');
        $bendahara1 = User::where('email', 'bendahara1@masjid.id')->value('id');
        $bendahara2 = User::where('email', 'bendahara2@masjid.id')->value('id');

        $katZakatMaal = KategoriTransaksi::where('nama_kategori', 'Penerimaan Zakat Maal')->value('id');

        $data = [
            // ── (A) INFAK KENCLENG (approval -> APPROVED) ─────────────────────────
            [
                'no_referensi'          => 'INF-KCL-2026-03-001',
                'dompet_id'             => $kasInfak,
                'kegiatan_id'           => null,
                'user_id'               => $phm,
                'kategori_transaksi_id' => null,
                'tanggal_transaksi'     => '2026-03-15',
                'jenis_transaksi'       => 'PEMASUKAN',
                'jumlah'                => 2700000,
                'deskripsi'             => 'Setoran kencleng/kotak amal Jumat pekan ke-2 Maret',
                'catatan'               => 'Dihitung & disaksikan 2 pengurus, berita acara terlampir',
                'status_approval'       => 'APPROVED',
                'status_jurnal'         => 'MAPPED',   // sudah dijurnal (POSTED)
            ],
            [
                'no_referensi'          => 'INF-KCL-2026-06-002',
                'dompet_id'             => $kasInfak,
                'kegiatan_id'           => null,
                'user_id'               => $phm,
                'kategori_transaksi_id' => null,
                'tanggal_transaksi'     => '2026-06-12',
                'jenis_transaksi'       => 'PEMASUKAN',
                'jumlah'                => 1850000,
                'deskripsi'             => 'Setoran kencleng/kotak amal Jumat pekan ke-2 Juni',
                'catatan'               => 'Sudah disetujui, menunggu pemetaan akun oleh bendahara',
                'status_approval'       => 'APPROVED',
                'status_jurnal'         => 'UNMAPPED', // belum dipetakan -> belum ada jurnal
            ],

            // ── (B) PENCATATAN BENDAHARA (status_approval = null) ─────────────────
            [
                'no_referensi'          => 'INF-2026-01-001',
                'dompet_id'             => $kasInfak,
                'kegiatan_id'           => null,
                'user_id'               => $bendahara1,
                'kategori_transaksi_id' => null,
                'tanggal_transaksi'     => '2026-01-10',
                'jenis_transaksi'       => 'PEMASUKAN',
                'jumlah'                => 3500000,
                'deskripsi'             => 'Infak tunai jamaah bulan Januari',
                'catatan'               => null,
                'status_approval'       => null,
                'status_jurnal'         => 'MAPPED',   // dijurnal (POSTED)
            ],
            [
                'no_referensi'          => 'OPS-2026-04-001',
                'dompet_id'             => $kasKecil,
                'kegiatan_id'           => null,
                'user_id'               => $bendahara2,
                'kategori_transaksi_id' => null,
                'tanggal_transaksi'     => '2026-04-05',
                'jenis_transaksi'       => 'PENGELUARAN',
                'jumlah'                => 850000,
                'deskripsi'             => 'Pembelian perlengkapan & jasa kebersihan masjid',
                'catatan'               => null,
                'status_approval'       => null,
                'status_jurnal'         => 'MAPPED',   // dijurnal (POSTED)
            ],
            [
                'no_referensi'          => 'OPS-2026-06-001',
                'dompet_id'             => $kasKecil,
                'kegiatan_id'           => null,
                'user_id'               => $bendahara1,
                'kategori_transaksi_id' => null,
                'tanggal_transaksi'     => '2026-06-03',
                'jenis_transaksi'       => 'PENGELUARAN',
                'jumlah'                => 1250000,
                'deskripsi'             => 'Pembayaran listrik masjid periode Mei',
                'catatan'               => null,
                'status_approval'       => null,
                'status_jurnal'         => 'MAPPED',   // dijurnal tapi masih DRAFT
            ],
            [
                'no_referensi'          => 'OPS-2026-06-002',
                'dompet_id'             => $kasKecil,
                'kegiatan_id'           => null,
                'user_id'               => $bendahara1,
                'kategori_transaksi_id' => null,
                'tanggal_transaksi'     => '2026-06-15',
                'jenis_transaksi'       => 'PENGELUARAN',
                'jumlah'                => 4000000,
                'deskripsi'             => 'Honor imam & muadzin bulan Juni',
                'catatan'               => 'Belum dipetakan ke akun',
                'status_approval'       => null,
                'status_jurnal'         => 'UNMAPPED', // belum ada jurnal
            ],
            [
                'no_referensi'          => 'ZKT-2026-06-001',
                'dompet_id'             => $kasZakat,
                'kegiatan_id'           => null,
                'user_id'               => $bendahara1,
                'kategori_transaksi_id' => $katZakatMaal,
                'tanggal_transaksi'     => '2026-06-20',
                'jenis_transaksi'       => 'PEMASUKAN',
                'jumlah'                => 10000000,
                'deskripsi'             => 'Penerimaan zakat maal (uang & tabungan)',
                'catatan'               => 'Diperlakukan sebagai pendapatan dengan pembatasan (Opsi B)',
                'status_approval'       => null,
                'status_jurnal'         => 'MAPPED',   // dijurnal tapi masih DRAFT
            ],
        ];

        foreach ($data as $row) {
            Transaksi::create($row);
        }
    }
}
