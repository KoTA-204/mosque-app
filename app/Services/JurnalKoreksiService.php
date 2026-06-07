<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;

class JurnalKoreksiService
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

    public function getPeriodeAktif(): ?Periode
    {
        return Periode::aktif()->where('tipe', 'bulanan')->latest('tanggal_awal')->first();
    }

    public function getPeriodeList()
    {
        return Periode::orderBy('tanggal_awal', 'desc')->get();
    }

    public function getAkunList(): array
    {
        $query = Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun');

        return $query->get()
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
            ->map(function ($jurnal) {
            return [
                'id'         => $jurnal->id,
                'periode_id' => $jurnal->periode_id,
                'nomor'      => 'JP-' . str_pad($jurnal->id, 5, '0', STR_PAD_LEFT),
                'tanggal'    => $jurnal->tanggal,
                'keterangan' => $jurnal->keterangan,

                'detail' => $jurnal->detailJurnal->map(function ($detail) {
                    return [
                        'akun'   => $detail->akun->nama_akun,
                        'posisi' => $detail->tipe === 'DEBIT' ? 'D' : 'K',
                        'debit'  => $detail->tipe === 'DEBIT'
                            ? (float) $detail->nominal
                            : 0,
                        'kredit' => $detail->tipe === 'KREDIT'
                            ? (float) $detail->nominal
                            : 0,
                    ];
                })->values(),
            ];
        })
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

            // Simpan detail jurnal (baris debit/kredit)
            foreach ($data['detail'] as $detail) {
                $nominalRaw = $detail['nominal'] ?? 0;
                $nominal    = is_string($nominalRaw)
                    ? (float) str_replace(['.', ','], ['', '.'], $nominalRaw)
                    : (float) $nominalRaw;

                if (empty($detail['akun_id']) || $nominal <= 0) continue;

                DetailJurnal::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id'   => $detail['akun_id'],
                    'tipe'      => $detail['tipe'],
                    'nominal'   => $nominal,
                ]);
            }

            return $jurnal->load('detailJurnal.akun');
        });
    }

    // ── Post ───────────────────────────────────────────────────────────────

    public function post(Jurnal $jurnal): bool|string
    {
        if ($jurnal->status === 'POSTED') {
            return 'Jurnal sudah diposting';
        }

        $jurnal->load('detailJurnal');

        if ($jurnal->detailJurnal->isEmpty()) {
            return 'Jurnal harus memiliki minimal satu entri';
        }

        $totalDebit  = $jurnal->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = $jurnal->detailJurnal->where('tipe', 'KREDIT')->sum('nominal');

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            return 'Total debit dan kredit harus sama sebelum diposting';
        }

        DB::transaction(function () use ($jurnal) {
            $jurnal->update(['status' => 'POSTED']);
        });

        return true;
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    public function delete(Jurnal $jurnal): bool|string
    {
        if ($jurnal->status === 'POSTED') {
            return 'Jurnal yang sudah diposting tidak bisa dihapus';
        }

        DB::transaction(function () use ($jurnal) {
            $jurnal->aset()->detach();
            $jurnal->detailJurnal()->delete();
            $jurnal->delete();
        });

        return true;
    }
}