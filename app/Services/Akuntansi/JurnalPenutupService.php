<?php

namespace App\Services\Akuntansi;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// Service tutup buku - versi untuk CoA penomoran 4-digit: K-CFUU.
class JurnalPenutupService extends JurnalService
{
    // Penomoran 4-digit: kode akun berbentuk "K-CFUU".
    //   K  = kategori akun (3 Aset Neto, 4 Pendapatan, 5 Beban, dst.)
    //   C  = KELAS pembatasan (digit ribuan): 1 = Tanpa Pembatasan (Dana Umum),
    //        2 = Dengan Pembatasan (semua dana terikat).
    //   F  = INDEKS dana terikat (digit ratusan), bermakna saat C = 2:
    //        1 Zakat Maal, 2 Zakat Fitrah, 3 Wakaf, 4 Pembangunan, 5 Qurban,
    //        6 Program Terikat.
    //   UU = nomor urut akun dalam kelompok tersebut.
    const KODE_KATEGORI_ASET_NETO  = '3';
    const KODE_KATEGORI_PENDAPATAN = '4';
    const KODE_KATEGORI_BEBAN      = '5';

    // Kelas pembatasan (digit ribuan setelah tanda hubung).
    const KELAS_TANPA_PEMBATASAN  = '1';
    const KELAS_DENGAN_PEMBATASAN = '2';

    // Akun saldo Dana Umum (aset neto tanpa pembatasan) = 3-1001.
    const KODE_DANA_UMUM = '3-1001';

    const TIPE_LABELS = [
        'TUTUP_PENDAPATAN'     => 'Tutup Pendapatan',
        'TUTUP_BEBAN'          => 'Tutup Beban',
        'PELEPASAN_PEMBATASAN' => 'Pelepasan Aset Neto dari Pembatasan',
    ];

    public function __construct(private PeriodeService $periode) {}

    public function daftar(array $filter): LengthAwarePaginator
    {
        return Jurnal::with(['periode', 'detailJurnal.akun'])
            ->penutup()
            ->when($filter['periode_id'] ?? null, fn($q) => $q->where('periode_id', $filter['periode_id']))
            ->when($filter['status'] ?? null,    fn($q) => $q->where('status', strtoupper($filter['status'])))
            ->when($filter['search'] ?? null,    fn($q) => $q->where('keterangan', 'like', "%{$filter['search']}%"))
            ->orderBy('tanggal', 'desc')
            ->paginate($filter['per_page'] ?? 10)
            ->withQueryString();
    }

    public function getById(Jurnal $jurnal): Jurnal
    {
        return $jurnal->load('periode', 'detailJurnal.akun');
    }

    // Periode siap ditutup jika ada >=1 jurnal operasional POSTED dan tidak ada DRAFT.
    public function validasiPeriodeSiapTutup(Periode $periode): ?string
    {
        $adaPosted = Jurnal::where('periode_id', $periode->id)
            ->whereIn('jenis_jurnal', ['UMUM', 'PENYESUAIAN', 'KOREKSI'])
            ->where('status', 'POSTED')
            ->exists();

        if (!$adaPosted) {
            return 'Periode belum memiliki jurnal yang diposting. '
                 . 'Catat minimal satu transaksi dan posting sebelum menutup periode.';
        }

        $adaDraft = Jurnal::where('periode_id', $periode->id)
            ->whereIn('jenis_jurnal', ['UMUM', 'PENYESUAIAN', 'KOREKSI'])
            ->where('status', 'DRAFT')
            ->exists();

        if ($adaDraft) {
            return 'Masih ada jurnal yang belum diposting. '
                 . 'Posting semua jurnal terlebih dahulu sebelum menutup periode.';
        }

        return null;
    }

