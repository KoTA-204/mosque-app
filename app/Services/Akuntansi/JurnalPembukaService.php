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
 *  - Periode dihitung dari (jenis periode + tanggal awal).
 *  - Terkunci setelah diposting / ada transaksi turunan.
 *
 * Mewarisi JurnalService (parent) yang SUDAH menyediakan:
 *  - protected catatDetailJurnal(Jurnal, array): void
 *  - public isDetailSeimbang(array): bool  &  isBalanced(Jurnal): bool
 *  - public postingKeBukuBesar(Jurnal): bool|string   (Template Method + hook setelahPosting)
 *  - public hapusJurnal(Jurnal): bool|string          (Template Method + hook sebelumPenghapusan)
 *  - abstract daftar(array): LengthAwarePaginator      (WAJIB diimplementasikan di sini)
 *
 * Catatan penting soal kompatibilitas:
 *  Method posting & hapus milik parent BUKAN untuk di-override dengan return type
 *  berbeda (itu memicu FatalError). Di sini kita PANGGIL parent-nya lalu bungkus
 *  hasilnya (bool|string) menjadi array ['ok','status','message'] via method sendiri
 *  bernama postingSaldoAwal() & hapusSaldoAwal().
 */
class JurnalPembukaService extends JurnalService
{
    private const JENIS = 'PEMBUKA';

    // -- KONTRAK PARENT --------------------------------------------

    /**
     * Implementasi wajib dari JurnalService::daftar().
     * Jurnal pembuka bersifat singleton, tetapi tetap dikembalikan sebagai
     * paginator agar kompatibel dengan kontrak induk.
     */
    public function daftar(array $filter): LengthAwarePaginator
    {
        return Jurnal::with(['periode', 'detailJurnal.akun'])
            ->where('jenis_jurnal', self::JENIS)
            ->latest('tanggal')
            ->paginate($filter['per_page'] ?? 15);
    }

    // -- READ ------------------------------------------------------

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

    // -- GUARD -----------------------------------------------------

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
        return DB::transaction(function () use ($data) {
            $tanggal = Carbon::parse($data['tanggal_awal']);
            $periode = $this->derivePeriode($tanggal);

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
        return DB::transaction(function () use ($jurnal, $data) {
            $tanggal = Carbon::parse($data['tanggal_awal']);
            $periode = $this->derivePeriode($tanggal);

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

    // -- POSTING / HAPUS (bungkus Template Method milik parent) ----

    /**
     * Posting jurnal pembuka ke buku besar.
     * Memanggil parent::postingKeBukuBesar() (bool|string) lalu membungkus
     * hasilnya menjadi array ['ok','status','message'] untuk controller.
     */
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

    /**
     * Hapus jurnal pembuka.
     * Guard domain (dapatDiubah) diperiksa dulu, lalu didelegasikan ke
     * parent::hapusJurnal() (bool|string).
     */
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

    /**
     * Dipakai internal oleh catat/perbarui saat submit_type = posting.
     * Melempar RuntimeException bila gagal agar transaksi di-rollback
     * (validasi keseimbangan sudah dijamin FormRequest, ini pengaman lapis dua).
     */
    private function postingLewatParent(Jurnal $jurnal): void
    {
        $hasil = parent::postingKeBukuBesar($jurnal);

        if ($hasil !== true) {
            throw new RuntimeException(
                is_string($hasil) ? $hasil : 'Gagal memposting jurnal pembuka.'
            );
        }
    }

    // -- HELPER ----------------------------------------------------

    protected function sukses(string $message = 'Berhasil.'): array
    {
        return ['ok' => true, 'status' => 200, 'message' => $message];
    }

    protected function gagal(string $message, int $status = 422): array
    {
        return ['ok' => false, 'status' => $status, 'message' => $message];
    }

    /**
     * Hitung periode dari (tanggal awal + jenis periode) yang dipilih user.
     * Tanggal akhir dihitung sistem agar periode konsisten & tidak tumpang tindih.
     */
    protected function derivePeriode(Carbon $tanggal): Periode
    {
        // Aplikasi hanya menggunakan periode bulanan.
        $awal  = $tanggal->copy()->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();
        $nama  = $awal->translatedFormat('F Y');   // contoh: "Maret 2025"

        return Periode::firstOrCreate(
            [
                'tanggal_awal'  => $awal->toDateString(),
                'tanggal_akhir' => $akhir->toDateString(),
            ],
            [
                'nama_periode' => $nama,
                'tipe'         => 'bulanan',
                'status'       => true,
            ]
        );
    }
}
