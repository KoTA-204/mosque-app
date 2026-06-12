<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use App\Http\Requests\ImportTransaksiRequest;
use App\Models\Transaksi;
use App\Models\BuktiTransaksi;
use App\Models\Jurnal;
use App\Models\Akun;
use App\Models\Dompet;
use App\Models\Kegiatan;
use App\Models\KategoriTransaksi;
use App\Services\TransaksiService;
use App\Services\MutasiBankParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransaksiController extends Controller
{
    public function __construct(
        private readonly TransaksiService $transaksiService,
        private readonly MutasiBankParserService $parserService,
    ) {}

    public function index(Request $request)
    {
        $query = Transaksi::with([
                'kategoriTransaksi',
                'dompet',
                'buktiTransaksi',
                'jurnal.detailJurnal.akun',
                'kencleng',
                'aset',
            ])
            ->where(function ($q) {
                $q->whereNull('status_approval')
                ->orWhere('status_approval', 'APPROVED');
            })
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id');

        // Filter
        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal_transaksi', [
                $request->dari,
                $request->sampai,
            ]);
        } elseif ($request->filled('dari')) {
            $query->whereDate('tanggal_transaksi', $request->dari);
        }
        if ($request->filled('kategori_id')) {
            $query->where('kategori_transaksi_id', $request->kategori_id);
        }
        if ($request->filled('akun_id')) {
            $query->whereHas('jurnal.detailJurnal', fn($q) =>
                $q->where('akun_id', $request->akun_id)
            );
        }
        if ($request->filled('search')) {
            $query->where('deskripsi', 'like', '%' . $request->search . '%');
        }

        $perPage     = (int) $request->input('per_page', 10);
        $transaksis  = $query->paginate($perPage)->withQueryString();

        $statsQuery = Transaksi::where(function ($q) {
            $q->whereNull('status_approval')
            ->orWhere('status_approval', 'APPROVED');
        });

        // Stats
        $stats = [
            'total'       => (clone $statsQuery)->count(),
            'pemasukan'   => (clone $statsQuery)->where('jenis_transaksi', 'PEMASUKAN')->count(),
            'pengeluaran' => (clone $statsQuery)->where('jenis_transaksi', 'PENGELUARAN')->count(),
        ];

        // Dropdown data
        $kategoris = KategoriTransaksi::orderBy('nama_kategori')->get();
        $akuns     = Akun::orderBy('kode_akun')->get();
        $dompets   = Dompet::orderBy('nama_dompet')->get();
        $kegiatans = Kegiatan::orderBy('nama_kegiatan')->get();

        return view('pages.transaksi.index', compact(
            'transaksis', 'stats', 'kategoris', 'akuns', 'dompets', 'kegiatans'
        ));
    }

    public function store(StoreTransaksiRequest $request)
    {
        try {
            $transaksi = $this->transaksiService->store($request);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
                'data'    => $transaksi,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Transaksi $transaksi)
    {
        $transaksi->load([
            'kategoriTransaksi',
            'dompet',
            'kegiatan',
            'buktiTransaksi',
            'jurnal.detailJurnal.akun',
            'aset',
        ]);

        $detailJurnals = $transaksi->jurnal
            ->firstWhere('jenis_jurnal', 'UMUM')
            ?->detailJurnal;

        $debit  = $detailJurnals?->firstWhere('tipe', 'DEBIT');   // ← fix: tipe bukan posisi
        $kredit = $detailJurnals?->firstWhere('tipe', 'KREDIT');  // ← fix: tipe bukan posisi

        return response()->json([
            'success' => true,
            'data'    => array_merge($transaksi->toArray(), [
                'akun_debit_id'    => $debit?->akun_id,
                'akun_kredit_id'   => $kredit?->akun_id,
                'akun_debit_nama'  => $debit?->akun?->nama_akun,
                'akun_kredit_nama' => $kredit?->akun?->nama_akun,
            ]),
        ]);
    }

    public function update(UpdateTransaksiRequest $request, Transaksi $transaksi)
    {
        $jurnal = $transaksi->jurnal()->where('jenis_jurnal', 'UMUM')->first();
        $isUnmapped = $transaksi->status_approval === 'APPROVED' 
                && $transaksi->status_jurnal === 'UNMAPPED';

        if (!$isUnmapped && $jurnal?->status !== 'DRAFT') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ini tidak dapat diubah karena jurnal sudah diposting.',
            ], 403);
        }

        try {
            $transaksi = $this->transaksiService->update($request, $transaksi);
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diperbarui.',
                'data'    => $transaksi,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Transaksi $transaksi)
    {
        $jurnal = $transaksi->jurnal()->where('jenis_jurnal', 'UMUM')->first();
        $isUnmapped = $transaksi->status_approval === 'APPROVED'
                && $transaksi->status_jurnal === 'UNMAPPED';

        if (!$isUnmapped && $jurnal?->status !== 'DRAFT') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ini tidak dapat dihapus karena jurnal sudah diposting.',
            ], 403);
        }

        try {
            $this->transaksiService->destroy($transaksi);
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyBukti(BuktiTransaksi $bukti)
    {
        Storage::disk('public')->delete($bukti->path_file);
        $bukti->delete();

        return response()->json(['success' => true, 'message' => 'Bukti dihapus.']);
    }

    public function import(ImportTransaksiRequest $request)
    {
        try {
            $result = $this->parserService->parse(
                file: $request->file('file'),
                bank: $request->bank,
            );

            // Parsing gagal
            if (!empty($result['errors']) && empty($result['rows'])) {
                return response()->json([
                    'success' => false,
                    'type'    => 'parse_error',
                    'message' => implode(' ', $result['errors']),
                ], 422);
            }

            // Simpan hasil parsing ke session
            $key = 'import_' . Auth::id() . '_' . time();
            session([$key => [
                'bank'            => $request->bank,
                'dompet_id'       => $request->dompet_id,
                'jenis_transaksi' => $request->jenis_transaksi,
                'rows'            => $result['rows'],
                'meta'            => $result['meta'],
                'warnings'        => $result['errors'],
            ]]);

            $total    = count($result['rows']);
            $duplikat = count(array_filter($result['rows'], fn($r) => $r['is_duplikat']));

            return response()->json([
                'success'    => true,
                'type'       => 'parse_success',
                'import_key' => $key,
                'redirect'   => route('dashboard.transaksi.import.review', ['key' => $key]),
                'stats'      => [
                    'total'    => $total,
                    'duplikat' => $duplikat,
                    'bersih'   => $total - $duplikat,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'type'    => 'parse_error',
                'message' => 'Gagal memproses file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function importReview(Request $request)
    {
        $key  = $request->query('key');
        $data = session($key);

        if (!$data) {
            return redirect()->route('dashboard.transaksi.index')
                ->with('error', 'Sesi import tidak ditemukan atau sudah kedaluwarsa.');
        }

        $rows     = $data['rows'];
        $meta     = $data['meta'];
        $warnings = $data['warnings'] ?? [];

        $stats = [
            'total'    => count($rows),
            'duplikat' => count(array_filter($rows, fn($r) => $r['is_duplikat'])),
            'bersih'   => count(array_filter($rows, fn($r) => !$r['is_duplikat'])),
        ];

        $akuns = Akun::orderBy('kode_akun')->get();
        $dompets = Dompet::orderBy('nama_dompet')->get();

        return view('pages.transaksi.import-review', compact(
            'rows', 'meta', 'stats', 'warnings', 'key', 'akuns', 'dompets'
        ));
    }

    public function importSimpan(Request $request)
    {
        $request->validate([
            'import_key'                    => 'required|string',
            'klasifikasi'                   => 'required|array',
            'klasifikasi.*.no_referensi'    => 'required|string',
            'klasifikasi.*.dompet_id'       => 'required|exists:dompet,id',
            'klasifikasi.*.akun_debit_id'   => 'required|exists:akun,id',
            'klasifikasi.*.akun_kredit_id'  => 'required|exists:akun,id',
            'klasifikasi.*.skip'            => 'nullable|boolean',
        ], [
            'klasifikasi.*.dompet_id.required'      => 'Dompet wajib dipilih pada setiap baris.',
            'klasifikasi.*.akun_debit_id.required'  => 'Akun debit wajib dipilih pada setiap baris.',
            'klasifikasi.*.akun_kredit_id.required' => 'Akun kredit wajib dipilih pada setiap baris.',
        ]);

        $key  = $request->import_key;
        $data = session($key);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi import tidak ditemukan atau sudah kedaluwarsa.',
            ], 422);
        }

        try {
            $result = $this->transaksiService->simpanImport($data, $request->klasifikasi);

            // Bersihkan session
            session()->forget($key);

            return response()->json([
                'success'   => true,
                'type'      => 'import_success',
                'tersimpan' => $result['tersimpan'],
                'dilewati'  => $result['dilewati'],
                'duplikat'  => $result['duplikat'],
                'total'     => $result['total'],
                'periode'   => $data['meta']['periode'] ?? '-',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }
}