    public function getRingkasanPeriode(Periode $periode): array
    {
        if ($this->periode->isPeriodeClosed($periode)) {
            throw new \RuntimeException('Periode sudah ditutup.');
        }

        $pesanTidakSiap = $this->validasiPeriodeSiapTutup($periode);

        $jurnals = Jurnal::with('detailJurnal.akun.kategoriAkun')
            ->where('periode_id', $periode->id)
            ->whereIn('jenis_jurnal', ['UMUM', 'PENYESUAIAN', 'KOREKSI'])
            ->where('status', 'POSTED')
            ->get();

        $pendapatan = collect();
        $beban      = collect();

        foreach ($jurnals as $jurnal) {
            foreach ($jurnal->detailJurnal as $detail) {
                $akun     = $detail->akun;
                $kategori = $akun?->kategoriAkun;
                if (!$kategori) continue;

                $kodeKat = $kategori->kode_kategori;

                if ($kodeKat === self::KODE_KATEGORI_PENDAPATAN) {
                    $this->hitungAkumulasiSaldo($pendapatan, $akun, $detail, 'KREDIT');
                }
                if ($kodeKat === self::KODE_KATEGORI_BEBAN) {
                    $this->hitungAkumulasiSaldo($beban, $akun, $detail, 'DEBIT');
                }
            }
        }

        $totalPendapatan = $pendapatan->sum('saldo');
        $totalBeban      = $beban->sum('saldo');

        return [
            'pendapatan'       => $pendapatan->values(),
            'beban'            => $beban->values(),
            'total_pendapatan' => $totalPendapatan,
            'total_beban'      => $totalBeban,
            'surplus'          => $totalPendapatan - $totalBeban,
            'pesan_tidak_siap' => $pesanTidakSiap,
        ];
    }

    private function hitungAkumulasiSaldo(
        Collection $collection,
        $akun,
        $detail,
        string $saldoNormal
    ): void {
        $key = $akun->id;
        if (!$collection->has($key)) {
            $collection->put($key, ['akun' => $akun, 'saldo' => 0]);
        }
        $item = $collection->get($key);
        $item['saldo'] += $detail->tipe === $saldoNormal
            ? (float) $detail->nominal
            : -(float) $detail->nominal;
        $collection->put($key, $item);
    }

    // Ambil KELAS pembatasan (C) = digit ke-1 setelah '-' dari kode "K-CFUU".
    private function kelasDana(string $kodeAkun): string
    {
        $pos  = strpos($kodeAkun, '-');
        $body = $pos === false ? $kodeAkun : substr($kodeAkun, $pos + 1);
        return substr($body, 0, 1);
    }

    // Ambil INDEKS dana terikat (F) = digit ke-2 setelah '-' dari kode "K-CFUU".
    private function indeksDana(string $kodeAkun): string
    {
        $pos  = strpos($kodeAkun, '-');
        $body = $pos === false ? $kodeAkun : substr($kodeAkun, $pos + 1);
        return substr($body, 1, 1);
    }

    // Apakah akun tergolong Tanpa Pembatasan (Dana Umum)?
    private function isTanpaPembatasan(string $kodeAkun): bool
    {
        return $this->kelasDana($kodeAkun) === self::KELAS_TANPA_PEMBATASAN;
    }

    // Bangun kode akun Aset Neto (saldo dana terikat) untuk sebuah indeks dana: "3-2F01".
    private function kodeDanaTerikat(string $f): string
    {
        return self::KODE_KATEGORI_ASET_NETO . '-' . self::KELAS_DENGAN_PEMBATASAN . $f . '01';
    }

    // Tentukan kode akun dana tujuan penutupan untuk sebuah akun (pendapatan/beban).
    // Tanpa Pembatasan => Dana Umum (3-1001); terikat => 3-2F01 sesuai indeks dana.
    private function tentukanKodeDana(Akun $akun): string
    {
        if ($this->isTanpaPembatasan($akun->kode_akun)) {
            return self::KODE_DANA_UMUM;
        }
        return $this->kodeDanaTerikat($this->indeksDana($akun->kode_akun));
    }

    public function getStatusTahap(Periode $periode): array
    {
        $status = [];
        foreach (array_keys(self::TIPE_LABELS) as $tipe) {
            $jurnal = Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('tipe_penutupan', $tipe)
                ->latest()
                ->first();

            $ada     = (bool) $jurnal;
            $selesai = $ada && $jurnal->status === 'POSTED';

            $status[$tipe] = [
                'jurnal'  => $jurnal,
                'selesai' => $selesai,
                'ada'     => $ada,
                'status'  => $jurnal?->status,
            ];
        }
        return $status;
    }

    public function getTahapSelesai(Periode $periode): int
    {
        $status  = $this->getStatusTahap($periode);
        $selesai = 0;
        foreach (array_keys(self::TIPE_LABELS) as $tipe) {
            if ($status[$tipe]['selesai']) {
                $selesai++;
            }
        }
        return $selesai;
    }

