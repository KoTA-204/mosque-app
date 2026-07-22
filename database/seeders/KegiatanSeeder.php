<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use App\Models\Pengguna;
use Illuminate\Database\Seeder;

class KegiatanSeeder extends Seeder
{
    /**
     * Kegiatan khusus masjid. panitia_id mengarah ke pengguna ber-peran Panitia Khusus.
     */
    public function run(): void
    {
        $panitia = Pengguna::where('email', 'panitia@masjid.id')->firstOrFail();

        // ══════════════════════════════════════════════════════════════
        // DATA PRODUKSI
        // ══════════════════════════════════════════════════════════════

        // AKTIF — tgl belum selesai, transaksi masih berjalan
        Kegiatan::create([
            'nama_kegiatan'   => 'Qurban 1447 H',
            'deskripsi'       => 'Penyelenggaraan ibadah qurban Idul Adha 1447 H meliputi penerimaan hewan qurban, penyembelihan, hingga distribusi daging kepada warga sekitar masjid dan kaum dhuafa.',
            'jenis_kegiatan'  => 'QURBAN',
            'tanggal_mulai'   => '2026-05-01',
            'tanggal_selesai' => '2026-06-30',
            'anggaran'        => 50000000,
            'status'          => Kegiatan::STATUS_AKTIF,
            'panitia_id'      => $panitia->id,
        ]);

        // AKTIF — tgl sudah lewat, semua APPROVED
        // → akan di-TUTUP oleh scheduled command
        Kegiatan::create([
            'nama_kegiatan'   => 'Zakat Fitrah 1447 H',
            'deskripsi'       => 'Pengelolaan zakat fitrah Ramadan 1447 H mulai dari penghimpunan zakat jamaah, pencatatan muzakki, hingga penyaluran kepada delapan asnaf yang berhak menerima.',
            'jenis_kegiatan'  => 'ZAKAT',
            'tanggal_mulai'   => '2026-03-20',
            'tanggal_selesai' => '2026-04-10',
            'anggaran'        => 20000000,
            'status'          => Kegiatan::STATUS_AKTIF, // ← auto-close yang akan tutup
            'panitia_id'      => $panitia->id,
        ]);

        // AKTIF — tgl sudah lewat, semua APPROVED
        // → akan di-TUTUP oleh scheduled command
        Kegiatan::create([
            'nama_kegiatan'   => 'Kajian Ramadan 1447 H',
            'deskripsi'       => 'Rangkaian kajian rutin selama bulan Ramadan 1447 H dengan menghadirkan ustadz dan penceramah untuk mengisi tausiyah menjelang berbuka dan setelah tarawih.',
            'jenis_kegiatan'  => 'KAJIAN',
            'tanggal_mulai'   => '2026-02-28',
            'tanggal_selesai' => '2026-03-30',
            'anggaran'        => 5000000,
            'status'          => Kegiatan::STATUS_AKTIF, // ← auto-close yang akan tutup
            'panitia_id'      => $panitia->id,
        ]);

        // AKTIF — tgl belum selesai, ada transaksi berjalan
        Kegiatan::create([
            'nama_kegiatan'   => 'Bakti Sosial Idul Adha 1447 H',
            'deskripsi'       => 'Kegiatan bakti sosial dalam rangka Idul Adha 1447 H berupa pembagian paket sembako dan santunan bagi warga kurang mampu di lingkungan sekitar masjid.',
            'jenis_kegiatan'  => 'SOSIAL',
            'tanggal_mulai'   => '2026-06-01',
            'tanggal_selesai' => '2026-06-15',
            'anggaran'        => 10000000,
            'status'          => Kegiatan::STATUS_AKTIF,
            'panitia_id'      => $panitia->id,
        ]);

        // ══════════════════════════════════════════════════════════════
        // DATA SKENARIO — validasi logika auto-close & pencatatan
        // Transaksinya dibuat oleh TransaksiKegiatanSeeder
        // ══════════════════════════════════════════════════════════════

        // Skenario 1: tgl belum lewat, tidak ada transaksi → AKTIF, bisa catat
        Kegiatan::create([
            'nama_kegiatan'   => 'Renovasi Serambi Masjid',
            'deskripsi'       => 'Proyek renovasi dan perluasan serambi masjid untuk menambah kapasitas jamaah, meliputi perbaikan lantai, atap, serta pengecatan ulang area serambi.',
            'jenis_kegiatan'  => 'LAINNYA',
            'tanggal_mulai'   => now()->addDays(7)->toDateString(),
            'tanggal_selesai' => now()->addDays(60)->toDateString(),
            'anggaran'        => 15000000,
            'status'          => Kegiatan::STATUS_AKTIF,
            'panitia_id'      => $panitia->id,
        ]);

        // Skenario 2: tgl belum lewat, ada PENDING → AKTIF, bisa catat
        Kegiatan::create([
            'nama_kegiatan'   => 'Program Beasiswa Santri 1447 H',
            'deskripsi'       => 'Program pemberian beasiswa pendidikan bagi santri berprestasi dan kurang mampu, mencakup bantuan biaya sekolah serta perlengkapan belajar selama satu periode.',
            'jenis_kegiatan'  => 'SOSIAL',
            'tanggal_mulai'   => now()->subDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(20)->toDateString(),
            'anggaran'        => 10000000,
            'status'          => Kegiatan::STATUS_AKTIF,
            'panitia_id'      => $panitia->id,
        ]);

        // Skenario 3: tgl belum lewat, semua APPROVED → AKTIF, masih bisa catat
        Kegiatan::create([
            'nama_kegiatan'   => 'Pengadaan Kitab Perpustakaan Masjid',
            'deskripsi'       => 'Pengadaan koleksi kitab dan buku keislaman untuk memperkaya perpustakaan masjid sebagai sarana belajar dan literasi bagi jamaah dan santri.',
            'jenis_kegiatan'  => 'KAJIAN',
            'tanggal_mulai'   => now()->subDays(3)->toDateString(),
            'tanggal_selesai' => now()->addDays(14)->toDateString(),
            'anggaran'        => 8000000,
            'status'          => Kegiatan::STATUS_AKTIF,
            'panitia_id'      => $panitia->id,
        ]);

        // Skenario 4: tgl sudah lewat, ada PENDING → AKTIF, panitia tidak bisa catat
        Kegiatan::create([
            'nama_kegiatan'   => 'Peringatan Isra Miraj 1447 H',
            'deskripsi'       => 'Peringatan Isra Miraj Nabi Muhammad SAW 1447 H berupa tabligh akbar dan tausiyah untuk mengambil hikmah perjalanan spiritual Rasulullah bersama jamaah.',
            'jenis_kegiatan'  => 'KAJIAN',
            'tanggal_mulai'   => now()->subDays(20)->toDateString(),
            'tanggal_selesai' => now()->subDays(1)->toDateString(),
            'anggaran'        => 7000000,
            'status'          => Kegiatan::STATUS_AKTIF,
            'panitia_id'      => $panitia->id,
        ]);

        // Skenario 5: tgl sudah lewat, ada REVISION → AKTIF, panitia tidak bisa catat
        Kegiatan::create([
            'nama_kegiatan'   => 'Khitanan Massal 1447 H',
            'deskripsi'       => 'Kegiatan khitanan massal gratis bagi anak-anak dari keluarga kurang mampu, meliputi layanan khitan, pemeriksaan kesehatan, serta pemberian bingkisan.',
            'jenis_kegiatan'  => 'SOSIAL',
            'tanggal_mulai'   => now()->subDays(15)->toDateString(),
            'tanggal_selesai' => now()->subDays(2)->toDateString(),
            'anggaran'        => 12000000,
            'status'          => Kegiatan::STATUS_AKTIF,
            'panitia_id'      => $panitia->id,
        ]);

        // Skenario 6: tgl sudah lewat, ada REJECTED → AKTIF, panitia tidak bisa catat
        Kegiatan::create([
            'nama_kegiatan'   => 'Tabligh Akbar Muharram 1447 H',
            'deskripsi'       => 'Tabligh akbar menyambut tahun baru Islam 1 Muharram 1447 H dengan menghadirkan penceramah ternama untuk memberikan siraman rohani kepada masyarakat.',
            'jenis_kegiatan'  => 'KAJIAN',
            'tanggal_mulai'   => now()->subDays(10)->toDateString(),
            'tanggal_selesai' => now()->subDays(1)->toDateString(),
            'anggaran'        => 9000000,
            'status'          => Kegiatan::STATUS_AKTIF,
            'panitia_id'      => $panitia->id,
        ]);

        // Skenario 7: tgl sudah lewat, semua APPROVED → HARUS DITUTUP oleh command
        Kegiatan::create([
            'nama_kegiatan'   => 'Maulid Nabi 1446 H',
            'deskripsi'       => 'Peringatan Maulid Nabi Muhammad SAW 1446 H berupa pembacaan shalawat, ceramah maulid, dan santunan sebagai wujud kecintaan kepada Rasulullah.',
            'jenis_kegiatan'  => 'KAJIAN',
            'tanggal_mulai'   => now()->subDays(30)->toDateString(),
            'tanggal_selesai' => now()->subDays(1)->toDateString(),
            'anggaran'        => 20000000,
            'status'          => Kegiatan::STATUS_AKTIF, // ← auto-close yang akan tutup
            'panitia_id'      => $panitia->id,
        ]);

        // Skenario 8: tgl sudah lewat, tidak ada transaksi → AKTIF, panitia tidak bisa catat
        Kegiatan::create([
            'nama_kegiatan'   => 'Kajian Fiqih Wanita Bulanan',
            'deskripsi'       => 'Kajian fiqih khusus muslimah yang diadakan rutin setiap bulan untuk membahas persoalan ibadah dan kehidupan sehari-hari dari perspektif fiqih wanita.',
            'jenis_kegiatan'  => 'KAJIAN',
            'tanggal_mulai'   => now()->subDays(10)->toDateString(),
            'tanggal_selesai' => now()->subDays(1)->toDateString(),
            'anggaran'        => 3000000,
            'status'          => Kegiatan::STATUS_AKTIF,
            'panitia_id'      => $panitia->id,
        ]);

        // Skenario 9: tanpa tgl_selesai, tgl_mulai sudah lewat, semua APPROVED → HARUS DITUTUP
        Kegiatan::create([
            'nama_kegiatan'   => 'Santunan Dhuafa Bulanan',
            'deskripsi'       => 'Program santunan rutin bulanan bagi kaum dhuafa dan anak yatim di sekitar masjid berupa bantuan uang tunai dan kebutuhan pokok.',
            'jenis_kegiatan'  => 'SOSIAL',
            'tanggal_mulai'   => now()->subDays(5)->toDateString(),
            'tanggal_selesai' => null,
            'anggaran'        => 5000000,
            'status'          => Kegiatan::STATUS_AKTIF, // ← auto-close yang akan tutup
            'panitia_id'      => $panitia->id,
        ]);

        // Skenario 10: tanpa tgl_selesai, tgl_mulai belum lewat, semua APPROVED → AKTIF, bisa catat
        Kegiatan::create([
            'nama_kegiatan'   => 'Pengajian Rutin Ahad Pagi',
            'deskripsi'       => 'Pengajian rutin setiap Ahad pagi yang terbuka untuk seluruh jamaah dengan materi tafsir, hadis, dan pembinaan akhlak guna meningkatkan pemahaman keislaman.',
            'jenis_kegiatan'  => 'KAJIAN',
            'tanggal_mulai'   => now()->addDays(3)->toDateString(),
            'tanggal_selesai' => null,
            'anggaran'        => 4000000,
            'status'          => Kegiatan::STATUS_AKTIF,
            'panitia_id'      => $panitia->id,
        ]);

        $this->command->info('✅ KegiatanSeeder selesai — 14 kegiatan berhasil dibuat.');
        $this->command->info('   Semua kegiatan berstatus AKTIF — tidak ada penutupan manual.');
        $this->command->info('   Jalankan: php artisan kegiatan:tutup-otomatis untuk menutup otomatis.');
    }
}
