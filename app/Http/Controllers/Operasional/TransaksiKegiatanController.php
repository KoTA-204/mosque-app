<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use App\Models\Kegiatan;
use App\Models\Transaksi;
use App\Services\Operasional\TransaksiKegiatanService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTransaksiKegiatanRequest;
use App\Http\Requests\UpdateTransaksiKegiatanRequest;

class TransaksiKegiatanController extends Controller
{
    public function __construct(
        protected TransaksiKegiatanService $transaksiKegiatanService
    ) {}

    // ── List Kegiatan ─────────────────────────────────────────
    public function tampilkanDaftarKegiatan(Request $request)
    {
        $search   = $request->get('search', '');
        $status   = $request->get('status', '');
        $kegiatan = $this->transaksiKegiatanService->getDaftarKegiatan($search, $status);
        $summary  = $this->transaksiKegiatanService->hitungRingkasanKegiatan();

        return view('pages.operasional.transaksi-kegiatan.index', compact('kegiatan', 'summary', 'search', 'status'));
    }

    // ── List Transaksi per Kegiatan ────────────────────────────
    public function tampilkanTransaksiKegiatan(Kegiatan $kegiatan, Request $request)
    {
        $this->pastikanBerhakAtasKegiatan($kegiatan);

        $search        = $request->get('search', '');
        $jenis         = $request->get('jenis', '');
        $status        = $request->get('status', '');
        $transaksi     = $this->transaksiKegiatanService->getTransaksiPerKegiatan($kegiatan, $search, $jenis, $status);
        $porsi         = $this->transaksiKegiatanService->hitungPorsiAnggaran($kegiatan);
        $dompetList    = $this->transaksiKegiatanService->getDaftarDompet();
        $kategoriList  = $this->transaksiKegiatanService->getDaftarKategori();
        $kodeTransaksi = $this->transaksiKegiatanService->buatKodeTransaksi();

        return view('pages.operasional.transaksi-kegiatan.show', compact(
            'kegiatan', 'transaksi', 'porsi', 'search', 'jenis', 'status',
            'dompetList', 'kategoriList', 'kodeTransaksi'
        ));
    }

    // ── Simpan Transaksi ───────────────────────────────────────
    public function simpanTransaksiKegiatan(StoreTransaksiKegiatanRequest $request, Kegiatan $kegiatan)
    {
        $this->pastikanBerhakAtasKegiatan($kegiatan);

        //yang mengunci pencatatan adalah STATUS kegiatan, bukan tanggal.
        if (! $kegiatan->sedangAktif()) {
            return back()->with('error', 'Kegiatan sudah ditutup, transaksi tidak dapat dicatat');
        }

        $data = $request->validated();

        try {
            $this->transaksiKegiatanService->simpanTransaksiKegiatan($kegiatan, $data);
        } catch (\Throwable $e) {
            \Log::error('Gagal menyimpan transaksi kegiatan', [
                'kegiatan_id' => $kegiatan->id,
                'error'       => $e->getMessage(),
            ]);

            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan transaksi. Silakan coba lagi.');
        }

        $redirect = redirect()
            ->route('dashboard.transaksi-kegiatan.show', $kegiatan)
            ->with('success', 'Transaksi berhasil dicatat');

        //(warning lunak): tanggal acara sudah lewat → ingatkan, TAPI tetap simpan.
        if ($kegiatan->tanggalSudahSelesai()) {
            $redirect->with('info', 'Catatan: tanggal kegiatan sudah lewat. Pastikan ini pencatatan susulan yang sah.');
        }

        if ($data['jenis_transaksi'] === 'PENGELUARAN') {
            $lebih = $kegiatan->selisihLebihAnggaran();
            if ($lebih > 0) {
                $redirect->with('warning',
                    'Perhatian: total pengeluaran kegiatan melebihi anggaran sebesar Rp '
                    . number_format($lebih, 0, ',', '.')
                    . '. Transaksi tetap tercatat untuk ditinjau bendahara.');
            }
        }

        return $redirect;
    }

    // ── Detail Transaksi ───────────────────────────────────────
    public function tampilkanDetailTransaksiKegiatan(Kegiatan $kegiatan, Transaksi $transaksi)
    {
        $this->pastikanTransaksiMilikKegiatan($kegiatan, $transaksi);

        $transaksi = $this->transaksiKegiatanService->getDetailTransaksi($transaksi);

        return view('pages.operasional.transaksi-kegiatan.show-transaksi', compact('kegiatan', 'transaksi'));
    }

    // ── Update Transaksi (hanya PENDING / REVISION) ───────────
    public function perbaruiTransaksiKegiatan(UpdateTransaksiKegiatanRequest $request, Kegiatan $kegiatan, Transaksi $transaksi)
    {
        $this->pastikanTransaksiMilikKegiatan($kegiatan, $transaksi);

        if (! $transaksi->bisaDiedit()) {
            return back()->with('error', 'Transaksi tidak dapat diedit karena sudah diproses');
        }
        if ($transaksi->pengguna_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validated();

        try {
            $this->transaksiKegiatanService->perbaruiTransaksiKegiatan($transaksi, $data);
        } catch (\Throwable $e) {
            \Log::error('Gagal memperbarui transaksi kegiatan', [
                'transaksi_id' => $transaksi->id,
                'error'        => $e->getMessage(),
            ]);

            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui transaksi. Silakan coba lagi.');
        }

        $redirect = redirect()
            ->route('dashboard.transaksi-kegiatan.show', $kegiatan)
            ->with('success', 'Transaksi berhasil diperbarui');

        if ($data['jenis_transaksi'] === 'PENGELUARAN') {
            $lebih = $kegiatan->selisihLebihAnggaran();
            if ($lebih > 0) {
                $redirect->with('warning',
                    'Perhatian: total pengeluaran kegiatan melebihi anggaran sebesar Rp '
                    . number_format($lebih, 0, ',', '.') . '.');
            }
        }

        return $redirect;
    }

    // ── Hapus Transaksi ────────────────────────────────────────
    public function hapusTransaksiKegiatan(Kegiatan $kegiatan, Transaksi $transaksi)
    {
        $this->pastikanTransaksiMilikKegiatan($kegiatan, $transaksi);

        $result = $this->transaksiKegiatanService->hapusTransaksiKegiatan($transaksi);

        if ($result !== true) {
            return back()->with('error', $result);
        }

        return redirect()
            ->route('dashboard.transaksi-kegiatan.show', $kegiatan)
            ->with('success', 'Transaksi berhasil dihapus');
    }

    // ── Helpers ────────────────────────────────────────────────
    private function pastikanBerhakAtasKegiatan(Kegiatan $kegiatan): void
    {
        if (auth()->user()->hasPeran('panitia-kegiatan-khusus') && $kegiatan->panitia_id !== auth()->id()) {
            abort(403);
        }
    }

    private function pastikanTransaksiMilikKegiatan(Kegiatan $kegiatan, Transaksi $transaksi): void
    {
        if ($transaksi->kegiatan_id !== $kegiatan->id) {
            abort(404);
        }
        $this->pastikanBerhakAtasKegiatan($kegiatan);
    }
}