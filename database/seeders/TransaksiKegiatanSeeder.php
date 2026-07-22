<?php

namespace Database\Seeders;

use App\Models\Dompet;
use App\Models\KategoriTransaksi;
use App\Models\Kegiatan;
use App\Models\Transaksi;
use App\Models\Pengguna;
use Illuminate\Database\Seeder;

class TransaksiKegiatanSeeder extends Seeder
{
    private Pengguna $panitia;
    private Dompet $dompet;
    private KategoriTransaksi $kategori;

    public function run(): void
    {
        $this->panitia  = Pengguna::where('email', 'panitia@masjid.id')->firstOrFail();
        $this->dompet   = Dompet::firstOrFail();
        $this->kategori = KategoriTransaksi::firstOrFail();

        // ══════════════════════════════════════════════════════════════
        // DATA PRODUKSI — transaksi untuk kegiatan nyata
        // (Qurban & Bakti Sosial sudah ditangani TransaksiSeeder)
        // ══════════════════════════════════════════════════════════════

        // Zakat Fitrah — tgl sudah lewat, semua APPROVED → HARUS DITUTUP oleh command
        $zakat = $this->findKegiatan('Zakat Fitrah 1447 H');
        if ($zakat) {
            $this->buat($zakat, 'APPROVED', 'PEMASUKAN',   8_500_000, 'Penerimaan zakat fitrah dari jamaah');
            $this->buat($zakat, 'APPROVED', 'PENGELUARAN', 7_200_000, 'Distribusi zakat fitrah ke mustahiq');
            $this->command->info("Produksi — 2 APPROVED untuk [{$zakat->nama_kegiatan}] ← HARUS DITUTUP");
        }

        // Kajian Ramadan — tgl sudah lewat, semua APPROVED → HARUS DITUTUP oleh command
        $kajianRamadan = $this->findKegiatan('Kajian Ramadan 1447 H');
        if ($kajianRamadan) {
            $this->buat($kajianRamadan, 'APPROVED', 'PEMASUKAN',   3_000_000, 'Infak peserta kajian Ramadan');
            $this->buat($kajianRamadan, 'APPROVED', 'PENGELUARAN', 1_500_000, 'Honor ustadz kajian Ramadan');
            $this->buat($kajianRamadan, 'APPROVED', 'PENGELUARAN',   500_000, 'Konsumsi peserta kajian Ramadan');
            $this->command->info("Produksi — 3 APPROVED untuk [{$kajianRamadan->nama_kegiatan}] ← HARUS DITUTUP");
        }

        // Bakti Sosial — tgl belum lewat, ada PENDING → tetap AKTIF
        $baksos = $this->findKegiatan('Bakti Sosial Idul Adha 1447 H');
        if ($baksos) {
            $this->buat($baksos, 'APPROVED', 'PEMASUKAN',   5_000_000, 'Donasi bakti sosial dari jamaah');
            $this->buat($baksos, 'PENDING',  'PENGELUARAN', 3_000_000, 'Pembelian paket sembako — menunggu persetujuan');
            $this->command->info("Produksi — 1 APPROVED + 1 PENDING untuk [{$baksos->nama_kegiatan}]");
        }

        // ══════════════════════════════════════════════════════════════
        // DATA SKENARIO — validasi logika auto-close & pencatatan
        // ══════════════════════════════════════════════════════════════

        // Skenario 1: tgl belum lewat, tidak ada transaksi → AKTIF, bisa catat
        $this->command->info('Skenario 1 (Renovasi Serambi Masjid) — skip, tidak ada transaksi');

        // Skenario 2: tgl belum lewat, ada PENDING → AKTIF, bisa catat
        $s2 = $this->findKegiatan('Program Beasiswa Santri 1447 H');
        if ($s2) {
            $this->buat($s2, 'PENDING', 'PEMASUKAN', 750_000, 'Donasi beasiswa santri — menunggu verifikasi');
            $this->command->info("Skenario 2 — 1 PENDING untuk [{$s2->nama_kegiatan}]");
        }

        // Skenario 3: tgl belum lewat, semua APPROVED → AKTIF, masih bisa catat
        $s3 = $this->findKegiatan('Pengadaan Kitab Perpustakaan Masjid');
        if ($s3) {
            $this->buat($s3, 'APPROVED', 'PEMASUKAN',   2_000_000, 'Donasi pengadaan kitab fiqih');
            $this->buat($s3, 'APPROVED', 'PENGELUARAN', 1_800_000, 'Pembelian kitab hadits — telah disetujui');
            $this->command->info("Skenario 3 — 2 APPROVED untuk [{$s3->nama_kegiatan}]");
        }

        // Skenario 4: tgl sudah lewat, ada PENDING → AKTIF, panitia tidak bisa catat
        $s4 = $this->findKegiatan('Peringatan Isra Miraj 1447 H');
        if ($s4) {
            $this->buat($s4, 'APPROVED', 'PEMASUKAN',   3_000_000, 'Infak jamaah Isra Miraj');
            $this->buat($s4, 'APPROVED', 'PENGELUARAN', 1_500_000, 'Honor penceramah Isra Miraj');
            $this->buat($s4, 'PENDING',  'PENGELUARAN', 2_500_000, 'Sewa sound system — menunggu persetujuan');
            $this->command->info("Skenario 4 — 2 APPROVED + 1 PENDING untuk [{$s4->nama_kegiatan}]");
        }

        // Skenario 5: tgl sudah lewat, ada REVISION → AKTIF, panitia tidak bisa catat
        $s5 = $this->findKegiatan('Khitanan Massal 1447 H');
        if ($s5) {
            $this->buat($s5, 'APPROVED', 'PEMASUKAN',   5_000_000, 'Donasi khitanan massal dari jamaah');
            $this->buat($s5, 'APPROVED', 'PENGELUARAN', 3_000_000, 'Biaya medis khitanan massal');
            $this->buat($s5, 'REVISION', 'PENGELUARAN',   800_000, 'Konsumsi panitia — bukti perlu direvisi');
            $this->command->info("Skenario 5 — 2 APPROVED + 1 REVISION untuk [{$s5->nama_kegiatan}]");
        }

        // Skenario 6: tgl sudah lewat, ada REJECTED → AKTIF, panitia tidak bisa catat
        $s6 = $this->findKegiatan('Tabligh Akbar Muharram 1447 H');
        if ($s6) {
            $this->buat($s6, 'APPROVED', 'PEMASUKAN',   4_000_000, 'Infak jamaah tabligh akbar');
            $this->buat($s6, 'APPROVED', 'PENGELUARAN', 2_000_000, 'Honor ustadz tabligh akbar');
            $this->buat($s6, 'REJECTED', 'PENGELUARAN',   500_000, 'Dekorasi panggung — ditolak, nota tidak valid');
            $this->command->info("Skenario 6 — 2 APPROVED + 1 REJECTED untuk [{$s6->nama_kegiatan}]");
        }

        // Skenario 7: tgl sudah lewat, semua APPROVED → HARUS DITUTUP oleh command
        $s7 = $this->findKegiatan('Maulid Nabi 1446 H');
        if ($s7) {
            $this->buat($s7, 'APPROVED', 'PEMASUKAN',   10_000_000, 'Donasi maulid nabi dari jamaah');
            $this->buat($s7, 'APPROVED', 'PENGELUARAN',  3_000_000, 'Honor penceramah maulid nabi');
            $this->buat($s7, 'APPROVED', 'PENGELUARAN',  2_500_000, 'Konsumsi jamaah maulid nabi');
            $this->command->info("Skenario 7 — 3 APPROVED untuk [{$s7->nama_kegiatan}] ← HARUS DITUTUP");
        }

        // Skenario 8: tgl sudah lewat, tidak ada transaksi → AKTIF, panitia tidak bisa catat
        $this->command->info('Skenario 8 (Kajian Fiqih Wanita Bulanan) — skip, tidak ada transaksi');

        // Skenario 9: tanpa tgl_selesai, tgl_mulai sudah lewat, semua APPROVED → HARUS DITUTUP
        $s9 = $this->findKegiatan('Santunan Dhuafa Bulanan');
        if ($s9) {
            $this->buat($s9, 'APPROVED', 'PEMASUKAN',   3_000_000, 'Donasi santunan dhuafa');
            $this->buat($s9, 'APPROVED', 'PENGELUARAN', 2_500_000, 'Penyaluran santunan dhuafa — telah disetujui');
            $this->command->info("Skenario 9 — 2 APPROVED untuk [{$s9->nama_kegiatan}] ← HARUS DITUTUP");
        }

        // Skenario 10: tanpa tgl_selesai, tgl_mulai belum lewat, semua APPROVED → AKTIF, bisa catat
        $s10 = $this->findKegiatan('Pengajian Rutin Ahad Pagi');
        if ($s10) {
            $this->buat($s10, 'APPROVED', 'PEMASUKAN', 2_000_000, 'Infak peserta pengajian rutin');
            $this->command->info("Skenario 10 — 1 APPROVED untuk [{$s10->nama_kegiatan}]");
        }

        $this->command->info('');
        $this->command->info('✅ TransaksiKegiatanSeeder selesai.');
        $this->command->info('   Jalankan: php artisan kegiatan:tutup-otomatis');
        $this->command->info('   Ekspektasi ditutup: Zakat Fitrah 1447 H, Kajian Ramadan 1447 H, Maulid Nabi 1446 H, Santunan Dhuafa Bulanan');
    }

