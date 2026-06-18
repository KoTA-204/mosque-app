<?php

namespace App\Services;

use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JurnalPembukaService extends JurnalService
{
    private const JENIS = 'PEMBUKA';

    // untuk daftar jurnal pembuka terfilter
    public function daftar(array $filter): LengthAwarePaginator
    {
        return Jurnal::with(['periode', 'detailJurnal'])
            ->where('jenis_jurnal', self::JENIS)
            ->when($filter['periode'] ?? null, fn($q) => $q->where('periode_id', $filter['periode']))
            ->when($filter['status'] ?? null, fn($q) => $q->where('status', $filter['status']))
            ->when($filter['search'] ?? null, fn($q) =>
                $q->where('keterangan', 'like', "%{$filter['search']}%")
                  ->orWhere('kode_jurnal', 'like', "%{$filter['search']}%")
            )
            ->orderByDesc('tanggal')
            ->paginate($filter['per_page'] ?? 10)
            ->withQueryString();
    }

    // untuk statistik jumlah jurnal pembuka
    public function stats(): array
    {
        return [
            'total'  => Jurnal::where('jenis_jurnal', self::JENIS)->count(),
            'posted' => Jurnal::where('jenis_jurnal', self::JENIS)->where('status', 'POSTED')->count(),
            'draft'  => Jurnal::where('jenis_jurnal', self::JENIS)->where('status', 'DRAFT')->count(),
        ];
    }

    // untuk cek seimbang dari raw detail (sebelum jurnal dibuat)
    public function detailSeimbang(array $detail): bool
    {
        $totalDebit = $totalKredit = 0;
        foreach ($detail as $row) {
            $nominal = $this->parseNominal($row['nominal'] ?? 0);
            if ($row['tipe'] === 'DEBIT')  $totalDebit  += $nominal;
            if ($row['tipe'] === 'KREDIT') $totalKredit += $nominal;
        }
        return round($totalDebit, 2) === round($totalKredit, 2);
    }

    // untuk simpan jurnal pembuka baru (buat/ambil periode dulu)
    public function simpan(array $data): Jurnal
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
            $this->storeDetail($jurnal, $data['detail'] ?? []);
            return $jurnal;
        });
    }

    // untuk perbarui jurnal pembuka
    public function perbarui(Jurnal $jurnal, array $data): Jurnal
    {
        return DB::transaction(function () use ($jurnal, $data) {
            $jurnal->update([
                'periode_id' => $data['periode_id'],
                'tanggal'    => $data['tanggal_mulai'],
                'keterangan' => $data['keterangan'] ?? null,
                'status'     => ($data['submit_type'] ?? null) === 'posting' ? 'POSTED' : 'DRAFT',
            ]);
            $jurnal->detailJurnal()->delete();
            $this->storeDetail($jurnal, $data['detail'] ?? []);
            return $jurnal;
        });
    }
}