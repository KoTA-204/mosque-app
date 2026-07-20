<?php

namespace App\Http\Controllers\Aset;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAsetRequest;
use App\Http\Requests\UpdateAsetRequest;
use App\Models\Aset;
use App\Models\Jurnal;
use App\Services\Aset\AsetService;
use Illuminate\Http\Request;

class AsetController extends Controller
{
    // inject service
    public function __construct(private readonly AsetService $asetService)
    {
    }

    // daftar aset + statistik + filter
    public function tampilkanDaftarAset(Request $request)
    {
        // mode statistik saja
        if ($request->boolean('stats_only')) {
            return response()->json(['stats' => $this->hitungStatistikAset()]);
        }

        $perPage = (int) $request->get('per_page', 10);
        $asets   = Aset::saring($request->all())
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $stats = $this->hitungStatistikAset();

        // Setelah jurnal pembuka di-POSTING, saldo awal aset dianggap final.
        // Penambahan aset selanjutnya HARUS lewat pencatatan transaksi
        // (pembelian aset), sehingga tombol "Tambah Aset" manual dinonaktifkan.
        $asetTerkunci = Jurnal::pembuka()->posted()->exists();

        // kirim potongan tabel untuk request ajax
        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.aset.table', compact('asets'))->render(),
            ]);
        }

        return view('pages.aset.index', compact('asets', 'stats', 'asetTerkunci'));
    }

    // form create
    public function tampilkanFormTambahAset()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.aset.create')->render(),
            ]);
        }
        return redirect()->route('dashboard.aset.index');
    }

    // simpan aset baru
    public function simpanAsetBaru(StoreAsetRequest $request)
    {
        if (! $request->boolean('abaikan_duplikat')) {
            $duplikat = $this->cariAsetDuplikat($request->validated());

            if (! empty($duplikat)) {
                $msg = 'Sepertinya aset ini sudah pernah dicatat (nama dan tanggal perolehan sama). Pastikan ini bukan input ganda.';

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'           => false,
                        'duplicate_warning' => true,
                        'message'           => $msg,
                        'matches'           => $duplikat,
                    ]);
                }

                return redirect()->back()->withInput()->with('warning', $msg);
            }
        }

        $this->asetService->simpanAset(
            $request->validated(),
            $request->file('dokumen_pendukung'),
        );

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Aset berhasil ditambahkan.']);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', 'Aset berhasil ditambahkan.');
    }

    // detail aset
    public function tampilkanDetailAset(Aset $aset)
    {
        $aset->load('jurnalPenyesuaian.periode');

        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.aset.show', compact('aset'))->render(),
            ]);
        }
        return redirect()->route('dashboard.aset.index');
    }

    // form edit
    public function tampilkanFormUbahAset(Aset $aset)
    {
        // Aset tidak aktif tidak dapat diedit
        if ($aset->status_aset === 'TIDAK AKTIF') {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Aset tidak aktif tidak dapat diedit.'], 422);
            }
            return redirect()->route('dashboard.aset.index')->with('error', 'Aset tidak aktif tidak dapat diedit.');
        }

        $keuanganTerkunci = $aset->jurnalPenyesuaian()->exists();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.aset.edit', compact('aset', 'keuanganTerkunci'))->render(),
            ]);
        }
        return redirect()->route('dashboard.aset.index');
    }

    // update aset
    public function perbaruiAset(UpdateAsetRequest $request, Aset $aset)
    {
        // Blokir update aset tidak aktif
        if ($aset->status_aset === 'TIDAK AKTIF') {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Aset tidak aktif tidak dapat diperbarui.'], 422);
            }
            return redirect()->back()->with('error', 'Aset tidak aktif tidak dapat diperbarui.');
        }

        if (! $request->boolean('abaikan_duplikat')) {
            $duplikat = $this->cariAsetDuplikat($request->validated(), $aset->id);

            if (! empty($duplikat)) {
                $msg = 'Sepertinya aset ini sudah pernah dicatat (nama dan tanggal perolehan sama). Pastikan ini bukan input ganda.';

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'           => false,
                        'duplicate_warning' => true,
                        'message'           => $msg,
                        'matches'           => $duplikat,
                    ]);
                }

                return redirect()->back()->withInput()->with('warning', $msg);
            }
        }

        $this->asetService->perbaruiAset(
            $aset,
            $request->validated(),
            $request->file('dokumen_pendukung'),
            $request->boolean('disusutkan'),
        );

        if ($request->ajax()) {
            $message = 'Aset berhasil diperbarui.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'alert'   => (string) view('components.jurnal.alert', [
                    'type'    => 'success',
                    'message' => $message,
                ]),
            ]);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', 'Aset berhasil diperbarui.');
    }

    // aktif / nonaktifkan aset
    public function ubahStatusAset(Request $request, Aset $aset)
    {
        try {
            $newStatus = $this->asetService->ubahStatusAset(
                $aset,
                $request->input('alasan_nonaktif'),
                $request->input('catatan_nonaktif'),
                $request->input('jenis_pelepasan'),
            );
        } catch (\InvalidArgumentException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'status'  => $newStatus,
                'message' => "Aset berhasil diubah ke {$newStatus}.",
            ]);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', "Aset berhasil diubah ke {$newStatus}.");
    }

    // hapus aset
    public function hapusAset(Aset $aset)
    {
        try {
            $this->asetService->hapusAset($aset);
        } catch (\InvalidArgumentException $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Aset berhasil dihapus.']);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', 'Aset berhasil dihapus.');
    }

    // hitung statistik kartu
    private function hitungStatistikAset(): array
    {
        return [
            'total'       => Aset::count(),
            'aktif'       => Aset::where('status_aset', 'AKTIF')->count(),
            'tidak_aktif' => Aset::where('status_aset', 'TIDAK AKTIF')->count(),
        ];
    }

    /**
     * Normalisasi nama aset agar perbandingan tahan terhadap beda huruf besar/kecil,
     * spasi berlebih, dan tanda baca.
     */
    private function normalisasiNamaAset(string $nama): string
    {
        $n = mb_strtolower(trim($nama));
        $n = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $n);
        $n = preg_replace('/\s+/', ' ', $n);

        return trim($n ?? '');
    }

    /**
     * Deteksi kemungkinan INPUT GANDA aset (bukan sekadar nama mirip): nama sama persis
     * setelah normalisasi DAN tanggal perolehan sama.
     * Nilai tidak lagi diperhitungkan; duplikasi nama di tanggal berbeda tetap dibiarkan
     * karena aset identik bisa sah berjumlah banyak.
     */
    private function cariAsetDuplikat(array $data, ?int $excludeId = null): array
    {
        $namaBaru = $this->normalisasiNamaAset((string) ($data['nama_aset'] ?? ''));
        if ($namaBaru === '') {
            return [];
        }

        $tanggal = \Illuminate\Support\Carbon::parse($data['tanggal_perolehan'])->toDateString();

        $kandidat = Aset::query()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereDate('tanggal_perolehan', $tanggal)
            ->get(['id', 'kode_aset', 'nama_aset', 'tanggal_perolehan', 'nilai_tercatat']);

        $duplikat = [];

        foreach ($kandidat as $a) {
            if ($this->normalisasiNamaAset((string) $a->nama_aset) !== $namaBaru) {
                continue;
            }

            $duplikat[] = [
                'id'                => $a->id,
                'kode_aset'         => $a->kode_aset,
                'nama_aset'         => $a->nama_aset,
                'tanggal_perolehan' => \Illuminate\Support\Carbon::parse($a->tanggal_perolehan)->format('d M Y'),
                'nilai_tercatat'    => (float) $a->nilai_tercatat,
            ];
        }

        return $duplikat;
    }
}