    public function getExistingDraft(Periode $periode): array
    {
        $result = [];
        foreach (array_keys(self::TIPE_LABELS) as $tipe) {
            $jurnal = Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('tipe_penutupan', $tipe)
                ->where('status', 'DRAFT')
                ->latest()
                ->first();

            if ($jurnal) {
                $result[$tipe] = $jurnal->load('detailJurnal.akun');
            }
        }
        return $result;
    }

    public function susunJurnalTutupPendapatan(array $ringkasan): array
    {
        if ($ringkasan['total_pendapatan'] <= 0) {
            return [];
        }

        $grouped = collect($ringkasan['pendapatan'])
            ->filter(fn($item) => $item['saldo'] > 0)
            ->groupBy(fn($item) => $this->tentukanKodeDana($item['akun']));

        $detail = [];

        foreach ($grouped as $kodeDana => $items) {
            $totalDana = $items->sum('saldo');
            $danaAkun  = Akun::where('kode_akun', $kodeDana)->first();
            if (!$danaAkun || $totalDana <= 0) continue;

            foreach ($items as $item) {
                $detail[] = $this->buatEntriJurnal(
                    $item['akun']->id,
                    $item['akun']->nama_akun,
                    $item['akun']->kode_akun,
                    'DEBIT',
                    $item['saldo']
                );
            }

            $detail[] = $this->buatEntriJurnal(
                $danaAkun->id,
                $danaAkun->nama_akun,
                $danaAkun->kode_akun,
                'KREDIT',
                $totalDana
            );
        }

        return $detail;
    }

    public function susunJurnalTutupBeban(array $ringkasan): array
    {
        if ($ringkasan['total_beban'] <= 0) {
            return [];
        }

        $danaUmum = Akun::where('kode_akun', self::KODE_DANA_UMUM)->first();
        if (!$danaUmum) {
            return [];
        }

        $detail = [];

        $detail[] = $this->buatEntriJurnal(
            $danaUmum->id,
            $danaUmum->nama_akun,
            $danaUmum->kode_akun,
            'DEBIT',
            $ringkasan['total_beban']
        );

        foreach ($ringkasan['beban'] as $item) {
            if ($item['saldo'] <= 0) continue;
            $detail[] = $this->buatEntriJurnal(
                $item['akun']->id,
                $item['akun']->nama_akun,
                $item['akun']->kode_akun,
                'KREDIT',
                $item['saldo']
            );
        }

        return $detail;
    }

    // Tahap-3 (ISAK 35): pelepasan aset neto dari pembatasan.
    public function susunJurnalPelepasanPembatasan(array $ringkasan): array
    {
        $danaUmum = Akun::where('kode_akun', self::KODE_DANA_UMUM)->first();
        if (!$danaUmum) {
            return [];
        }

        $detail = [];

        // Kelompokkan beban penyaluran TERIKAT (segmen dana != Dana Umum) per dana,
        // lalu lepaskan pembatasannya: debit dana terikat, kredit Dana Umum.
        $tersalurPerDana = collect($ringkasan['beban'])
            ->filter(fn($item) =>
                $item['saldo'] > 0 &&
                !$this->isTanpaPembatasan($item['akun']->kode_akun)
            )
            ->groupBy(fn($item) => $this->indeksDana($item['akun']->kode_akun));

        foreach ($tersalurPerDana as $ff => $items) {
            $totalTersalur = $items->sum('saldo');
            if ($totalTersalur <= 0) continue;

            $danaTerikat = Akun::where('kode_akun', $this->kodeDanaTerikat($ff))->first();
            if (!$danaTerikat) continue;

            $detail[] = $this->buatEntriJurnal(
                $danaTerikat->id,
                $danaTerikat->nama_akun,
                $danaTerikat->kode_akun,
                'DEBIT',
                $totalTersalur
            );

            $detail[] = $this->buatEntriJurnal(
                $danaUmum->id,
                $danaUmum->nama_akun,
                $danaUmum->kode_akun,
                'KREDIT',
                $totalTersalur
            );
        }

        return $detail;
    }

    private function buatEntriJurnal(
        int    $akunId,
        string $namaAkun,
        string $kodeAkun,
        string $tipe,
        float  $nominal
    ): array {
        return [
            'akun_id'   => $akunId,
            'akun'      => $namaAkun,
            'kode_akun' => $kodeAkun,
            'tipe'      => $tipe,
            'nominal'   => $nominal,
        ];
    }

