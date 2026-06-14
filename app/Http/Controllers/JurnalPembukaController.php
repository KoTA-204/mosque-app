<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Models\DetailJurnal;
use App\Models\Akun;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalPembukaController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->input('search', '');
        $periode = $request->input('periode', '');
        $status  = $request->input('status', '');
        $perPage = $request->input('per_page', 10);

        $query = Jurnal::with(['periode', 'detailJurnal'])
            ->where('jenis_jurnal', 'PEMBUKA')
            ->when($periode, fn($q) => $q->where('periode_id', $periode))
            ->when($status,  fn($q) => $q->where('status', $status))
            ->when($search,  fn($q) => $q->where('keterangan', 'like', "%{$search}%")
                                          ->orWhere('kode_jurnal', 'like', "%{$search}%"))
            ->orderByDesc('tanggal');

        $jurnals  = $query->paginate($perPage)->withQueryString();
        $periodes = Periode::orderByDesc('tanggal_awal')->get();

        // Stats
        $stats = [
            'total'   => Jurnal::where('jenis_jurnal', 'PEMBUKA')->count(),
            'posted'  => Jurnal::where('jenis_jurnal', 'PEMBUKA')->where('status', 'POSTED')->count(),
            'draft'   => Jurnal::where('jenis_jurnal', 'PEMBUKA')->where('status', 'DRAFT')->count(),
        ];

        return view('pages.jurnal-pembuka.index', compact(
            'jurnals', 'periodes', 'stats', 'search', 'periode', 'status', 'perPage'
        ));
    }

    public function create()
    {
        $periodes = Periode::orderByDesc('tanggal_awal')->get();
        $periodeAktif = Periode::aktif()->latest('tanggal_awal')->first();
        $akuns = Akun::with('kategoriAkun')
                ->whereNotNull('parent_id')
                ->orderBy('kode_akun')
                ->get()
                ->map(function ($a) {
                    return [
                        'id'            => $a->id,
                        'kode'          => $a->kode_akun,
                        'nama'          => $a->nama_akun,
                        'saldo_normal'  => $a->saldo_normal,
                    ];
                });

        return view('pages.jurnal-pembuka.create', compact('periodes', 'periodeAktif', 'akuns'));
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

        // Validasi keseimbangan debit = kredit
        $totalDebit = $totalKredit = 0;
        foreach ($request->detail as $row) {
            $nominal = (float) str_replace(['.', ','], ['', '.'], $row['nominal'] ?? '0');
            if ($row['tipe'] === 'DEBIT')  $totalDebit  += $nominal;
            if ($row['tipe'] === 'KREDIT') $totalKredit += $nominal;
        }

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            return back()->withInput()
                ->withErrors(['balance' => 'Total Debit dan Kredit harus seimbang.']);
        }

        DB::transaction(function () use ($request) {
            $mulai = $request->tanggal_mulai;
            $akhir = $request->tanggal_akhir;

            $periode = Periode::firstOrCreate(
                [
                    'tanggal_awal'  => $mulai,
                    'tanggal_akhir' => $akhir,
                ],
                [
                    'nama_periode' => \Carbon\Carbon::parse($mulai)->translatedFormat('F Y'),
                    'tipe'         => 'bulanan',
                    'status'       => true,
                ]
            );

            $status = $request->submit_type === 'posting' ? 'POSTED' : 'DRAFT';

            $jurnal = Jurnal::create([
                'periode_id'   => $periode->id,
                'jenis_jurnal' => 'PEMBUKA',
                'tanggal'      => $mulai,
                'keterangan'   => $request->keterangan,
                'status'       => $status,
            ]);

            foreach ($request->detail as $row) {
                $nominal = (float) str_replace(['.', ','], ['', '.'], $row['nominal'] ?? '0');
                if (empty($row['akun_id']) || $nominal <= 0) continue;

                DetailJurnal::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id'   => $row['akun_id'],
                    'tipe'      => $row['tipe'],
                    'nominal'   => $nominal,
                ]);
            }
        });

        return redirect()->route('dashboard.jurnal-pembuka.index')
            ->with('success', 'Jurnal pembuka berhasil disimpan.');
    }

    public function show(Jurnal $jurnalPembuka)
    {
        $jurnalPembuka->load(['periode', 'detailJurnal.akun']);

        return response()->json([
            'success' => true,
            'data'    => [
                'kode_jurnal' => $jurnalPembuka->kode_jurnal,
                'status'      => $jurnalPembuka->status,
                'tanggal'     => $jurnalPembuka->tanggal->format('d M Y'),
                'periode'     => $jurnalPembuka->periode?->nama_periode,
                'keterangan'  => $jurnalPembuka->keterangan ?? '—',
                'dibuat_oleh' => optional($jurnalPembuka->user)->name ?? '—',
                'total_debit' => $jurnalPembuka->total_debit,
                'total_kredit'=> $jurnalPembuka->total_kredit,
                'is_balance'  => $jurnalPembuka->is_balance,
                'detail'      => $jurnalPembuka->detailJurnal->map(fn($d) => [
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

        $periodes = Periode::orderByDesc('tanggal_awal')->get();
        $akuns    = Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        return view('pages.jurnal-pembuka.edit', compact('jurnalPembuka', 'periodes', 'akuns'));
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

        // Validasi keseimbangan
        $totalDebit = $totalKredit = 0;
        foreach ($request->detail as $row) {
            $nominal = (float) str_replace(['.', ','], ['', '.'], $row['nominal'] ?? '0');
            if ($row['tipe'] === 'DEBIT')  $totalDebit  += $nominal;
            if ($row['tipe'] === 'KREDIT') $totalKredit += $nominal;
        }

        if ($request->submit_type === 'posting' && round($totalDebit, 2) !== round($totalKredit, 2)) {
            return back()
                ->withInput()
                ->withErrors(['balance' => 'Total Debit dan Kredit harus seimbang sebelum dapat diposting.']);
        }

        DB::transaction(function () use ($request, $jurnalPembuka) {
            $status = $request->submit_type === 'posting' ? 'POSTED' : 'DRAFT';

            $jurnalPembuka->update([
                'periode_id'  => $request->periode_id,
                'tanggal'     => $request->tanggal_mulai,
                'keterangan'  => $request->keterangan,
                'status'      => $status,
            ]);

            $jurnalPembuka->detailJurnal()->delete();

            foreach ($request->detail as $row) {
                $nominal = (float) str_replace(['.', ','], ['', '.'], $row['nominal'] ?? '0');
                if (empty($row['akun_id']) || $nominal <= 0) continue;

                DetailJurnal::create([
                    'jurnal_id' => $jurnalPembuka->id,
                    'akun_id'   => $row['akun_id'],
                    'tipe'      => $row['tipe'],
                    'nominal'   => $nominal,
                ]);
            }
        });

        return redirect()->route('dashboard.jurnal-pembuka.index')
            ->with('success', 'Jurnal pembuka berhasil diperbarui.');
    }

    public function posting(Jurnal $jurnalPembuka)
    {
        if ($jurnalPembuka->status === 'POSTED') {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal sudah diposting.',
            ], 403);
        }

        if (!$jurnalPembuka->is_balance) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal tidak seimbang, tidak dapat diposting.',
            ], 422);
        }

        $jurnalPembuka->update(['status' => 'POSTED']);

        return response()->json([
            'success' => true,
            'message' => 'Jurnal berhasil diposting.',
        ]);
    }

    public function destroy(Jurnal $jurnalPembuka)
    {
        if ($jurnalPembuka->status === 'POSTED') {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal yang sudah diposting tidak dapat dihapus.',
            ], 403);
        }

        DB::transaction(function () use ($jurnalPembuka) {
            $jurnalPembuka->detailJurnal()->delete();
            $jurnalPembuka->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Jurnal pembuka berhasil dihapus.',
        ]);
    }
}