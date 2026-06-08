<?php
// app/Http/Controllers/JurnalUmumController.php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Models\Akun;
use App\Models\Periode;
use App\Models\DetailJurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalUmumController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->input('search', '');
        $bulan    = $request->input('bulan', now()->format('Y-m'));
        $perPage  = $request->input('per_page', 10);

        $query = DetailJurnal::with(['jurnal.periode', 'akun'])
            ->whereHas('jurnal', function ($q) use ($bulan) {
                $q->where('jenis_jurnal', 'UMUM');
                if ($bulan) {
                    $q->whereYear('tanggal', substr($bulan, 0, 4))
                    ->whereMonth('tanggal', substr($bulan, 5, 2));
                }
            })
            ->when($search, fn($q) =>
                $q->whereHas('akun', fn($a) =>
                    $a->where('nama_akun', 'like', "%{$search}%")
                    ->orWhere('kode_akun', 'like', "%{$search}%")
                )->orWhereHas('jurnal', fn($j) =>
                    $j->where('keterangan', 'like', "%{$search}%")
                )
            )
            ->orderByDesc(
                Jurnal::select('tanggal')
                    ->whereColumn('jurnal.id', 'detail_jurnal.jurnal_id')
                    ->limit(1)
            );

        $details = $query->paginate($perPage)->withQueryString();

        // Summary — query terpisah, bukan clone dari query yang sudah dipaginate
        $summaryBase = DetailJurnal::whereHas('jurnal', function ($q) use ($bulan) {
            $q->where('jenis_jurnal', 'UMUM')->where('status', 'POSTED');
            if ($bulan) {
                $q->whereYear('tanggal', substr($bulan, 0, 4))
                ->whereMonth('tanggal', substr($bulan, 5, 2));
            }
        });

        $totalDebit  = (clone $summaryBase)->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = (clone $summaryBase)->where('tipe', 'KREDIT')->sum('nominal');

        $akuns    = Akun::whereNotNull('parent_id')->orderBy('kode_akun')->get();
        $periodes = Periode::orderByDesc('tanggal_awal')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.jurnal-umum.table', compact('details'))->render(),
            ]);
        }

        return view('pages.jurnal-umum.index', compact(
            'details', 'totalDebit', 'totalKredit', 'akuns', 'periodes', 'bulan', 'search'
        ));
    }

    public function create()
    {
        $akuns   = Akun::with('kategoriAkun')->whereNotNull('parent_id')->orderBy('kode_akun')->get();
        $periodes = Periode::orderByDesc('tanggal_awal')->get();

        return view('pages.jurnal-umum.create', compact('akuns', 'periodes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'periode_id'         => 'required|exists:periode,id',
            'tanggal'            => 'required|date',
            'keterangan'         => 'nullable|string|max:500',
            'detail'             => 'required|array|min:2',
            'detail.*.akun_id'   => 'required|exists:akun,id',
            'detail.*.tipe'      => 'required|in:DEBIT,KREDIT',
            'detail.*.nominal'   => 'required',
        ]);

        DB::transaction(function () use ($request) {
            $jurnal = Jurnal::create([
                'periode_id'   => $request->periode_id,
                'jenis_jurnal' => 'UMUM',
                'tanggal'      => $request->tanggal,
                'keterangan'   => $request->keterangan,
                'status'       => $request->input('action') === 'post' ? 'POSTED' : 'DRAFT',
            ]);

            foreach ($request->detail as $row) {
                $nominal = (float) str_replace(['.', ','], ['', '.'], $row['nominal']);
                if (empty($row['akun_id']) || $nominal <= 0) continue;

                DetailJurnal::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id'   => $row['akun_id'],
                    'tipe'      => $row['tipe'],
                    'nominal'   => $nominal,
                ]);
            }
        });

        return redirect()->route('dashboard.jurnal-umum.index')
            ->with('success', 'Jurnal umum berhasil disimpan.');
    }

    public function show(Jurnal $jurnalUmum)
    {
        $jurnalUmum->load('periode', 'detailJurnal.akun');
        return view('pages.jurnal-umum.show', ['jurnal' => $jurnalUmum]);
    }

    public function edit(Jurnal $jurnalUmum)
    {
        if ($jurnalUmum->status === 'POSTED') {
            return redirect()->route('dashboard.jurnal-umum.index')
                ->with('error', 'Jurnal yang sudah diposting tidak dapat diedit.');
        }

        $jurnalUmum->load('detailJurnal.akun');
        $akuns   = Akun::with('kategoriAkun')->whereNotNull('parent_id')->orderBy('kode_akun')->get();
        $periodes = Periode::orderByDesc('tanggal_awal')->get();

        return view('pages.jurnal-umum.edit', [
            'jurnal'  => $jurnalUmum,
            'akuns'   => $akuns,
            'periodes' => $periodes,
        ]);
    }

    public function update(Request $request, Jurnal $jurnalUmum)
    {
        if ($jurnalUmum->status === 'POSTED') {
            return back()->with('error', 'Jurnal yang sudah diposting tidak dapat diubah.');
        }

        $request->validate([
            'periode_id'       => 'required|exists:periode,id',
            'tanggal'          => 'required|date',
            'keterangan'       => 'nullable|string|max:500',
            'detail'           => 'required|array|min:2',
            'detail.*.akun_id' => 'required|exists:akun,id',
            'detail.*.tipe'    => 'required|in:DEBIT,KREDIT',
            'detail.*.nominal' => 'required',
        ]);

        DB::transaction(function () use ($request, $jurnalUmum) {
            $jurnalUmum->update([
                'periode_id'  => $request->periode_id,
                'tanggal'     => $request->tanggal,
                'keterangan'  => $request->keterangan,
                'status'      => $request->input('action') === 'post' ? 'POSTED' : 'DRAFT',
            ]);

            $jurnalUmum->detailJurnal()->delete();

            foreach ($request->detail as $row) {
                $nominal = (float) str_replace(['.', ','], ['', '.'], $row['nominal']);
                if (empty($row['akun_id']) || $nominal <= 0) continue;

                DetailJurnal::create([
                    'jurnal_id' => $jurnalUmum->id,
                    'akun_id'   => $row['akun_id'],
                    'tipe'      => $row['tipe'],
                    'nominal'   => $nominal,
                ]);
            }
        });

        return redirect()->route('dashboard.jurnal-umum.index')
            ->with('success', 'Jurnal umum berhasil diperbarui.');
    }

    public function destroy(Jurnal $jurnalUmum)
    {
        if ($jurnalUmum->status === 'POSTED') {
            return back()->with('error', 'Jurnal yang sudah diposting tidak dapat dihapus.');
        }

        DB::transaction(function () use ($jurnalUmum) {
            $jurnalUmum->detailJurnal()->delete();
            $jurnalUmum->delete();
        });

        return redirect()->route('dashboard.jurnal-umum.index')
            ->with('success', 'Jurnal umum berhasil dihapus.');
    }
}