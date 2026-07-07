<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Models\Jurnal;
use App\Models\Akun;
use App\Models\Periode;
use App\Services\Akuntansi\JurnalPembukaService;
use Illuminate\Http\Request;

class JurnalPembukaController extends Controller
{
    public function __construct(private JurnalPembukaService $jurnal) {}

    public function index(Request $request)
    {
        $filter = [
            'search'   => $request->input('search', ''),
            'periode'  => $request->input('periode', ''),
            'status'   => $request->input('status', ''),
            'per_page' => $request->input('per_page', 10),
        ];

        $jurnals  = $this->jurnal->daftar($filter);
        $periodes = $this->jurnal->getPeriodeList();
        $stats    = $this->jurnal->stats();

        $search  = $filter['search'];
        $periode = $filter['periode'];
        $status  = $filter['status'];
        $perPage = $filter['per_page'];
        return view('pages.akuntansi.jurnal-pembuka.index', compact(
            'jurnals', 'periodes', 'stats', 'search', 'periode', 'status', 'perPage'
        ));
    }

    public function create()
    {
        $periodes     = $this->jurnal->getPeriodeList();
        $periodeAktif = Periode::aktif()->latest('tanggal_awal')->first();
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

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_mulai'      => 'required|date',
            'tanggal_akhir'      => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'         => 'nullable|string|max:500',
            'submit_type'        => 'required|in:draft,posting',
            'detail'             => 'required|array|min:2',
            'detail.*.akun_id'   => 'required|exists:akun,id',
            'detail.*.tipe'      => 'required|in:DEBIT,KREDIT',
            'detail.*.nominal'   => 'required',
        ]);

        if (!$this->jurnal->detailSeimbang($request->detail)) {
            return back()->withInput()
                ->withErrors(['balance' => 'Total Debit dan Kredit harus seimbang.']);
        }

        $this->jurnal->simpan($request->all());

        return redirect()->route('dashboard.jurnal-pembuka.index')
            ->with('success', 'Jurnal pembuka berhasil disimpan.');
    }

    public function show(Jurnal $jurnalPembuka)
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
                'dibuat_oleh'  => optional($jurnalPembuka->user)->name ?? '—',
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

    public function edit(Jurnal $jurnalPembuka)
    {
        if ($jurnalPembuka->status === 'POSTED') {
            return redirect()->route('dashboard.jurnal-pembuka.index')
                ->with('error', 'Jurnal yang sudah diposting tidak dapat diedit.');
        }
        $jurnalPembuka->load(['periode', 'detailJurnal.akun']);
        $periodes = $this->jurnal->getPeriodeList();
        $akuns    = Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();
        return view('pages.akuntansi.jurnal-pembuka.edit', compact('jurnalPembuka', 'periodes', 'akuns'));
    }

    public function update(Request $request, Jurnal $jurnalPembuka)
    {
        if ($jurnalPembuka->status === 'POSTED') {
            return back()->with('error', 'Jurnal yang sudah diposting tidak dapat diubah.');
        }

        $request->validate([
            'periode_id'         => 'required|exists:periode,id',
            'tanggal_mulai'      => 'required|date',
            'tanggal_akhir'      => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'         => 'nullable|string|max:500',
            'submit_type'        => 'required|in:draft,posting',
            'detail'             => 'required|array|min:2',
            'detail.*.akun_id'   => 'required|exists:akun,id',
            'detail.*.tipe'      => 'required|in:DEBIT,KREDIT',
            'detail.*.nominal'   => 'required',
        ]);

        if ($request->submit_type === 'posting' && !$this->jurnal->detailSeimbang($request->detail)) {
            return back()->withInput()
                ->withErrors(['balance' => 'Total Debit dan Kredit harus seimbang sebelum dapat diposting.']);
        }

        $this->jurnal->perbarui($jurnalPembuka, $request->all());

        return redirect()->route('dashboard.jurnal-pembuka.index')
            ->with('success', 'Jurnal pembuka berhasil diperbarui.');
    }

    public function posting(Jurnal $jurnalPembuka)
    {
        $result = $this->jurnal->post($jurnalPembuka); // dari abstract
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

    public function destroy(Jurnal $jurnalPembuka)
    {
        $result = $this->jurnal->delete($jurnalPembuka); // dari abstract
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