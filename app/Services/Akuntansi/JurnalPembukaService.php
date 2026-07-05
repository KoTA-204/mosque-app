<?php

namespace App\Services\Akuntansi;

use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JurnalPembukaService extends JurnalService
{
    private const JENIS = 'PEMBUKA';

    /** Daftar jurnal pembuka terfilter. */
    public function daftar(array $filter): LengthAwarePaginator
    {
        return Jurnal::with(['periode', 'detailJurnal'])
            ->where('jenis_jurnal', self::JENIS)
            ->when($filter['periode'] ?? null, fn($q) => $q->where('periode_id', $filter['periode']))
            ->when($filter['status'] ?? null, fn($q) => $q->where('status', $filter['status']))
            ->when($filter['search'] ?? null, fn($q) =>
                $q->where('keterangan', 'like', "%{$filter['search']}%")
            )
            ->orderByDesc('tanggal')
            ->paginate($filter['per_page'] ?? 10)
            ->withQueryString();
    }

    /** Statistik jumlah jurnal pembuka. */
    public function getStatistik(): array
    {
        return [
            'total'  => Jurnal::where('jenis_jurnal', self::JENIS)->count(),
            'posted' => Jurnal::where('jenis_jurnal', self::JENIS)->where('status', 'POSTED')->count(),
            'draft'  => Jurnal::where('jenis_jurnal', self::JENIS)->where('status', 'DRAFT')->count(),
        ];
    }

    /** Catat jurnal pembuka (saldo awal) baru; buat/ambil periode dulu. */
    public function catatSaldoAwal(array $data): Jurnal
    {
        return DB::transaction(function () use ($data) {
            $periode = Periode::firstOrCreate(
                [
                    'tanggal_awal'  => $data['tanggal_mulai'],
                    'tanggal_akhir' => $data['tanggal_akhir'],
                ],
                [
                    'nama_periode' => Carbon::parse($data['tanggal_mulai'])->translatedFormat('F Y'),
                    'tipe'         => 'bulanan',
                    'status'       => true,
                ]
            );

            $jurnal = Jurnal::create([
                'periode_id'   => $periode->id,
                'jenis_jurnal' => self::JENIS,
                'tanggal'      => $data['tanggal_mulai'],
                'keterangan'   => $data['keterangan'] ?? null,
                'status'       => ($data['submit_type'] ?? null) === 'posting' ? 'POSTED' : 'DRAFT',
            ]);

            $this->catatDetailJurnal($jurnal, $data['detail'] ?? []);

            return $jurnal;
        });
    }

    /** Perbarui jurnal pembuka (saldo awal). */
    public function perbaruiSaldoAwal(Jurnal $jurnal, array $data): Jurnal
    {
        return DB::transaction(function () use ($jurnal, $data) {
            $jurnal->update([
                'periode_id' => $data['periode_id'],
                'tanggal'    => $data['tanggal_mulai'],
                'keterangan' => $data['keterangan'] ?? null,
                'status'     => ($data['submit_type'] ?? null) === 'posting' ? 'POSTED' : 'DRAFT',
            ]);

            $jurnal->detailJurnal()->delete();
            $this->catatDetailJurnal($jurnal, $data['detail'] ?? []);

            return $jurnal;
        });
    }
}
