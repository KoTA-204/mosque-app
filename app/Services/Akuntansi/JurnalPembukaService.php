<?php

namespace App\Services\Akuntansi;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use RuntimeException;

/**
 * JurnalPembukaService
 *
 * Aturan bisnis "saldo awal" (opening balance).
 *  - Singleton: hanya boleh ada satu jurnal pembuka.
 *  - Periode ditentukan langsung dari (nama_periode, tanggal_awal, tanggal_akhir)
 *    yang diinput user; tanggal_akhir sudah dihitung di sisi client (akhir bulan
 *    dari tanggal_awal) dan divalidasi ulang di sini sebagai pengaman lapis dua.
 *  - Terkunci setelah diposting / ada transaksi turunan.
 */
class JurnalPembukaService extends JurnalService
{
    private const JENIS = 'PEMBUKA';

    // -- KONTRAK PARENT -------------------------------------------- (tidak berubah)

    public function daftar(array $filter): LengthAwarePaginator
    {
        return Jurnal::with(['periode', 'detailJurnal.akun'])
            ->where('jenis_jurnal', self::JENIS)
            ->latest('tanggal')
            ->paginate($filter['per_page'] ?? 15);
    }

    // -- READ ------------------------------------------------------ (tidak berubah)

    public function getJurnalPembuka(): ?Jurnal
    {
        return Jurnal::with(['periode', 'detailJurnal.akun'])
            ->where('jenis_jurnal', self::JENIS)
            ->latest('tanggal')
            ->first();
    }

    public function sudahDibuat(): bool
    {
        return Jurnal::where('jenis_jurnal', self::JENIS)->exists();
    }

    public function getStatistik(): array
    {
        $jurnal = $this->getJurnalPembuka();

        return [
            'ada'     => (bool) $jurnal,
            'status'  => $jurnal?->status,
            'periode' => $jurnal?->periode?->nama_periode,
        ];
    }

    public function getAkunTransaksional()
    {
        return Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get()
            ->map(fn ($a) => [
                'id'           => $a->id,
                'kode'         => $a->kode_akun,
                'nama'         => $a->nama_akun,
                'saldo_normal' => $a->saldo_normal,
            ]);
    }

    public function toDetailArray(Jurnal $jurnal): array
    {
        $jurnal->loadMissing(['periode', 'detailJurnal.akun']);

        return [
            'kode_jurnal'  => $jurnal->kode_jurnal,
            'status'       => $jurnal->status,
            'tanggal'      => optional($jurnal->tanggal)->format('d M Y'),
            'periode'      => $jurnal->periode?->nama_periode,
            'keterangan'   => $jurnal->keterangan ?? '-',
            'dibuat_oleh'  => $jurnal->dibuatOleh?->name ?? '-',
            'total_debit'  => $jurnal->total_debit,
            'total_kredit' => $jurnal->total_kredit,
            'is_balance'   => $jurnal->is_balance,
            'detail'       => $jurnal->detailJurnal->map(fn ($d) => [
                'akun'    => $d->akun->kode_akun . ' - ' . $d->akun->nama_akun,
                'tipe'    => $d->tipe,
                'nominal' => $d->nominal,
            ])->values(),
        ];
    }

    // -- GUARD ----------------------------------------------------- (tidak berubah)

    public function dapatDiubah(Jurnal $jurnal, ?string &$alasan = null): bool
    {
        if ($jurnal->status === 'POSTED') {
            $alasan = 'Jurnal yang sudah diposting tidak dapat diubah.';
            return false;
        }

        if ($this->adaTransaksiTurunan($jurnal)) {
            $alasan = 'Sudah ada transaksi setelah saldo awal; jurnal pembuka tidak dapat diubah/dihapus.';
            return false;
        }

        return true;
    }

    protected function adaTransaksiTurunan(Jurnal $jurnal): bool
    {
        return Jurnal::where('jenis_jurnal', '!=', self::JENIS)
            ->where('tanggal', '>=', $jurnal->tanggal)
            ->exists();
    }

    // -- WRITE -----------------------------------------------------
    
    public function catatSaldoAwal(array $data): Jurnal
    {
        // Jurnal pembuka baru tidak boleh memakai tanggal awal yang sudah berlalu.
        $this->pastikanTanggalTidakBerlalu($data['tanggal_awal']);

        return DB::transaction(function () use ($data) {
            $periode = $this->derivePeriodeDariTanggal(
                $data['nama_periode'],
                $data['tanggal_awal'],
                $data['tanggal_akhir']
            );

            // Selalu dibuat sebagai DRAFT dulu; posting dilakukan lewat parent.
            $jurnal = Jurnal::create([
                'periode_id'   => $periode->id,
                'jenis_jurnal' => self::JENIS,
                'tanggal'      => $periode->tanggal_awal,   // saldo awal = awal periode
                'keterangan'   => $data['keterangan'] ?? null,
                'dibuat_oleh'  => Auth::id(),
                'status'       => 'DRAFT',
            ]);

            $this->catatDetailJurnal($jurnal, $data['detail'] ?? []);

            if (($data['submit_type'] ?? null) === 'posting') {
                $this->postingLewatParent($jurnal->refresh());
            }

            return $jurnal;
        });
    }

