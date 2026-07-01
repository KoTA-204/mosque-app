<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Akun;
use App\Models\KategoriAkun;

class AkunSeeder extends Seeder
{
    /**
     * Chart of Account (CoA).
     * - Level 1 -> kategori_akun (lihat KategoriAkunSeeder)
     * - Level 2 -> akun, parent_id = null
     * - Level 3 -> akun, parent_id = akun level 2
     *
     * Catatan revisi:
     * - 2-109 Utang Jangka Panjang diubah menjadi 2-201 (mengikuti induk 2-200).
     * - 3-201 Saldo Awal Aset Neto Dengan Pembatasan ditambahkan (anak 3-200).
     */
    public function run(): void
    {
        // 'kode_kategori' => id
        $kategoriMap = KategoriAkun::pluck('id', 'kode_kategori');

        // 'kode_akun' => id, diisi seiring proses insert
        $map = [];

        // ===== LEVEL 2 (sub-grup) : [kode, nama, saldo (D/K), deskripsi] =====
        $level2 = [
            ['1-100', 'Aset Lancar', 'D', 'Aset yang dapat digunakan dalam kegiatan operasional sehari-hari'],
            ['1-200', 'Aset Tetap', 'D', 'Aset berwujud yang digunakan dalam operasional masjid'],
            ['2-100', 'Liabilitas Jangka Pendek', 'K', 'Kewajiban yang harus dilunasi dalam waktu dekat'],
            ['2-200', 'Liabilitas Jangka Panjang', 'K', 'Kewajiban yang harus dilunasi dalam jangka panjang'],
            ['3-100', 'Aset Neto Tanpa Pembatasan', 'K', 'Dana yang dapat digunakan untuk kegiatan umum masjid'],
            ['3-200', 'Aset Neto Dengan Pembatasan', 'K', 'Dana yang penggunaannya dibatasi donor atau syariah'],
            ['4-100', 'Pendapatan Tanpa Pembatasan', 'K', 'Pendapatan yang dapat digunakan untuk kebutuhan umum'],
            ['4-200', 'Pendapatan Dengan Pembatasan', 'K', 'Pendapatan yang memiliki pembatasan penggunaan'],
            ['5-100', 'Beban Operasional', 'D', 'Beban operasional rutin masjid'],
            ['5-200', 'Beban Kegiatan', 'D', 'Beban kegiatan dakwah dan keagamaan'],
            ['5-300', 'Beban Penyaluran Zakat', 'D', 'Penyaluran dana zakat kepada mustahik'],
            ['5-400', 'Beban Lainnya', 'D', 'Beban lainnya'],
            ['5-500', 'Beban Pemeliharaan', 'D', 'Perawatan dan perbaikan aset'],
            ['5-600', 'Beban Penyusutan', 'D', 'Beban penyusutan aset tetap'],
        ];

        foreach ($level2 as $a) {
            $akun = Akun::create([
                'kategori_akun_id' => $kategoriMap[substr($a[0], 0, 1)],
                'parent_id'        => null,
                'kode_akun'        => $a[0],
                'nama_akun'        => $a[1],
                'saldo_normal'     => $a[2] === 'D' ? 'DEBIT' : 'KREDIT',
                'deskripsi'        => $a[3],
                'status'           => 'aktif',
            ]);
            $map[$a[0]] = $akun->id;
        }

        // ===== LEVEL 3 (akun transaksional) : [kode, nama, parent_kode, saldo (D/K), deskripsi] =====
        $level3 = [
            // 1 - Aset Lancar
            ['1-101', 'Kas Kecil', '1-100', 'D', 'Kas yang digunakan untuk operasional masjid'],
            ['1-102', 'Kas Infak', '1-100', 'D', 'Kas yang digunakan untuk dana infak'],
            ['1-103', 'Kas Zakat', '1-100', 'D', 'Kas yang digunakan untuk dana zakat'],
            ['1-104', 'Piutang', '1-100', 'D', 'Tagihan yang belum diterima'],
            ['1-105', 'Beban Dibayar Dimuka', '1-100', 'D', 'Pembayaran beban untuk periode mendatang'],
            ['1-106', 'Perlengkapan Masjid', '1-100', 'D', 'Perlengkapan masjid seperti Al-Quran, mukena, sajadah'],
            // 1 - Aset Tetap
            ['1-201', 'Tanah Masjid', '1-200', 'D', 'Nilai tanah yang dimiliki masjid'],
            ['1-202', 'Bangunan Masjid', '1-200', 'D', 'Nilai bangunan utama masjid'],
            ['1-203', 'Akumulasi Penyusutan Bangunan', '1-200', 'K', 'Akumulasi penyusutan bangunan masjid'],
            ['1-204', 'Aset Dalam Pembangunan', '1-200', 'D', 'Proyek pembangunan yang belum selesai'],
            ['1-205', 'Investasi Jangka Panjang', '1-200', 'D', 'Investasi syariah jangka panjang milik masjid'],
            ['1-206', 'Peralatan Masjid', '1-200', 'D', 'Peralatan yang menunjang kegiatan masjid secara berkelanjutan seperti mimbar dan sound system'],
            ['1-207', 'Akumulasi Penyusutan Peralatan Masjid', '1-200', 'K', 'Akumulasi penyusutan peralatan masjid'],
            // 2 - Liabilitas Jangka Pendek
            ['2-101', 'Utang Operasional', '2-100', 'K', 'Kewajiban terkait operasional masjid'],
            ['2-102', 'Utang Listrik', '2-100', 'K', 'Tagihan listrik yang belum dibayar'],
            ['2-103', 'Utang Air', '2-100', 'K', 'Tagihan air yang belum dibayar'],
            ['2-104', 'Utang Honorarium', '2-100', 'K', 'Honor yang belum dibayarkan'],
            ['2-105', 'Utang Kegiatan', '2-100', 'K', 'Kewajiban terkait kegiatan masjid'],
            ['2-106', 'Dana Titipan Zakat Maal', '2-100', 'K', 'Dana zakat maal yang belum disalurkan'],
            ['2-107', 'Dana Titipan Zakat Fitrah', '2-100', 'K', 'Dana zakat fitrah yang belum disalurkan'],
            ['2-108', 'Dana Titipan Qurban', '2-100', 'K', 'Dana qurban yang belum direalisasikan'],
            // 2 - Liabilitas Jangka Panjang (2-109 -> 2-201)
            ['2-201', 'Utang Jangka Panjang', '2-200', 'K', 'Kewajiban jangka panjang masjid'],
            // 3 - Aset Neto Tanpa Pembatasan
            ['3-101', 'Saldo Awal Aset Neto', '3-100', 'K', 'Saldo awal aset neto'],
            ['3-102', 'Surplus/Defisit Tahun Berjalan', '3-100', 'K', 'Akumulasi hasil kegiatan periode berjalan'],
            // 3 - Aset Neto Dengan Pembatasan (tambahan)
            ['3-201', 'Saldo Awal Aset Neto Dengan Pembatasan', '3-200', 'K', 'Saldo awal aset neto dengan pembatasan'],
            // 4 - Pendapatan Tanpa Pembatasan
            ['4-101', 'Infak Tunai', '4-100', 'K', 'Penerimaan infak harian melalui tunai'],
            ['4-102', 'Infak Kotak Amal', '4-100', 'K', 'Penerimaan dari kotak amal'],
            ['4-103', 'Infak Online', '4-100', 'K', 'Penerimaan infak melalui transfer'],
            ['4-104', 'Donasi Umum', '4-100', 'K', 'Donasi tanpa pembatasan'],
            ['4-105', 'Pendapatan Lain-lain', '4-100', 'K', 'Pendapatan lain yang tidak termasuk kategori utama'],
            ['4-106', 'Keuntungan Pelepasan Aset Tetap', '4-100', 'K', 'Keuntungan dari pelepasan aset tetap saat nilai jual melebihi nilai buku'],
            // 4 - Pendapatan Dengan Pembatasan
            ['4-201', 'Zakat Maal Emas & Perak', '4-200', 'K', 'Penerimaan zakat maal emas dan perak'],
            ['4-202', 'Zakat Maal Uang & Tabungan', '4-200', 'K', 'Penerimaan zakat uang dan tabungan'],
            ['4-203', 'Zakat Maal Perdagangan', '4-200', 'K', 'Penerimaan zakat perdagangan'],
            ['4-204', 'Zakat Maal Pertanian', '4-200', 'K', 'Penerimaan zakat hasil pertanian'],
            ['4-205', 'Zakat Maal Profesi', '4-200', 'K', 'Penerimaan zakat profesi'],
            ['4-206', 'Zakat Maal Ternak', '4-200', 'K', 'Penerimaan zakat ternak'],
            ['4-207', 'Zakat Maal Investasi Syariah', '4-200', 'K', 'Penerimaan zakat investasi syariah'],
            ['4-208', 'Zakat Fitrah Beras', '4-200', 'K', 'Penerimaan zakat fitrah berupa beras'],
            ['4-209', 'Zakat Fitrah Uang', '4-200', 'K', 'Penerimaan zakat fitrah berupa uang'],
            ['4-210', 'Penerimaan Wakaf Tunai', '4-200', 'K', 'Dana wakaf dalam bentuk uang'],
            ['4-211', 'Penerimaan Wakaf Aset', '4-200', 'K', 'Dana wakaf berupa aset'],
            ['4-212', 'Dana Pembangunan', '4-200', 'K', 'Donasi khusus pembangunan masjid'],
            ['4-213', 'Dana Qurban', '4-200', 'K', 'Dana untuk pelaksanaan qurban'],
            ['4-214', 'Donasi Terikat Program', '4-200', 'K', 'Donasi dengan tujuan program tertentu'],
            // 5 - Beban Operasional
            ['5-101', 'Beban Listrik', '5-100', 'D', 'Penggunaan listrik masjid'],
            ['5-102', 'Beban Air', '5-100', 'D', 'Penggunaan air masjid'],
            ['5-103', 'Beban Internet', '5-100', 'D', 'Layanan internet masjid'],
            ['5-104', 'Beban Kebersihan', '5-100', 'D', 'Perlengkapan dan jasa kebersihan'],
            ['5-105', 'Beban Perlengkapan Masjid', '5-100', 'D', 'Pembelian perlengkapan Masjid'],
            ['5-106', 'Beban Honor Imam', '5-100', 'D', 'Honor imam'],
            ['5-107', 'Beban Honor Muadzin', '5-100', 'D', 'Honor muadzin'],
            ['5-108', 'Beban Honor Marbot', '5-100', 'D', 'Honor marbot'],
            // 5 - Beban Kegiatan
            ['5-201', 'Beban Kajian', '5-200', 'D', 'Pelaksanaan kajian rutin'],
            ['5-202', 'Beban Pengajian', '5-200', 'D', 'Pelaksanaan pengajian'],
            ['5-203', 'Beban PHBI', '5-200', 'D', 'Peringatan hari besar Islam'],
            ['5-204', 'Beban Konsumsi Kegiatan', '5-200', 'D', 'Konsumsi panitia dan kegiatan'],
            ['5-205', 'Beban Kegiatan Sosial', '5-200', 'D', 'Bakti sosial dan kegiatan kemasyarakatan'],
            // 5 - Beban Penyaluran Zakat
            ['5-301', 'Penyaluran Zakat Fakir', '5-300', 'D', 'Penyaluran kepada fakir'],
            ['5-302', 'Penyaluran Zakat Miskin', '5-300', 'D', 'Penyaluran kepada miskin'],
            ['5-303', 'Penyaluran Zakat Amil', '5-300', 'D', 'Hak amil pengelola zakat'],
            ['5-304', 'Penyaluran Zakat Muallaf', '5-300', 'D', 'Penyaluran kepada muallaf'],
            ['5-305', 'Penyaluran Zakat Gharimin', '5-300', 'D', 'Penyaluran kepada gharimin'],
            ['5-306', 'Penyaluran Zakat Fisabilillah', '5-300', 'D', 'Penyaluran fisabilillah'],
            ['5-307', 'Penyaluran Zakat Ibnu Sabil', '5-300', 'D', 'Penyaluran ibnu sabil'],
            ['5-308', 'Penyaluran Zakat Fitrah', '5-300', 'D', 'Distribusi zakat fitrah'],
            // 5 - Beban Lainnya
            ['5-401', 'Penyaluran Wakaf', '5-400', 'D', 'Realisasi penggunaan dana wakaf'],
            ['5-402', 'Beban Pembangunan Masjid', '5-400', 'D', 'Pengeluaran pembangunan masjid'],
            ['5-403', 'Beban Qurban', '5-400', 'D', 'Pelaksanaan kegiatan qurban'],
            ['5-404', 'Bantuan Sosial', '5-400', 'D', 'Penyaluran bantuan sosial'],
            ['5-405', 'Kerugian Pelepasan Aset Tetap', '5-400', 'D', 'Kerugian dari pelepasan aset tetap akibat penjualan, kehilangan, pencurian, atau penghibahan saat nilai buku masih tersisa'],
            // 5 - Beban Pemeliharaan
            ['5-501', 'Perawatan Bangunan', '5-500', 'D', 'Perawatan bangunan masjid'],
            ['5-502', 'Perawatan Peralatan Masjid', '5-500', 'D', 'Perawatan peralatan masjid'],
            ['5-503', 'Perbaikan Fasilitas', '5-500', 'D', 'Perbaikan fasilitas masjid'],
            // 5 - Beban Penyusutan
            ['5-601', 'Penyusutan Bangunan', '5-600', 'D', 'Penyusutan bangunan masjid'],
            ['5-602', 'Penyusutan Peralatan Masjid', '5-600', 'D', 'Penyusutan peralatan masjid'],
        ];

        foreach ($level3 as $a) {
            $akun = Akun::create([
                'kategori_akun_id' => $kategoriMap[substr($a[0], 0, 1)],
                'parent_id'        => $map[$a[2]] ?? null,
                'kode_akun'        => $a[0],
                'nama_akun'        => $a[1],
                'saldo_normal'     => $a[3] === 'D' ? 'DEBIT' : 'KREDIT',
                'deskripsi'        => $a[4],
                'status'           => 'aktif',
            ]);
            $map[$a[0]] = $akun->id;
        }
    }
}
