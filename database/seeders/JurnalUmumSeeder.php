<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Dompet;
use App\Models\Jurnal;
use App\Models\DetailJurnal;
use App\Models\KategoriTransaksi;
use App\Models\Periode;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JurnalUmumSeeder extends Seeder
{
    /**
     * Membuat jurnal umum (POSTED) beserta detail akun debit/kredit
     * untuk setiap transaksi yang berstatus MAPPED.
     *
     * Aturan double-entry:
     *  - PEMASUKAN  : Debit  akun Kas/Bank (sesuai dompet)
     *                 Kredit akun Pendapatan (sesuai kategori)
     *  - PENGELUARAN: Debit  akun Beban/Aset (sesuai kategori)
     *                 Kredit akun Kas/Bank (sesuai dompet)
     */
    public function run(): void
    {
        // ── Peta akun berdasarkan kode_akun ──────────────────────────────
        $akun = Akun::pluck('id', 'kode_akun');

        // ── Peta dompet → akun Kas/Bank ──────────────────────────────
        // CoA laporan hanya memiliki akun kas (1-1001/1-1002/1-1003), tidak ada akun bank.
        $dompetKeKodeAkun = [
            'Kas Masjid'           => '1-1001', // Kas Kecil
            'Bank BSI Operasional' => '1-1001', // Kas Kecil
            'Bank BRI Operasional' => '1-1001', // Kas Kecil
        ];
        $dompetAkun = [];
        foreach (Dompet::all() as $dompet) {
            $kode = $dompetKeKodeAkun[$dompet->nama_dompet] ?? '1-1001';
            $dompetAkun[$dompet->id] = $akun[$kode] ?? $akun['1-1001'];
        }

        // ── Peta kategori transaksi → akun lawan ────────────────────────
        // PEMASUKAN  → akun pendapatan (4-xxxx)
        // PENGELUARAN→ akun beban (5-xxxx) / aset (1-xxxx utk pembelian aset)
        $kategoriKeKodeAkun = [
            // Pendapatan
            'Infak Jumat'         => '4-1002', // Infak Kotak Amal
            'Infak Harian'        => '4-1001', // Infak Tunai
            'Kencleng'            => '4-1002', // Infak Kotak Amal
            'Sedekah'             => '4-1004', // Donasi Umum
            'Wakaf'               => '4-2301', // Wakaf Tunai
            'Zakat'               => '4-2102', // Zakat Maal Uang & Tabungan
            'Donasi Kegiatan'     => '4-2601', // Donasi Terikat Program
            // Beban / Aset
            'Operasional Masjid'  => '5-1104', // Kebersihan
            'Pembelian Aset'      => '1-2006', // Peralatan Masjid (aset)
            'Perawatan & Renovasi'=> '5-1301', // Perawatan Bangunan
            'Honorarium'          => '5-1106', // Honor Imam
            'Konsumsi'            => '5-1204', // Konsumsi Kegiatan
            'Perlengkapan Ibadah' => '5-1105', // Perlengkapan Masjid
            'Sosial & Santunan'   => '5-1501', // Bantuan Sosial
            'Kegiatan'            => '5-1201', // Kajian
            'Lainnya'             => '5-1105', // Perlengkapan Masjid
        ];
        $kategoriById = KategoriTransaksi::pluck('nama_kategori', 'id');

        // ── Ambil semua transaksi MAPPED yang belum punya jurnal ────────────
        $transaksiMapped = Transaksi::where('status_jurnal', 'MAPPED')
            ->whereDoesntHave('jurnal')
            ->orderBy('tanggal_transaksi')
            ->orderBy('id')
            ->get();

        $dibuat   = 0;
        $dilewati = 0;

        foreach ($transaksiMapped as $trx) {
            $namaKategori = $kategoriById[$trx->kategori_transaksi_id] ?? null;
            $kodeLawan    = $namaKategori ? ($kategoriKeKodeAkun[$namaKategori] ?? null) : null;
            $akunLawan    = $kodeLawan ? ($akun[$kodeLawan] ?? null) : null;
            $akunKas      = $dompetAkun[$trx->dompet_id] ?? null;

            // Lewati jika akun tidak lengkap agar jurnal tetap seimbang
            if (!$akunLawan || !$akunKas) {
                $dilewati++;
                continue;
            }

            $periode = $this->periodeAktif();

            $jurnal = Jurnal::create([
                'periode_id'   => $periode->id,
                'transaksi_id' => $trx->id,
                'tanggal'      => $this->tanggalDalamPeriode($trx->tanggal_transaksi, $periode),
                'jenis_jurnal' => 'UMUM',
                'keterangan'   => $trx->deskripsi ?? 'Jurnal transaksi',
                'status'       => 'POSTED',
            ]);

            if ($trx->jenis_transaksi === 'PEMASUKAN') {
                // Debit Kas/Bank, Kredit Pendapatan
                $debitAkun  = $akunKas;
                $kreditAkun = $akunLawan;
            } else {
                // PENGELUARAN: Debit Beban/Aset, Kredit Kas/Bank
                $debitAkun  = $akunLawan;
                $kreditAkun = $akunKas;
            }

            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id'   => $debitAkun,
                'tipe'      => 'DEBIT',
                'nominal'   => $trx->jumlah,
            ]);

            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id'   => $kreditAkun,
                'tipe'      => 'KREDIT',
                'nominal'   => $trx->jumlah,
            ]);

            $dibuat++;
        }

        $this->command->info("✅ JurnalUmumSeeder selesai — {$dibuat} jurnal umum dibuat, {$dilewati} transaksi dilewati.");
    }

    private ?Periode $periodeAktifCache = null;

    /**
     * Periode aktif (status = true) dipakai sebagai anchor semua jurnal demo,
     * supaya laporan yang default menampilkan periode aktif langsung berisi angka.
     * Fallback: periode bulanan terbaru, lalu periode apa pun.
     */
    private function periodeAktif(): Periode
    {
        if ($this->periodeAktifCache) {
            return $this->periodeAktifCache;
        }

        $periode = Periode::where('status', true)->orderBy('id')->first()
            ?? Periode::where('tipe', 'bulanan')->orderByDesc('tanggal_awal')->first()
            ?? Periode::orderByDesc('tanggal_awal')->first();

        return $this->periodeAktifCache = $periode;
    }

    /**
     * Jepit (clamp) tanggal transaksi ke dalam rentang periode aktif,
     * supaya daftar Jurnal Umum tampil konsisten dengan periodenya.
     */
    private function tanggalDalamPeriode($tanggal, Periode $periode): string
    {
        $t     = Carbon::parse($tanggal);
        $awal  = Carbon::parse($periode->tanggal_awal);
        $akhir = Carbon::parse($periode->tanggal_akhir);

        if ($t->lt($awal))  return $awal->toDateString();
        if ($t->gt($akhir)) return $akhir->toDateString();
        return $t->toDateString();
    }
}