    public function perbaruiSaldoAwal(Jurnal $jurnal, array $data): Jurnal
    {
        $tanggalAwalSaatIni = optional($jurnal->periode)->tanggal_awal?->format('Y-m-d')
            ?? optional($jurnal->tanggal)->format('Y-m-d');

        $this->pastikanTanggalTidakBerlalu($data['tanggal_awal'], $tanggalAwalSaatIni);

        return DB::transaction(function () use ($jurnal, $data) {
            $periode = $this->derivePeriodeDariTanggal(
                $data['nama_periode'],
                $data['tanggal_awal'],
                $data['tanggal_akhir']
            );

            $jurnal->update([
                'periode_id' => $periode->id,
                'tanggal'    => $periode->tanggal_awal,
                'keterangan' => $data['keterangan'] ?? null,
                'status'     => 'DRAFT',
            ]);

            $jurnal->detailJurnal()->delete();
            $this->catatDetailJurnal($jurnal, $data['detail'] ?? []);

            if (($data['submit_type'] ?? null) === 'posting') {
                $this->postingLewatParent($jurnal->refresh());
            }

            return $jurnal;
        });
    }

    // -- POSTING / HAPUS --------------------------------------------- (tidak berubah)

    public function postingSaldoAwal(Jurnal $jurnal): array
    {
        if ($jurnal->jenis_jurnal !== self::JENIS) {
            return $this->gagal('Jurnal ini bukan jurnal pembuka.', 422);
        }

        $hasil = parent::postingKeBukuBesar($jurnal);

        return $hasil === true
            ? $this->sukses('Jurnal berhasil diposting.')
            : $this->gagal((string) $hasil, 422);
    }

    public function hapusSaldoAwal(Jurnal $jurnal): array
    {
        if (! $this->dapatDiubah($jurnal, $alasan)) {
            return $this->gagal($alasan, 403);
        }

        $hasil = parent::hapusJurnal($jurnal);

        return $hasil === true
            ? $this->sukses('Jurnal pembuka berhasil dihapus.')
            : $this->gagal((string) $hasil, 422);
    }

    private function postingLewatParent(Jurnal $jurnal): void
    {
        $hasil = parent::postingKeBukuBesar($jurnal);

        if ($hasil !== true) {
            throw new RuntimeException(
                is_string($hasil) ? $hasil : 'Gagal memposting jurnal pembuka.'
            );
        }
    }

    // -- HELPER ------------------------------------------------------ (tidak berubah)

    protected function sukses(string $message = 'Berhasil.'): array
    {
        return ['ok' => true, 'status' => 200, 'message' => $message];
    }

    protected function gagal(string $message, int $status = 422): array
    {
        return ['ok' => false, 'status' => $status, 'message' => $message];
    }

    protected function derivePeriodeDariTanggal(string $namaPeriode, string $tanggalAwal, string $tanggalAkhir): Periode
    {
        $awal  = Carbon::parse($tanggalAwal)->startOfDay();
        $akhir = Carbon::parse($tanggalAkhir)->startOfDay();

        if ($akhir->lt($awal)) {
            throw new RuntimeException('Tanggal akhir periode tidak boleh sebelum tanggal awal.');
        }

        return Periode::firstOrCreate(
            [
                'tanggal_awal'  => $awal->toDateString(),
                'tanggal_akhir' => $akhir->toDateString(),
            ],
            [
                'nama_periode' => $namaPeriode,
                'tipe'         => 'bulanan',
                'status'       => true,
            ]
        );
    }

    /**
     *
     * @param  string       $tanggalAwal          Format Y-m-d, contoh "2026-07-15".
     * @param  string|null  $tanggalAwalSaatIni   Tanggal awal milik jurnal yang sedang
     *         diedit (Y-m-d). Jika sama dengan $tanggalAwal, validasi dilewati — supaya
     *         user tetap bisa menyimpan ulang tanggal lama miliknya.
     */
    protected function pastikanTanggalTidakBerlalu(string $tanggalAwal, ?string $tanggalAwalSaatIni = null): void
    {
        if ($tanggalAwalSaatIni !== null && $tanggalAwal === $tanggalAwalSaatIni) {
            return;
        }

        if (Carbon::parse($tanggalAwal)->startOfDay()->lt(Carbon::today())) {
            throw new RuntimeException('Tanggal awal saldo yang sudah berlalu tidak dapat dipilih untuk jurnal pembuka.');
        }
    }
}