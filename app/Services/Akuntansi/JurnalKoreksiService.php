<?php

namespace App\Services\Akuntansi;

use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class JurnalKoreksiService extends JurnalService
{
    public function __construct(private AkunQueryService $akunQuery) {}

    // ── Query (getter — dipertahankan) ────────────────────────────

    public function daftar(array $filter): LengthAwarePaginator
    {
        return Jurnal::with(['periode', 'detailJurnal.akun', 'transaksi'])
            ->koreksi()
            ->when($filter['periode_id'] ?? null, fn($q) => $q->where('periode_id', $filter['periode_id']))
            ->when($filter['status'] ?? null,    fn($q) => $q->where('status', strtoupper($filter['status'])))
            ->when($filter['search'] ?? null,    fn($q) =>
                $q->where('keterangan', 'like', "%{$filter['search']}%")
                ->orWhereHas('transaksi', fn($q2) =>
                    $q2->where('deskripsi', 'like', "%{$filter['search']}%")
                )
            )
            ->orderBy('tanggal', 'desc')
            ->paginate($filter['per_page'] ?? 10)
            ->withQueryString();
    }

    public function getById(Jurnal $jurnal): Jurnal
    {
        return $jurnal->load('periode', 'detailJurnal.akun', 'aset');
    }

    /** Delegasi ke AkunQueryService (hapus duplikasi). */
    public function getAkunList(): array
    {
        return $this->akunQuery->getGroupedAkun();
    }

    public function getJurnalData()
    {
        return Jurnal::with(['periode', 'detailJurnal.akun', 'aset', 'transaksi'])
            ->where('status', 'POSTED')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(fn($jurnal) => [
                'id'         => $jurnal->id,
                'periode_id' => $jurnal->periode_id,
                'nomor'      => $jurnal->kode_jurnal,
                'tanggal'    => $jurnal->tanggal->format('d M Y'),
                'keterangan' => $jurnal->keterangan ?: $jurnal->transaksi?->deskripsi ?: '-',
                'detail'     => $jurnal->detailJurnal->map(fn($detail) => [
                    'akun'   => $detail->akun->nama_akun,
                    'posisi' => $detail->tipe === 'DEBIT' ? 'D' : 'K',
                    'debit'  => $detail->tipe === 'DEBIT'  ? (float) $detail->nominal : 0,
                    'kredit' => $detail->tipe === 'KREDIT' ? (float) $detail->nominal : 0,
                ])->values(),
            ])
            ->values();
    }

    // ── Aksi ─────────────────────────────────────────────

    /** Mencatat jurnal koreksi. */
    public function catatKoreksi(array $data, string $status = 'DRAFT'): Jurnal
    {
        return DB::transaction(function () use ($data, $status) {
            $periode = Periode::findOrFail($data['periode_id']);

            $jurnal = Jurnal::create([
                'periode_id'       => $periode->id,
                'transaksi_id'     => null,
                'jurnal_ref_id'    => $data['jurnal_ref_id'] ?? null,
                'jenis_jurnal'     => 'KOREKSI',
                'tipe_penyesuaian' => null,
                'tanggal'          => $data['tanggal'],
                'keterangan'       => $data['keterangan'] ?? null,
                'status'           => $status,
            ]);

            $this->catatDetailJurnal($jurnal, $data['detail']);

            return $jurnal->load('detailJurnal.akun');
        });
    }

    // ── Hook: sebelum hapus ────────────────────────────────────

    protected function sebelumPenghapusan(Jurnal $jurnal): void
    {
        $jurnal->aset()->detach();
    }
}