    // ── Helper: cari kegiatan berdasarkan nama lengkap ──────────────────
    private function findKegiatan(string $nama): ?Kegiatan
    {
        $kegiatan = Kegiatan::where('nama_kegiatan', $nama)->first();

        if (! $kegiatan) {
            $this->command->warn("  ⚠ Kegiatan \"{$nama}\" tidak ditemukan. Pastikan KegiatanSeeder sudah dijalankan.");
        }

        return $kegiatan;
    }

    // ── Helper: buat 1 transaksi ─────────────────────────────────────────
    private function buat(
        Kegiatan $kegiatan,
        string   $statusApproval,
        string   $jenis,
        int      $jumlah,
        string   $deskripsi
    ): void {
        Transaksi::create([
            'dompet_id'             => $this->dompet->id,
            'kegiatan_id'           => $kegiatan->id,
            'pengguna_id'               => $this->panitia->id,
            'kategori_transaksi_id' => $this->kategori->id,
            'tanggal_transaksi'     => $kegiatan->tanggal_mulai ?? now()->toDateString(),
            'jenis_transaksi'       => $jenis,
            'jumlah'                => $jumlah,
            'deskripsi'             => $deskripsi,
            'status_approval'       => $statusApproval,
            'status_jurnal'         => 'UNMAPPED',
        ]);
    }
}