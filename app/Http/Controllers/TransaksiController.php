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
use App\Models\Periode;
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
        $periodeAktif = Periode::aktif()->first();

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
            });

        // Filter periode aktif (prioritas di atas filter tanggal manual)
        if ($request->boolean('periode_aktif')) {
            $query->periodeAktif();
        } elseif ($request->filled('dari') && $request->filled('sampai')) {
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

        $statsQuery = $query->toBase()->reorder();

        $stats = [
            'total'              => (clone $statsQuery)->count(),
            'jumlah_pemasukan'   => (clone $statsQuery)->where('jenis_transaksi', 'PEMASUKAN')->sum('jumlah'),
            'jumlah_pengeluaran' => (clone $statsQuery)->where('jenis_transaksi', 'PENGELUARAN')->sum('jumlah'),
            'count_pemasukan'    => (clone $statsQuery)->where('jenis_transaksi', 'PEMASUKAN')->count(),
            'count_pengeluaran'  => (clone $statsQuery)->where('jenis_transaksi', 'PENGELUARAN')->count(),
        ];

        $perPage    = (int) $request->input('per_page', 10);
        $transaksis = $query
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        // Dropdown data
        $kategoris = KategoriTransaksi::orderBy('nama_kategori')->get();
        $akuns = Akun::whereDoesntHave('children')->orderBy('kode_akun')->get();
        $dompets   = Dompet::orderBy('nama_dompet')->get();
        $kegiatans = Kegiatan::orderBy('nama_kegiatan')->get();

        return view('pages.transaksi.index', compact(
            'transaksis', 'stats', 'kategoris', 'akuns', 'dompets', 'kegiatans', 'periodeAktif'
        ));
    }

    public function store(StoreTransaksiRequest $request)
    {
        try {
            $force     = $request->boolean('force');
            $transaksi = $this->transaksiService->store($request, $force);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
                'data'    => $transaksi,
            ]);
        } catch (\Throwable $e) {
            if (str_starts_with($e->getMessage(), 'DUPLIKAT_WARNING:')) {
                $raw     = str_replace('DUPLIKAT_WARNING:', '', $e->getMessage());
                $detail  = json_decode($raw, true);

                return response()->json([
                    'success' => false,
                    'type'    => 'duplikat_warning',
                    'detail'  => $detail,
                ], 409);
            }

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

        $jurnalUmum = $transaksi->jurnal->firstWhere('jenis_jurnal', 'UMUM');

        $entries = $jurnalUmum?->detailJurnal
            ->map(fn($d) => [
                'akun_id'   => $d->akun_id,
                'tipe'      => $d->tipe,
                'nominal'   => (float) $d->nominal,
                'akun_nama' => $d->akun?->nama_akun,
            ])
            ->values()
            ->all() ?? [];

        return response()->json([
            'success' => true,
            'data'    => array_merge($transaksi->toArray(), [
                'jurnal_entries' => $entries,
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

            if (!empty($result['errors']) && empty($result['rows'])) {
                return response()->json([
                    'success' => false,
                    'type'    => 'parse_error',
                    'message' => implode(' ', $result['errors']),
                ], 422);
            }

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

        $akuns = Akun::whereDoesntHave('children')->orderBy('kode_akun')->get();
        $dompets = Dompet::orderBy('nama_dompet')->get();

        return view('pages.transaksi.import-review', compact(
            'rows', 'meta', 'stats', 'warnings', 'key', 'akuns', 'dompets'
        ));
    }

    public function importSimpan(Request $request)
    {
        $request->validate([
            'import_key'                      => 'required|string',
            'klasifikasi'                     => 'required|array',
            'klasifikasi.*.no_referensi'      => 'required|string',
            'klasifikasi.*.entries'           => 'required|array|min:2',
            'klasifikasi.*.entries.*.akun_id' => 'required|exists:akun,id',
            'klasifikasi.*.entries.*.tipe'    => 'required|in:DEBIT,KREDIT',
            'klasifikasi.*.entries.*.nominal' => 'required|numeric|min:1',
        ], [
            'klasifikasi.*.entries.required'           => 'Setiap baris wajib memiliki minimal 1 akun debit dan 1 akun kredit.',
            'klasifikasi.*.entries.*.akun_id.required' => 'Akun wajib dipilih pada setiap baris jurnal.',
        ]);

        $key  = $request->import_key;
        $data = session($key);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi import tidak ditemukan atau sudah kedaluwarsa.',
            ], 422);
        }

        // Validasi balance per baris sebelum diproses
        $errorBalance = [];
        foreach ($request->klasifikasi as $row) {
            $entries = $row['entries'] ?? [];
            $debit   = collect($entries)->where('tipe', 'DEBIT')->sum('nominal');
            $kredit  = collect($entries)->where('tipe', 'KREDIT')->sum('nominal');

            if (abs($debit - $kredit) > 0.5) {
                $errorBalance[] = "Baris {$row['no_referensi']}: total debit (Rp" . number_format($debit, 0, ',', '.') .
                    ") tidak sama dengan total kredit (Rp" . number_format($kredit, 0, ',', '.') . ").";
            }
        }

        if (!empty($errorBalance)) {
            return response()->json([
                'success' => false,
                'message' => implode(' ', $errorBalance),
            ], 422);
        }

        try {
            $result = $this->transaksiService->simpanImport($data, $request->klasifikasi);

            session()->forget($key);
            session()->save();

            $pesanTambahan = '';
            if (!empty($result['gagalPeriode'])) {
                $bulanList = [];
                foreach ($result['gagalPeriode'] as $tanggal => $jumlah) {
                    $bulanList[] = \Carbon\Carbon::parse($tanggal)->translatedFormat('F Y') . " ({$jumlah} transaksi)";
                }
                $pesanTambahan .= ' Beberapa transaksi dilewati karena periode belum aktif: ' . implode(', ', array_unique($bulanList)) . '. Aktifkan periode tersebut lalu impor ulang.';
            }
            if (!empty($result['gagalBalance'])) {
                $pesanTambahan .= ' ' . count($result['gagalBalance']) . ' transaksi dilewati karena jurnal tidak balance (total debit ≠ total kredit).';
            }

            return response()->json([
                'success'   => true,
                'type'      => 'import_success',
                'tersimpan' => $result['tersimpan'],
                'dilewati'  => $result['dilewati'],
                'duplikat'  => $result['duplikat'],
                'total'     => $result['total'],
                'periode'   => $data['meta']['periode'] ?? '-',
                'pesan_tambahan' => $pesanTambahan,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }
}