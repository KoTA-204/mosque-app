<?php

namespace App\Services\Akuntansi;

use App\Models\Akun;
use App\Models\Aset;
use App\Models\DetailJurnal;
use App\Models\Jurnal;
use App\Models\Periode;
use App\Models\Transaksi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JurnalPenutupService extends JurnalService
{
    const KODE_KATEGORI_ASET_NETO  = '3';
    const KODE_KATEGORI_PENDAPATAN = '4';
    const KODE_KATEGORI_BEBAN      = '5';

    const KELAS_TANPA_PEMBATASAN  = '1';
    const KELAS_DENGAN_PEMBATASAN = '2';

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

    public function validasiPeriodeSiapTutup(Periode $periode): ?string
    {
        $adaPosted = Jurnal::where('periode_id', $periode->id)
            ->where('jenis_jurnal', '!=', 'PENUTUP')
            ->where('status', 'POSTED')
            ->exists();

        if (!$adaPosted) {
            return 'Periode belum memiliki jurnal yang diposting. '
                 . 'Catat minimal satu transaksi dan posting sebelum menutup periode.';
        }

        $awal  = $periode->tanggal_awal->toDateString();
        $akhir = $periode->tanggal_akhir->toDateString();

        $transaksiMenggantung = Transaksi::whereBetween('tanggal_transaksi', [$awal, $akhir])
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('status_jurnal', 'UNMAPPED')
                        ->where(function ($appr) {
                            $appr->whereNull('status_persetujuan')
                                 ->orWhere('status_persetujuan', '!=', 'REJECTED');
                        });
                })
                ->orWhereIn('status_persetujuan', ['PENDING', 'REVISION']);
            })
            ->orderBy('tanggal_transaksi')
            ->get();

        if ($transaksiMenggantung->isNotEmpty()) {
            $rincian = $transaksiMenggantung
                ->map(function ($t) {
                    $tgl  = $t->tanggal_transaksi?->translatedFormat('d M Y') ?? '-';
                    $desk = $t->deskripsi ?: 'Transaksi tanpa deskripsi';
                    $nom  = 'Rp ' . number_format((float) $t->jumlah, 0, ',', '.');
                    return "- {$desk} ({$tgl}, {$nom})";
                })
                ->implode("\n");

            return 'Masih ada ' . $transaksiMenggantung->count() . ' transaksi yang belum '
                 . "dijurnalkan/disetujui sehingga belum masuk buku besar:\n" . $rincian
                 . "\nSelesaikan pemetaan jurnal & persetujuan transaksi tersebut sebelum menutup periode.";
        }

        $jurnalDraft = Jurnal::with('detailJurnal.akun')
            ->where('periode_id', $periode->id)
            ->where('jenis_jurnal', '!=', 'PENUTUP')
            ->where('status', 'DRAFT')
            ->orderBy('tanggal')
            ->get();

        if ($jurnalDraft->isNotEmpty()) {
            // Daftar jurnalnya tidak dirangkai di pesan ini karena kartu tautan ke
            // masing-masing jurnal draft sudah ditampilkan tepat di bawah peringatan.
            return 'Masih ada ' . $jurnalDraft->count() . ' jurnal yang belum diposting ke buku besar. '
                 . 'Posting seluruh jurnal draft di bawah ini terlebih dahulu agar buku besar lengkap sebelum ditutup.';
        }

        $saldo = DetailJurnal::whereHas('jurnal', function ($q) use ($periode) {
                $q->where('periode_id', $periode->id)
                  ->where('jenis_jurnal', '!=', 'PENUTUP')
                  ->where('status', 'POSTED');
            })
            ->selectRaw("COALESCE(SUM(CASE WHEN tipe = 'DEBIT' THEN nominal ELSE 0 END), 0) AS total_debit")
            ->selectRaw("COALESCE(SUM(CASE WHEN tipe = 'KREDIT' THEN nominal ELSE 0 END), 0) AS total_kredit")
            ->first();

        $totalDebit  = (float) ($saldo->total_debit ?? 0);
        $totalKredit = (float) ($saldo->total_kredit ?? 0);

        if (abs($totalDebit - $totalKredit) > 0.5) {
            return 'Buku besar periode belum seimbang: total debit Rp '
                 . number_format($totalDebit, 0, ',', '.')
                 . ' tidak sama dengan total kredit Rp '
                 . number_format($totalKredit, 0, ',', '.')
                 . '. Perbaiki jurnal yang tidak seimbang sebelum menutup periode.';
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function peringatanSebelumTutup(Periode $periode): array
    {
        $peringatan = [];

        $asetBelumDisusutkan = Aset::aktif()
            ->whereNotNull('umur_manfaat')
            ->where('umur_manfaat', '>', 0)
            ->belumDilepas()
            ->whereDoesntHave('jurnalPenyesuaian', fn($q) => $q
                ->where('tipe_penyesuaian', 'PENYUSUTAN_ASET')
                ->whereIn('status', ['POSTED', 'DRAFT'])
                ->where('periode_id', $periode->id)
            )
            ->count();

        if ($asetBelumDisusutkan > 0) {
            $peringatan[] = "Ada {$asetBelumDisusutkan} aset yang belum dicatat "
                . "penyusutannya pada periode ini. Sebaiknya catat jurnal penyusutan "
                . "sebelum menutup periode.";
        }

        return $peringatan;
    }

    /**
     * Daftar jurnal berstatus DRAFT (selain PENUTUP) yang menghambat penutupan,
     * lengkap dengan tautan ke halaman detail masing-masing jurnal agar bisa
     * langsung dibuka dan diposting oleh pengguna.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function getJurnalDraftPenghambat(Periode $periode): Collection
    {
        $rute = [
            'UMUM'        => 'dashboard.jurnal-umum.index',
            'PENYESUAIAN' => 'dashboard.jurnal-penyesuaian.index',
            'KOREKSI'     => 'dashboard.jurnal-koreksi.index',
            'PEMBUKA'     => 'dashboard.jurnal-pembuka.index',
        ];

        $label = [
            'UMUM'        => 'Jurnal Umum',
            'PENYESUAIAN' => 'Jurnal Penyesuaian',
            'KOREKSI'     => 'Jurnal Koreksi',
            'PEMBUKA'     => 'Jurnal Pembuka',
        ];

        return Jurnal::with('detailJurnal.akun')
            ->where('periode_id', $periode->id)
            ->where('jenis_jurnal', '!=', 'PENUTUP')
            ->where('status', 'DRAFT')
            ->orderBy('tanggal')
            ->get()
            ->map(function ($j) use ($rute, $label) {
                $akun = $j->detailJurnal
                    ->map(fn($d) => $d->akun ? "{$d->akun->kode_akun} {$d->akun->nama_akun}" : null)
                    ->filter()
                    ->unique()
                    ->implode(', ');

                $namaRute = $rute[$j->jenis_jurnal] ?? null;

                return [
                    'id'          => $j->id,
                    'kode_jurnal' => $j->kode_jurnal,
                    'jenis'       => $j->jenis_jurnal,
                    'jenis_label' => $label[$j->jenis_jurnal] ?? $j->jenis_jurnal,
                    'keterangan'  => $j->keterangan ?: 'Tanpa keterangan',
                    'tanggal'     => $j->tanggal?->translatedFormat('d M Y') ?? '-',
                    'akun'        => $akun,
                    'url'         => $namaRute ? route($namaRute, ['buka' => $j->id]) : null,
                ];
            })
            ->values();
    }

    private function validasiTanggalPenutupan(Periode $periode, string $tanggal): ?string
    {
        if (\Illuminate\Support\Carbon::parse($tanggal)->lt($periode->tanggal_akhir)) {
            return 'Tanggal penutupan tidak boleh sebelum akhir periode ('
                 . $periode->tanggal_akhir->translatedFormat('d M Y') . '). '
                 . 'Tutup periode pada atau setelah tanggal tersebut agar tidak ada '
                 . 'transaksi di sisa hari yang terlewat.';
        }

        return null;
    }

    /**
     * Periode hanya boleh DITUTUP (diposting) setelah benar-benar berakhir,
     * yaitu hari ini sudah mencapai/melewati tanggal akhir periode. Menyimpan
     * draft tetap diperbolehkan sebelum periode berakhir.
     */
    private function validasiPeriodeSudahBerakhir(Periode $periode): ?string
    {
        $hariIni = now()->startOfDay();
        $akhir   = $periode->tanggal_akhir->copy()->startOfDay();
        if ($hariIni->lt($akhir)) {
            return 'Periode ' . $periode->nama_periode . ' baru dapat ditutup mulai '
                 . $akhir->translatedFormat('d F Y') . ' (akhir periode). '
                 . 'Hari ini baru ' . $hariIni->translatedFormat('d F Y') . '. '
                 . 'Sebelum itu, penutupan hanya dapat disimpan sebagai draft.';
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
            'jurnal_draft'     => $this->getJurnalDraftPenghambat($periode),
            'peringatan'       => $this->peringatanSebelumTutup($periode),
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

    private function kelasDana(string $kodeAkun): string
    {
        $pos  = strpos($kodeAkun, '-');
        $body = $pos === false ? $kodeAkun : substr($kodeAkun, $pos + 1);
        return substr($body, 0, 1);
    }

    private function indeksDana(string $kodeAkun): string
    {
        $pos  = strpos($kodeAkun, '-');
        $body = $pos === false ? $kodeAkun : substr($kodeAkun, $pos + 1);
        return substr($body, 1, 1);
    }

    private function isTanpaPembatasan(string $kodeAkun): bool
    {
        return $this->kelasDana($kodeAkun) === self::KELAS_TANPA_PEMBATASAN;
    }

    private function kodeDanaTerikat(string $f): string
    {
        return self::KODE_KATEGORI_ASET_NETO . '-' . self::KELAS_DENGAN_PEMBATASAN . $f . '01';
    }

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

    public function susunJurnalPelepasanPembatasan(array $ringkasan): array
    {
        $danaUmum = Akun::where('kode_akun', self::KODE_DANA_UMUM)->first();
        if (!$danaUmum) {
            return [];
        }

        $detail = [];

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

        if ($err = $this->validasiTanggalPenutupan($periode, $tanggal)) {
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
        if ($err = $this->validasiPeriodeSudahBerakhir($periode)) {
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
        if ($err = $this->validasiTanggalPenutupan($periode, $tanggal)) {
            return $err;
        }
        if ($err = $this->validasiPeriodeSudahBerakhir($periode)) {
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
