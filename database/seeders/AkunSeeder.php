<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriAkun;
use App\Models\Akun;

class AkunSeeder extends Seeder
{
    public function run(): void
    {
        $aset       = KategoriAkun::where('kode_kategori', '1')->first();
        $liabilitas = KategoriAkun::where('kode_kategori', '2')->first();
        $asetNeto   = KategoriAkun::where('kode_kategori', '3')->first();
        $pendapatan = KategoriAkun::where('kode_kategori', '4')->first();
        $beban      = KategoriAkun::where('kode_kategori', '5')->first();

        // ── 1. ASET ────────────────────────────────────────────────────────
        $asetLancar = Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => null,
            'kode_akun'        => '1-1000',
            'nama_akun'        => 'Aset Lancar',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetLancar->id,
            'kode_akun'        => '1-1100',
            'nama_akun'        => 'Kas dan Setara Kas',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetLancar->id,
            'kode_akun'        => '1-1110',
            'nama_akun'        => 'Kas Utama',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetLancar->id,
            'kode_akun'        => '1-1120',
            'nama_akun'        => 'Kas Operasional',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetLancar->id,
            'kode_akun'        => '1-1130',
            'nama_akun'        => 'Rekening BRI',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetLancar->id,
            'kode_akun'        => '1-1140',
            'nama_akun'        => 'Rekening BSI',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetLancar->id,
            'kode_akun'        => '1-1200',
            'nama_akun'        => 'Piutang',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetLancar->id,
            'kode_akun'        => '1-1300',
            'nama_akun'        => 'Beban Dibayar Dimuka',
            'saldo_normal'     => 'DEBIT',
        ]);

        $asetTetap = Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => null,
            'kode_akun'        => '1-2000',
            'nama_akun'        => 'Aset Tetap',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetTetap->id,
            'kode_akun'        => '1-2100',
            'nama_akun'        => 'Tanah',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetTetap->id,
            'kode_akun'        => '1-2200',
            'nama_akun'        => 'Bangunan Masjid',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetTetap->id,
            'kode_akun'        => '1-2210',
            'nama_akun'        => 'Akumulasi Penyusutan Bangunan',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetTetap->id,
            'kode_akun'        => '1-2300',
            'nama_akun'        => 'Peralatan Masjid',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $aset->id,
            'parent_id'        => $asetTetap->id,
            'kode_akun'        => '1-2310',
            'nama_akun'        => 'Akumulasi Penyusutan Peralatan',
            'saldo_normal'     => 'KREDIT',
        ]);

        // ── 2. LIABILITAS ──────────────────────────────────────────────────
        $liabilitasLancar = Akun::create([
            'kategori_akun_id' => $liabilitas->id,
            'parent_id'        => null,
            'kode_akun'        => '2-1000',
            'nama_akun'        => 'Liabilitas Jangka Pendek',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $liabilitas->id,
            'parent_id'        => $liabilitasLancar->id,
            'kode_akun'        => '2-1100',
            'nama_akun'        => 'Utang Beban',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $liabilitas->id,
            'parent_id'        => $liabilitasLancar->id,
            'kode_akun'        => '2-1110',
            'nama_akun'        => 'Utang Beban Listrik',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $liabilitas->id,
            'parent_id'        => $liabilitasLancar->id,
            'kode_akun'        => '2-1120',
            'nama_akun'        => 'Utang Beban Air',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $liabilitas->id,
            'parent_id'        => $liabilitasLancar->id,
            'kode_akun'        => '2-1200',
            'nama_akun'        => 'Dana Titipan Zakat',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $liabilitas->id,
            'parent_id'        => $liabilitasLancar->id,
            'kode_akun'        => '2-1300',
            'nama_akun'        => 'Dana Titipan Qurban',
            'saldo_normal'     => 'KREDIT',
        ]);

        // ── 3. ASET NETO ───────────────────────────────────────────────────
        //
        // Terminologi diselaraskan dengan ISAK 335:
        //   "Tanpa Pembatasan" (without restrictions) — sebelumnya "Tidak Terikat"
        //   "Dengan Pembatasan" (with restrictions)   — sebelumnya "Terikat Temporer"
        //
        // Akun 3-1200 Surplus/Defisit Tahun Berjalan DIHAPUS — tidak diperlukan
        // karena surplus sudah tercermin otomatis di saldo 3-1000 setelah
        // jurnal penutup diposting.

        $asetNetoTanpaPembatasan = Akun::create([
            'kategori_akun_id' => $asetNeto->id,
            'parent_id'        => null,
            'kode_akun'        => '3-1000',
            'nama_akun'        => 'Aset Neto Tanpa Pembatasan',  // ← diperbarui
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $asetNeto->id,
            'parent_id'        => $asetNetoTanpaPembatasan->id,
            'kode_akun'        => '3-1100',
            'nama_akun'        => 'Saldo Awal',
            'saldo_normal'     => 'KREDIT',
        ]);

        // 3-1200 Surplus/Defisit Tahun Berjalan → DIHAPUS

        $asetNetoDenganPembatasan = Akun::create([
            'kategori_akun_id' => $asetNeto->id,
            'parent_id'        => null,
            'kode_akun'        => '3-2000',
            'nama_akun'        => 'Aset Neto Dengan Pembatasan', // ← diperbarui
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $asetNeto->id,
            'parent_id'        => $asetNetoDenganPembatasan->id,
            'kode_akun'        => '3-2100',
            'nama_akun'        => 'Dana Wakaf',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $asetNeto->id,
            'parent_id'        => $asetNetoDenganPembatasan->id,
            'kode_akun'        => '3-2200',
            'nama_akun'        => 'Dana Pembangunan',
            'saldo_normal'     => 'KREDIT',
        ]);

        // ── 4. PENDAPATAN ──────────────────────────────────────────────────
        //
        // Struktur sudah benar — dua parent sesuai klasifikasi ISAK 35:
        //   4-1000 Pendapatan Tidak Terikat  → PREFIX_DENGAN_PEMBATASAN tidak cocok
        //                                      → closing ke 3-1000
        //   4-2000 Pendapatan Terikat        → prefix '4-2' cocok
        //                                      → closing ke 3-2000

        $pendapatanTidakTerikat = Akun::create([
            'kategori_akun_id' => $pendapatan->id,
            'parent_id'        => null,
            'kode_akun'        => '4-1000',
            'nama_akun'        => 'Pendapatan Tidak Terikat',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $pendapatan->id,
            'parent_id'        => $pendapatanTidakTerikat->id,
            'kode_akun'        => '4-1100',
            'nama_akun'        => 'Pendapatan Infaq Jumat',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $pendapatan->id,
            'parent_id'        => $pendapatanTidakTerikat->id,
            'kode_akun'        => '4-1200',
            'nama_akun'        => 'Pendapatan Infaq Harian',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $pendapatan->id,
            'parent_id'        => $pendapatanTidakTerikat->id,
            'kode_akun'        => '4-1300',
            'nama_akun'        => 'Pendapatan Kencleng',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $pendapatan->id,
            'parent_id'        => $pendapatanTidakTerikat->id,
            'kode_akun'        => '4-1400',
            'nama_akun'        => 'Pendapatan Hibah',
            'saldo_normal'     => 'KREDIT',
        ]);

        $pendapatanTerikat = Akun::create([
            'kategori_akun_id' => $pendapatan->id,
            'parent_id'        => null,
            'kode_akun'        => '4-2000',
            'nama_akun'        => 'Pendapatan Terikat',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $pendapatan->id,
            'parent_id'        => $pendapatanTerikat->id,
            'kode_akun'        => '4-2100',
            'nama_akun'        => 'Penerimaan Zakat Fitrah',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $pendapatan->id,
            'parent_id'        => $pendapatanTerikat->id,
            'kode_akun'        => '4-2200',
            'nama_akun'        => 'Penerimaan Zakat Maal',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $pendapatan->id,
            'parent_id'        => $pendapatanTerikat->id,
            'kode_akun'        => '4-2300',
            'nama_akun'        => 'Penerimaan Donasi Qurban',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $pendapatan->id,
            'parent_id'        => $pendapatanTerikat->id,
            'kode_akun'        => '4-2400',
            'nama_akun'        => 'Penerimaan Donasi Pembangunan',
            'saldo_normal'     => 'KREDIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $pendapatan->id,
            'parent_id'        => $pendapatanTerikat->id,
            'kode_akun'        => '4-2500',
            'nama_akun'        => 'Penerimaan Wakaf Uang',
            'saldo_normal'     => 'KREDIT',
        ]);

        // ── 5. BEBAN ───────────────────────────────────────────────────────
        //
        // Semua beban → Tanpa Pembatasan (sesuai ISAK 335).
        // Tidak perlu pembagian terikat/tidak terikat di sini.

        $bebanOperasional = Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => null,
            'kode_akun'        => '5-1000',
            'nama_akun'        => 'Beban Operasional',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanOperasional->id,
            'kode_akun'        => '5-1100',
            'nama_akun'        => 'Beban Listrik',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanOperasional->id,
            'kode_akun'        => '5-1200',
            'nama_akun'        => 'Beban Air (PDAM)',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanOperasional->id,
            'kode_akun'        => '5-1300',
            'nama_akun'        => 'Beban Internet',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanOperasional->id,
            'kode_akun'        => '5-1400',
            'nama_akun'        => 'Beban Honor Imam',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanOperasional->id,
            'kode_akun'        => '5-1500',
            'nama_akun'        => 'Beban Honor Muadzin',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanOperasional->id,
            'kode_akun'        => '5-1600',
            'nama_akun'        => 'Beban Kebersihan',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanOperasional->id,
            'kode_akun'        => '5-1700',
            'nama_akun'        => 'Beban ATK & Perlengkapan',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanOperasional->id,
            'kode_akun'        => '5-1800',
            'nama_akun'        => 'Beban Perlengkapan Ibadah',
            'saldo_normal'     => 'DEBIT',
        ]);

        $bebanKegiatan = Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => null,
            'kode_akun'        => '5-2000',
            'nama_akun'        => 'Beban Kegiatan',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanKegiatan->id,
            'kode_akun'        => '5-2100',
            'nama_akun'        => 'Beban Qurban',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanKegiatan->id,
            'kode_akun'        => '5-2200',
            'nama_akun'        => 'Beban Penyaluran Zakat',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanKegiatan->id,
            'kode_akun'        => '5-2300',
            'nama_akun'        => 'Beban Kegiatan Sosial',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanKegiatan->id,
            'kode_akun'        => '5-2400',
            'nama_akun'        => 'Beban Kegiatan Kajian',
            'saldo_normal'     => 'DEBIT',
        ]);

        $bebanPenyusutan = Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => null,
            'kode_akun'        => '5-3000',
            'nama_akun'        => 'Beban Penyusutan',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanPenyusutan->id,
            'kode_akun'        => '5-3100',
            'nama_akun'        => 'Beban Penyusutan Bangunan',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $beban->id,
            'parent_id'        => $bebanPenyusutan->id,
            'kode_akun'        => '5-3200',
            'nama_akun'        => 'Beban Penyusutan Peralatan',
            'saldo_normal'     => 'DEBIT',
        ]);
    }
}