    public function catatSemuaTahapPenutupan(
        Periode $periode,
        array   $semua,
        string  $tanggal,
        string  $status = 'DRAFT'
    ): array {
        if ($this->periode->isPeriodeClosed($periode)) {
            throw new \RuntimeException('Periode sudah ditutup.');
        }

        if ($err = $this->validasiPeriodeSiapTutup($periode)) {
            throw new \RuntimeException($err);
        }

        return DB::transaction(function () use ($periode, $semua, $tanggal, $status) {
            $hasil = [];
            foreach ($semua as $tipe => $detail) {
                $this->pastikanBelumDiposting($periode, $tipe);
                $this->hapusDraftLama($periode, $tipe);

                $jurnal = Jurnal::create([
                    'periode_id'     => $periode->id,
                    'transaksi_id'   => null,
                    'jurnal_ref_id'  => null,
                    'jenis_jurnal'   => 'PENUTUP',
                    'tipe_penutupan' => $tipe,
                    'tanggal'        => $tanggal,
                    'keterangan'     => self::TIPE_LABELS[$tipe] . ' - ' . $periode->nama_periode,
                    'status'         => $status,
                ]);

                $this->catatDetailJurnal($jurnal, $detail);
                $hasil[$tipe] = $jurnal->load('detailJurnal.akun');
            }
            return $hasil;
        });
    }

    public function postingDraftPenutupan(Periode $periode): bool|string
    {
        if ($this->periode->isPeriodeClosed($periode)) {
            return 'Periode sudah ditutup.';
        }
        if ($err = $this->validasiPeriodeSiapTutup($periode)) {
            return $err;
        }
        if ($err = $this->periode->validasiPeriodeBerikutnya($periode)) {
            return $err;
        }

        DB::transaction(function () use ($periode) {
            Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('status', 'DRAFT')
                ->update(['status' => 'POSTED']);

            $this->periode->finalisasiPenutupan($periode);
        });

        return true;
    }

    public function catatDanPostingPenutupan(
        Periode $periode,
        array   $semua,
        string  $tanggal
    ): bool|string {
        if ($this->periode->isPeriodeClosed($periode)) {
            return 'Periode sudah ditutup.';
        }
        if ($err = $this->validasiPeriodeSiapTutup($periode)) {
            return $err;
        }
        if ($err = $this->periode->validasiPeriodeBerikutnya($periode)) {
            return $err;
        }

        DB::transaction(function () use ($periode, $semua, $tanggal) {
            foreach ($semua as $tipe => $detail) {
                $this->pastikanBelumDiposting($periode, $tipe);
                $this->hapusDraftLama($periode, $tipe);

                $jurnal = Jurnal::create([
                    'periode_id'     => $periode->id,
                    'transaksi_id'   => null,
                    'jurnal_ref_id'  => null,
                    'jenis_jurnal'   => 'PENUTUP',
                    'tipe_penutupan' => $tipe,
                    'tanggal'        => $tanggal,
                    'keterangan'     => self::TIPE_LABELS[$tipe] . ' - ' . $periode->nama_periode,
                    'status'         => 'POSTED',
                ]);

                $this->catatDetailJurnal($jurnal, $detail);
            }

            $this->periode->finalisasiPenutupan($periode);
        });

        return true;
    }

    private function pastikanBelumDiposting(Periode $periode, string $tipe): void
    {
        $sudahPosted = Jurnal::where('periode_id', $periode->id)
            ->where('jenis_jurnal', 'PENUTUP')
            ->where('tipe_penutupan', $tipe)
            ->where('status', 'POSTED')
            ->exists();

        if ($sudahPosted) {
            throw new \RuntimeException(
                'Tahap ' . (self::TIPE_LABELS[$tipe] ?? $tipe) .
                ' sudah diposting dan tidak dapat diulang.'
            );
        }
    }

    private function hapusDraftLama(Periode $periode, string $tipe): void
    {
        Jurnal::where('periode_id', $periode->id)
            ->where('jenis_jurnal', 'PENUTUP')
            ->where('tipe_penutupan', $tipe)
            ->where('status', 'DRAFT')
            ->each(function ($j) {
                $j->detailJurnal()->delete();
                $j->delete();
            });
    }
}
