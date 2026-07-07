<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalPembukaRequest;
use App\Http\Requests\UpdateJurnalPembukaRequest;
use App\Models\Jurnal;
use App\Models\Akun;
use App\Services\Akuntansi\JurnalPembukaService;
use Illuminate\Http\Request;

class JurnalPembukaController extends Controller
{
    public function __construct(private JurnalPembukaService $service) {}

    public function tampilkanJurnalPembuka(Request $request)
    {
        $filter = [
            'search'   => $request->input('search', ''),
            'periode'  => $request->input('periode', ''),
            'status'   => $request->input('status', ''),
            'per_page' => $request->input('per_page', 10),
        ];

        $jurnals  = $this->service->daftar($filter);
        $periodes = $this->service->getPeriodeList();
        $stats    = $this->service->getStatistik();

        $search  = $filter['search'];
        $periode = $filter['periode'];
        $status  = $filter['status'];
        $perPage = $filter['per_page'];

        return view('pages.akuntansi.jurnal-pembuka.index', compact(
            'jurnals', 'periodes', 'stats', 'search', 'periode', 'status', 'perPage'
        ));
    }

    public function tambahJurnalPembuka()
    {
        $periodes     = $this->service->getPeriodeList();
        $periodeAktif = $this->service->getPeriodeAktif();
        $akuns = Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'kode'         => $a->kode_akun,
                'nama'         => $a->nama_akun,
                'saldo_normal' => $a->saldo_normal,
            ]);

        return view('pages.akuntansi.jurnal-pembuka.create', compact('periodes', 'periodeAktif', 'akuns'));
    }

    public function simpanJurnalPembuka(StoreJurnalPembukaRequest $request)
    {
        if (!$this->service->isDetailSeimbang($request->detail)) {
            return back()->withInput()
                ->withErrors(['balance' => 'Total Debit dan Kredit harus seimbang.']);
        }

        $this->service->catatSaldoAwal($request->validated());

        return redirect()->route('dashboard.jurnal-pembuka.index')
            ->with('success', 'Jurnal pembuka berhasil disimpan.');
    }

    public function tampilkanDetailJurnalPembuka(Jurnal $jurnalPembuka)
    {
        $jurnalPembuka->load(['periode', 'detailJurnal.akun']);

        return response()->json([
            'success' => true,
            'data'    => [
                'kode_jurnal'  => $jurnalPembuka->kode_jurnal,
                'status'       => $jurnalPembuka->status,
                'tanggal'      => $jurnalPembuka->tanggal->format('d M Y'),
                'periode'      => $jurnalPembuka->periode?->nama_periode,
                'keterangan'   => $jurnalPembuka->keterangan ?? '—',
                'total_debit'  => $jurnalPembuka->total_debit,
                'total_kredit' => $jurnalPembuka->total_kredit,
                'is_balance'   => $jurnalPembuka->is_balance,
                'detail'       => $jurnalPembuka->detailJurnal->map(fn($d) => [
                    'akun'    => $d->akun->kode_akun . ' — ' . $d->akun->nama_akun,
                    'tipe'    => $d->tipe,
                    'nominal' => $d->nominal,
                ]),
            ],
        ]);
    }

    public function ubahJurnalPembuka(Jurnal $jurnalPembuka)
    {
        if ($jurnalPembuka->status === 'POSTED') {
            return redirect()->route('dashboard.jurnal-pembuka.index')
                ->with('error', 'Jurnal yang sudah diposting tidak dapat diedit.');
        }

        $jurnalPembuka->load(['periode', 'detailJurnal.akun']);
        $periodes = $this->service->getPeriodeList();
        $akuns    = Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        return view('pages.akuntansi.jurnal-pembuka.edit', compact('jurnalPembuka', 'periodes', 'akuns'));
    }

    public function perbaruiJurnalPembuka(UpdateJurnalPembukaRequest $request, Jurnal $jurnalPembuka)
    {
        if ($jurnalPembuka->status === 'POSTED') {
            return back()->with('error', 'Jurnal yang sudah diposting tidak dapat diubah.');
        }

        if ($request->submit_type === 'posting' && !$this->service->isDetailSeimbang($request->detail)) {
            return back()->withInput()
                ->withErrors(['balance' => 'Total Debit dan Kredit harus seimbang sebelum dapat diposting.']);
        }

        $this->service->perbaruiSaldoAwal($jurnalPembuka, $request->validated());

        return redirect()->route('dashboard.jurnal-pembuka.index')
            ->with('success', 'Jurnal pembuka berhasil diperbarui.');
    }

    public function posting(Jurnal $jurnalPembuka)
    {
        $result = $this->service->postingKeBukuBesar($jurnalPembuka);

        if ($result === true) {
            return response()->json([
                'success' => true,
                'message' => 'Jurnal berhasil diposting.',
            ]);
        }

        $code = str_contains($result, 'sudah diposting') ? 403 : 422;

        return response()->json([
            'success' => false,
            'message' => $result,
        ], $code);
    }

    public function hapusJurnalPembuka(Jurnal $jurnalPembuka)
    {
        $result = $this->service->hapusJurnal($jurnalPembuka);

        if ($result === true) {
            return response()->json([
                'success' => true,
                'message' => 'Jurnal pembuka berhasil dihapus.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result,
        ], 403);
    }
}
