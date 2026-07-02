<?php

namespace App\Services\Akuntansi;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;

class JurnalKoreksiService extends JurnalService
{
    // ── Query ──────────────────────────────────────────────────────────────
    public function getList(
        ?string $search      = '',
        ?string $periodeId   = '',
        ?string $referensiId = '',
        ?string $status      = '',
        int     $perPage     = 10
    ) {
        return Jurnal::with(['periode', 'detailJurnal.akun'])
            ->koreksi()
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->when($status,    fn($q) => $q->where('status', strtoupper($status)))
            ->when($search,    fn($q) =>
                $q->where('keterangan', 'like', "%{$search}%")
            )
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getById(Jurnal $jurnal): Jurnal
    {
        return $jurnal->load('periode', 'detailJurnal.akun', 'aset');
    }

    public function getAkunList(): array
    {
        return Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get()
            ->groupBy(fn($akun) => $akun->kategoriAkun->nama_kategori ?? 'Lainnya')
            ->map(fn($group, $kategori) => [
                'kategori' => $kategori,
                'akun'     => $group->map(fn($a) => [
                    'id'           => $a->id,
                    'kode_akun'    => $a->kode_akun,
                    'nama_akun'    => $a->nama_akun,
                    'saldo_normal' => $a->saldo_normal,
                    'label'        => $a->kode_akun . ' — ' . $a->nama_akun,
                ])->values(),
            ])
            ->values()
            ->toArray();
    }

    public function getJurnalData()
    {
        return Jurnal::with(['periode', 'detailJurnal.akun', 'aset'])
            ->where('status', 'POSTED')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(fn($jurnal) => [
                'id'         => $jurnal->id,
                'periode_id' => $jurnal->periode_id,
                'nomor'      => 'JP-' . str_pad($jurnal->id, 5, '0', STR_PAD_LEFT),
                'tanggal'    => $jurnal->tanggal,
                'keterangan' => $jurnal->keterangan,
                'detail'     => $jurnal->detailJurnal->map(fn($detail) => [
                    'akun'   => $detail->akun->nama_akun,
                    'posisi' => $detail->tipe === 'DEBIT' ? 'D' : 'K',
                    'debit'  => $detail->tipe === 'DEBIT'  ? (float) $detail->nominal : 0,
                    'kredit' => $detail->tipe === 'KREDIT' ? (float) $detail->nominal : 0,
                ])->values(),
            ])
            ->values();
    }

    // ── Store ──────────────────────────────────────────────────────────────
    public function store(array $data, string $status = 'DRAFT'): Jurnal
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

            $this->storeDetail($jurnal, $data['detail']);

            return $jurnal->load('detailJurnal.akun');
        });
    }

    // ── Hook: delete ───────────────────────────────────────────────────────
    protected function beforeDelete(Jurnal $jurnal): void
    {
        $jurnal->aset()->detach();
    }
}