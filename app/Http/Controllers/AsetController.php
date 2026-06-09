<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AsetController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('stats_only')) {
            return response()->json([
                'stats' => [
                    'total'       => Aset::count(),
                    'aktif'       => Aset::where('status_aset', 'AKTIF')->count(),
                    'tidak_aktif' => Aset::where('status_aset', 'TIDAK AKTIF')->count(),
                ]
            ]);
        }

        $query = Aset::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_aset',    'like', "%{$search}%")
                ->orWhere('kode_aset',  'like', "%{$search}%")
                ->orWhere('lokasi_aset','like', "%{$search}%");
            });
        }

        if ($tahun = $request->get('tahun')) {
            $query->where('kode_aset', 'like', "ASET-{$tahun}-%");
        }

        if ($lokasi = $request->get('lokasi')) {
            $query->where('lokasi_aset', $lokasi);
        }

        if ($sumber = $request->get('sumber')) {
            $query->where('sumber_perolehan', $sumber);
        }

        if ($status = $request->get('status')) {
            $query->where('status_aset', strtoupper($status));
        }

        if ($kondisi = $request->get('kondisi')) {
            $query->where('kondisi_aset', $kondisi);
        }

        $perPage = (int) $request->get('per_page', 10);
        $asets   = $query->latest()->paginate($perPage)->withQueryString();

        $stats = [
            'total'       => Aset::count(),
            'aktif'       => Aset::where('status_aset', 'AKTIF')->count(),
            'tidak_aktif' => Aset::where('status_aset', 'TIDAK AKTIF')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.aset.table', compact('asets'))->render(),
            ]);
        }

        return view('pages.aset.index', compact('asets', 'stats'));
    }

    public function create()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.aset.create')->render(),
            ]);
        }
        return redirect()->route('dashboard.aset.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_aset'                => 'required|string|max:255',
            'kondisi_aset'             => 'required|in:BAIK,RUSAK RINGAN,RUSAK BERAT',
            'lokasi_aset'              => 'required|string|max:255',
            'sumber_perolehan'         => 'required|string',
            'tanggal_perolehan'        => 'required|date',
            'nilai_tercatat'           => 'required|numeric|min:0',
            'nama_pemberi'             => 'nullable|string|max:255',
            'jumlah_unit'              => 'nullable|integer|min:1',
            'dokumen_pendukung'        => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:5120',
            'tanggal_mulai_penyusutan' => 'nullable|date',
            'umur_manfaat'             => 'nullable|integer|min:1',
            'keterangan'               => 'nullable|string',
        ]);

        $dokumenPath = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $dokumenPath = $request->file('dokumen_pendukung')
                ->store('aset/dokumen', 'public');
        }

        Aset::create([
            'kode_aset'                => Aset::generateKode($request->tanggal_perolehan),
            'nama_aset'                => $request->nama_aset,
            'sumber_perolehan'         => $request->sumber_perolehan,
            'tanggal_perolehan'        => $request->tanggal_perolehan,
            'nilai_tercatat'           => $request->nilai_tercatat,
            'kondisi_aset'             => $request->kondisi_aset,
            'lokasi_aset'              => $request->lokasi_aset,
            'nama_pemberi'             => $request->nama_pemberi,
            'jumlah_unit'              => $request->jumlah_unit ?? 1,
            'dokumen_pendukung'        => $dokumenPath,
            'tanggal_mulai_penyusutan' => $request->tanggal_mulai_penyusutan,
            'umur_manfaat'             => $request->umur_manfaat,
            'keterangan'               => $request->keterangan,
            'status_aset'              => 'AKTIF',
            'nilai_buku'               => $request->nilai_tercatat,
            'akumulasi_penyusutan'     => 0,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Aset berhasil ditambahkan.']);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', 'Aset berhasil ditambahkan.');
    }

    public function show(Aset $aset)
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.aset.show', compact('aset'))->render(),
            ]);
        }
        return redirect()->route('dashboard.aset.index');
    }

    public function edit(Aset $aset)
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.aset.edit', compact('aset'))->render(),
            ]);
        }
        return redirect()->route('dashboard.aset.index');
    }

    public function update(Request $request, Aset $aset)
    {
        $request->validate([
            'nama_aset'                => 'required|string|max:255',
            'kondisi_aset'             => 'required|in:BAIK,RUSAK RINGAN,RUSAK BERAT',
            'lokasi_aset'              => 'required|string|max:255',
            'sumber_perolehan'         => 'required|string',
            'tanggal_perolehan'        => 'required|date',
            'nilai_tercatat'           => 'required|numeric|min:0',
            'nama_pemberi'             => 'nullable|string|max:255',
            'jumlah_unit'              => 'nullable|integer|min:1',
            'dokumen_pendukung'        => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:5120',
            'tanggal_mulai_penyusutan' => 'nullable|date',
            'umur_manfaat'             => 'nullable|integer|min:1',
            'keterangan'               => 'nullable|string',
            'status_aset'              => 'required|in:AKTIF,TIDAK AKTIF',
        ]);

        $dokumenPath = $aset->dokumen_pendukung;
        if ($request->hasFile('dokumen_pendukung')) {
            if ($dokumenPath) Storage::disk('public')->delete($dokumenPath);
            $dokumenPath = $request->file('dokumen_pendukung')
                ->store('aset/dokumen', 'public');
        }

        // Jika checkbox penyusutan tidak dicentang, kosongkan field terkait
        $disusutkan = $request->boolean('disusutkan');

        $aset->update([
            'nama_aset'                => $request->nama_aset,
            'sumber_perolehan'         => $request->sumber_perolehan,
            'tanggal_perolehan'        => $request->tanggal_perolehan,
            'nilai_tercatat'           => $request->nilai_tercatat,
            'kondisi_aset'             => $request->kondisi_aset,
            'lokasi_aset'              => $request->lokasi_aset,
            'nama_pemberi'             => $request->nama_pemberi,
            'jumlah_unit'              => $request->jumlah_unit ?? 1,
            'dokumen_pendukung'        => $dokumenPath,
            'tanggal_mulai_penyusutan' => $disusutkan ? $request->tanggal_mulai_penyusutan : null,
            'umur_manfaat'             => $disusutkan ? $request->umur_manfaat : null,
            'keterangan'               => $request->keterangan,
            'status_aset'              => $request->status_aset,
            'nilai_buku'               => $disusutkan ? $aset->nilai_buku_real_time : (float) $request->nilai_tercatat,
            'akumulasi_penyusutan'     => $disusutkan ? $aset->akumulasi_real_time : 0,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Aset berhasil diperbarui.']);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', 'Aset berhasil diperbarui.');
    }

    public function toggleStatus(Aset $aset)
    {
        $newStatus = $aset->status_aset === 'AKTIF' ? 'TIDAK AKTIF' : 'AKTIF';

        $updateData = ['status_aset' => $newStatus];

        // Saat dinonaktifkan, snapshot nilai penyusutan saat ini
        if ($newStatus === 'TIDAK AKTIF' && $aset->umur_manfaat) {
            $updateData['akumulasi_penyusutan'] = $aset->akumulasi_real_time;
            $updateData['nilai_buku']           = $aset->nilai_buku_real_time;
        }

        // Saat diaktifkan kembali, reset ke hitungan real-time
        // (biarkan accessor yang hitung ulang, kosongkan snapshot)
        if ($newStatus === 'AKTIF' && $aset->umur_manfaat) {
            $updateData['akumulasi_penyusutan'] = 0;
            $updateData['nilai_buku']           = $aset->nilai_buku_real_time;
        }

        $aset->update($updateData);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'status'  => $newStatus,
                'message' => "Aset berhasil diubah ke {$newStatus}.",
            ]);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', "Aset berhasil diubah ke {$newStatus}.");
    }

    public function destroy(Aset $aset)
    {
        if ($aset->dokumen_pendukung) {
            Storage::disk('public')->delete($aset->dokumen_pendukung);
        }
        $aset->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Aset berhasil dihapus.']);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', 'Aset berhasil dihapus.');
    }
}