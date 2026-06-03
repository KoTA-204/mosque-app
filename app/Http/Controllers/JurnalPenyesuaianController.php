<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalPenyesuaianRequest;
use App\Models\Akun;
use App\Models\Aset;
use App\Models\DetailJurnal;
use App\Models\Jurnal;
use App\Models\JurnalPenyesuaian;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalPenyesuaianController extends Controller
{
    // ────────────────────────────────────────────────────────────────────────
    // INDEX
    // ────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Jurnal::with([
            'periode',
            'user',
            'jurnalPenyesuaian',
            'detailJurnal.akun',
        ])
            ->penyesuaian()
            ->orderByDesc('created_at');

        if ($request->filled('periode')) {
            $query->where('periode_id', $request->periode);
        }

        if ($request->filled('tipe')) {
            $query->whereHas('jurnalPenyesuaian', function ($q) use ($request) {
                $q->where('tipe_penyesuaian', $request->tipe);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage     = $request->input('per_page', 10);
        $jurnal      = $query->paginate($perPage)->withQueryString();
        $periodeList = Periode::orderByDesc('tanggal_awal')->get();

        return view('pages.jurnal-penyesuaian.index', compact(
            'jurnal',
            'periodeList',
            'perPage',
        ));
    }

    // ────────────────────────────────────────────────────────────────────────
    // CREATE
    // ────────────────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $periodeList = Periode::orderByDesc('tanggal_awal')->get();

        // Hanya akun level terbawah (yang punya parent) yang bisa dipilih
        $akunList = Akun::whereNotNull('parent_id')
            ->with('parent')
            ->orderBy('kode_akun')
            ->get();

        $asetList = Aset::aktif()->get();

        $tipe = $request->input('tipe', 'PENYUSUTAN_ASET');

        return view('pages.jurnal-penyesuaian.create', compact(
            'periodeList',
            'akunList',
            'asetList',
            'tipe',
        ));
    }

    // ────────────────────────────────────────────────────────────────────────
    // STORE
    // ────────────────────────────────────────────────────────────────────────

    public function store(StoreJurnalPenyesuaianRequest $request)
    {
        DB::beginTransaction();

        try {
            // Buat header jurnal (supertype)
            $jurnal = Jurnal::create([
                'periode_id'   => $request->periode_id,
                'user_id'      => auth()->id(),
                'nomor_jurnal' => Jurnal::generateNomor('PENYESUAIAN'),
                'jenis_jurnal' => 'PENYESUAIAN',
                'keterangan'   => $request->keterangan,
                'status'       => 'DRAFT',
            ]);

            // Buat subtype is-a
            JurnalPenyesuaian::create([
                'jurnal_id'        => $jurnal->id,
                'tipe_penyesuaian' => $request->tipe_penyesuaian,
                'aset_id'          => $request->tipe_penyesuaian === 'PENYUSUTAN_ASET'
                    ? $request->aset_id
                    : null,
            ]);

            // Buat baris detail jurnal sesuai tipe
            $this->storeDetail($jurnal, $request);

            DB::commit();

            return redirect()
                ->route('dashboard.jurnal-penyesuaian.show', $jurnal)
                ->with('success', 'Jurnal ' . $jurnal->nomor_jurnal . ' berhasil disimpan sebagai draft.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // SHOW
    // ────────────────────────────────────────────────────────────────────────

    public function show(Jurnal $jurnalPenyesuaian)
    {
        abort_if($jurnalPenyesuaian->jenis_jurnal !== 'PENYESUAIAN', 404);

        $jurnalPenyesuaian->load([
            'periode',
            'user',
            'jurnalPenyesuaian.aset',
            'detailJurnal.akun',
        ]);

        return view('pages.jurnal-penyesuaian.show', compact('jurnalPenyesuaian'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // POSTING — DRAFT → POSTED
    // ────────────────────────────────────────────────────────────────────────

    public function posting(Jurnal $jurnalPenyesuaian)
    {
        abort_if($jurnalPenyesuaian->jenis_jurnal !== 'PENYESUAIAN', 404);

        if ($jurnalPenyesuaian->status === 'POSTED') {
            return back()->with('error', 'Jurnal ini sudah diposting sebelumnya.');
        }

        // Wajib balance sebelum posting
        if (!$jurnalPenyesuaian->is_balance) {
            return back()->with('error', 'Jurnal tidak dapat diposting karena total debit dan kredit tidak seimbang.');
        }

        DB::beginTransaction();

        try {
            $jurnalPenyesuaian->update(['status' => 'POSTED']);

            // Side effect: update akumulasi aset jika tipe penyusutan
            $this->updateAkumulasiAset($jurnalPenyesuaian);

            DB::commit();

            return back()->with('success', 'Jurnal ' . $jurnalPenyesuaian->nomor_jurnal . ' berhasil diposting ke buku besar.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memposting jurnal: ' . $e->getMessage());
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // DESTROY — hanya DRAFT yang boleh dihapus
    // ────────────────────────────────────────────────────────────────────────

    public function destroy(Jurnal $jurnalPenyesuaian)
    {
        abort_if($jurnalPenyesuaian->jenis_jurnal !== 'PENYESUAIAN', 404);

        if ($jurnalPenyesuaian->status === 'POSTED') {
            return back()->with('error', 'Jurnal yang sudah diposting tidak dapat dihapus.');
        }

        // detail_jurnal dan jurnal_penyesuaian terhapus otomatis
        // via cascadeOnDelete di migration
        $jurnalPenyesuaian->delete();

        return redirect()
            ->route('dashboard.jurnal-penyesuaian.index')
            ->with('success', 'Draft jurnal berhasil dihapus.');
    }

    // ────────────────────────────────────────────────────────────────────────
    // API — data aset untuk preview JS di halaman create
    // ────────────────────────────────────────────────────────────────────────

    public function getAset(Aset $aset)
    {
        return response()->json([
            'id'                       => $aset->id,
            'nama_aset'                => $aset->nama_aset,
            'nilai_perolehan'          => $aset->nilai_perolehan,
            'akumulasi_penyusutan'     => $aset->akumulasi_penyusutan,
            'nilai_buku'               => $aset->nilai_buku,
            'penyusutan_per_bulan'     => $aset->penyusutan_per_bulan,
            'akun_beban_penyusutan_id' => $aset->akun_beban_penyusutan_id,
            'akun_akumulasi_id'        => $aset->akun_akumulasi_id,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PRIVATE — store detail per tipe penyesuaian
    // ────────────────────────────────────────────────────────────────────────

    private function storeDetail(Jurnal $jurnal, $request): void
    {
        match ($request->tipe_penyesuaian) {
            'PENYUSUTAN_ASET'          => $this->storeDetailPenyusutan($jurnal, $request),
            'BEBAN_BELUM_DIBAYAR'      => $this->storeDetailBebanBelumDibayar($jurnal, $request),
            'PENDAPATAN_BELUM_DICATAT' => $this->storeDetailPendapatanBelumDicatat($jurnal, $request),
            'BEBAN_DIBAYAR_DIMUKA'     => $this->storeDetailBebanDibayarDimuka($jurnal, $request),
            'ZAKAT_INFAQ'              => $this->storeDetailZakatInfaq($jurnal, $request),
            'MANUAL'                   => $this->storeDetailManual($jurnal, $request),
        };
    }

    /**
     * Penyusutan Aset
     * Debit  : Beban Penyusutan  (dari data aset)
     * Kredit : Akumulasi Penyusutan (dari data aset)
     * Nominal otomatis dari penyusutan_per_bulan aset
     */
    private function storeDetailPenyusutan(Jurnal $jurnal, $request): void
    {
        $aset = Aset::findOrFail($request->aset_id);

        DetailJurnal::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $aset->akun_beban_penyusutan_id,
            'tipe'       => 'DEBIT',
            'nominal'    => $aset->penyusutan_per_bulan,
            'keterangan' => $request->keterangan,
        ]);

        DetailJurnal::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $aset->akun_akumulasi_id,
            'tipe'       => 'KREDIT',
            'nominal'    => $aset->penyusutan_per_bulan,
            'keterangan' => $request->keterangan,
        ]);
    }

    /**
     * Beban Masih Harus Dibayar
     * Debit  : Beban terkait
     * Kredit : Utang Beban
     */
    private function storeDetailBebanBelumDibayar(Jurnal $jurnal, $request): void
    {
        DetailJurnal::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $request->akun_beban_id,
            'tipe'       => 'DEBIT',
            'nominal'    => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);

        DetailJurnal::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $request->akun_utang_id,
            'tipe'       => 'KREDIT',
            'nominal'    => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);
    }

    /**
     * Pendapatan Belum Dicatat
     * Debit  : Piutang Pendapatan
     * Kredit : Pendapatan
     */
    private function storeDetailPendapatanBelumDicatat(Jurnal $jurnal, $request): void
    {
        DetailJurnal::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $request->akun_piutang_id,
            'tipe'       => 'DEBIT',
            'nominal'    => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);

        DetailJurnal::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $request->akun_pendapatan_id,
            'tipe'       => 'KREDIT',
            'nominal'    => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);
    }

    /**
     * Beban Dibayar Dimuka
     * Debit  : Beban periode ini
     * Kredit : Biaya Dibayar Dimuka
     */
    private function storeDetailBebanDibayarDimuka(Jurnal $jurnal, $request): void
    {
        DetailJurnal::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $request->akun_beban_id,
            'tipe'       => 'DEBIT',
            'nominal'    => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);

        DetailJurnal::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $request->akun_dibayar_dimuka_id,
            'tipe'       => 'KREDIT',
            'nominal'    => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);
    }

    /**
     * Penyesuaian Zakat / Infaq
     * Debit  : Pendapatan Zakat/Infaq (sisa belum disalurkan)
     * Kredit : Kewajiban Dana Belum Disalurkan
     * Nominal = total_diterima - total_disalurkan
     */
    private function storeDetailZakatInfaq(Jurnal $jurnal, $request): void
    {
        $sisa = $request->total_diterima - $request->total_disalurkan;

        if ($sisa <= 0) {
            throw new \Exception('Tidak ada sisa dana yang perlu dicatat sebagai kewajiban.');
        }

        DetailJurnal::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $request->akun_pendapatan_id,
            'tipe'       => 'DEBIT',
            'nominal'    => $sisa,
            'keterangan' => $request->keterangan,
        ]);

        DetailJurnal::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $request->akun_kewajiban_id,
            'tipe'       => 'KREDIT',
            'nominal'    => $sisa,
            'keterangan' => $request->keterangan,
        ]);
    }

    /**
     * Manual Entry
     * Bendahara input debit/kredit bebas.
     * Validasi balance sudah dilakukan di StoreJurnalPenyesuaianRequest.
     */
    private function storeDetailManual(Jurnal $jurnal, $request): void
    {
        foreach ($request->detail as $row) {
            DetailJurnal::create([
                'jurnal_id'  => $jurnal->id,
                'akun_id'    => $row['akun_id'],
                'tipe'       => $row['tipe'],
                'nominal'    => $row['nominal'],
                'keterangan' => $row['keterangan'] ?? null,
            ]);
        }
    }

    /**
     * Update akumulasi penyusutan di tabel aset setelah posting.
     * Hanya berjalan jika tipe_penyesuaian = PENYUSUTAN_ASET.
     */
    private function updateAkumulasiAset(Jurnal $jurnal): void
    {
        $sub = $jurnal->jurnalPenyesuaian;

        if (!$sub || $sub->tipe_penyesuaian !== 'PENYUSUTAN_ASET' || !$sub->aset_id) {
            return;
        }

        $aset = $sub->aset;

        if (!$aset) return;

        $nominal = $jurnal->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');

        $aset->increment('akumulasi_penyusutan', $nominal);

        if ($aset->fresh()->akumulasi_penyusutan >= $aset->nilai_perolehan) {
            $aset->update(['status_aset' => 'HABIS']);
        }
    }
}