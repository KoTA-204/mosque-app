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
        $status   = $request->input('status', '');
        $perPage  = $request->input('per_page', 10);

        $query = Jurnal::with(['detailJurnal.akun', 'periode'])
            ->where('jenis_jurnal', 'UMUM')
            ->when($bulan, function ($q) use ($bulan) {
                $q->whereYear('tanggal', substr($bulan, 0, 4))
                ->whereMonth('tanggal', substr($bulan, 5, 2));
            })
            ->when($status, fn($q) => $q->where('status', strtoupper($status)))
            ->when($search, fn($q) =>
                $q->where('keterangan', 'like', "%{$search}%")
            )
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $jurnals = $query->paginate($perPage)->withQueryString();

        $summaryBase = Jurnal::where('jenis_jurnal', 'UMUM')
            ->where('status', 'POSTED')
            ->when($bulan, function ($q) use ($bulan) {
                $q->whereYear('tanggal', substr($bulan, 0, 4))
                ->whereMonth('tanggal', substr($bulan, 5, 2));
            });

        $totalDebit  = DetailJurnal::whereIn('jurnal_id', (clone $summaryBase)->pluck('id'))
            ->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = DetailJurnal::whereIn('jurnal_id', (clone $summaryBase)->pluck('id'))
            ->where('tipe', 'KREDIT')->sum('nominal');

        $periodes = Periode::orderByDesc('tanggal_awal')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.jurnal-umum.table', compact('jurnals'))->render(),
            ]);
        }

        return view('pages.jurnal-umum.index', compact(
            'jurnals', 'totalDebit', 'totalKredit', 'periodes', 'bulan', 'search', 'status'
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

    public function post(Jurnal $jurnalUmum)
    {
        if ($jurnalUmum->status === 'POSTED') {
            return back()->with('error', 'Jurnal sudah diposting.');
        }

        $jurnalUmum->load('detailJurnal');

        if ($jurnalUmum->detailJurnal->isEmpty()) {
            return back()->with('error', 'Jurnal tidak memiliki entri.');
        }

        $totalDebit  = $jurnalUmum->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = $jurnalUmum->detailJurnal->where('tipe', 'KREDIT')->sum('nominal');

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            return back()->with('error', 'Total debit dan kredit tidak seimbang.');
        }

        $jurnalUmum->update(['status' => 'POSTED']);

        return back()->with('success', 'Jurnal berhasil diposting.');
    }

    public function bulkPost(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            $message = 'Tidak ada jurnal yang dipilih.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'alert'   => (string) view('components.jurnal.alert', [
                        'type'    => 'error',
                        'message' => $message,
                    ]),
                ]);
            }

            return back()->with('error', $message);
        }

        $jurnals = Jurnal::whereIn('id', $ids)
            ->where('jenis_jurnal', 'UMUM')
            ->where('status', 'DRAFT')
            ->with('detailJurnal')
            ->get();

        $posted = 0;
        $errors = [];

        foreach ($jurnals as $jurnal) {
            $totalDebit  = $jurnal->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');
            $totalKredit = $jurnal->detailJurnal->where('tipe', 'KREDIT')->sum('nominal');

            if (round($totalDebit, 2) !== round($totalKredit, 2)) {
                $errors[] = "Jurnal #{$jurnal->id} tidak seimbang, dilewati.";
                continue;
            }

            $jurnal->update(['status' => 'POSTED']);
            $posted++;
        }

        $message = "{$posted} jurnal berhasil diposting.";
        if (!empty($errors)) {
            $message .= ' ' . implode(' ', $errors);
        }

        $success = $posted > 0;

        if ($request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'alert'   => (string) view('components.jurnal.alert', [
                    'type'    => $success ? 'success' : 'error',
                    'message' => $message,
                ]),
            ]);
        }

        return back()->with($success ? 'success' : 'error', $message);
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