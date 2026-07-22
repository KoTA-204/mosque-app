<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalPembukaRequest;
use App\Http\Requests\UpdateJurnalPembukaRequest;
use App\Models\Jurnal;
use App\Services\Akuntansi\JurnalPembukaService;

/**
 * JurnalPembukaController
 *
 * Jurnal pembuka = SETUP satu-kali (opening balance), bukan CRUD biasa:
 *  - Hanya boleh ada satu jurnal pembuka (invarian domain).
 *  - Periode dihitung dari (jenis periode + tanggal awal) yang dipilih pengguna.
 *  - Setelah diposting / ada transaksi turunan, jurnal pembuka dikunci.
 *
 * Controller tipis: aturan bisnis di service, validasi input di FormRequest.
 * Operasi service mengembalikan array ['ok', 'status', 'message'].
 */
class JurnalPembukaController extends Controller
{
    public function __construct(private JurnalPembukaService $service) {}

    /** Halaman utama: tampilkan satu-satunya jurnal pembuka, atau arahkan ke setup. */
    public function tampilkanJurnalPembuka()
    {
        $jurnalPembuka = $this->service->getJurnalPembuka();

        if (! $jurnalPembuka) {
            return redirect()->route('dashboard.jurnal-pembuka.create');
        }

        $jurnalPembuka->load(['periode', 'detailJurnal.akun']);

        return view('pages.akuntansi.jurnal-pembuka.index', [
            'jurnalPembuka' => $jurnalPembuka,
            'stats'         => $this->service->getStatistik(),
        ]);
    }

    /** Form setup. Guard: hanya boleh dibuat sekali. */
    public function tambahJurnalPembuka()
    {
        if ($this->service->sudahDibuat()) {
            return redirect()->route('dashboard.jurnal-pembuka.index')
                ->with('error', 'Jurnal pembuka sudah pernah dibuat dan tidak dapat dibuat ulang.');
        }

        $akuns = $this->service->getAkunTransaksional();

        return view('pages.akuntansi.jurnal-pembuka.create', compact('akuns'));
    }

    /** Simpan saldo awal. */
    public function simpanJurnalPembuka(StoreJurnalPembukaRequest $request)
    {
        if ($this->service->sudahDibuat()) {
            return redirect()->route('dashboard.jurnal-pembuka.index')
                ->with('error', 'Jurnal pembuka sudah ada. Setup saldo awal hanya dapat dilakukan sekali.');
        }

        try {
            $jurnal = $this->service->catatSaldoAwal($request->validated());
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['tanggal_awal' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', 'Gagal menyimpan jurnal pembuka: ' . $e->getMessage());
        }

        $pesan = $jurnal->status === 'POSTED'
            ? 'Jurnal pembuka berhasil disimpan dan diposting.'
            : 'Jurnal pembuka berhasil disimpan sebagai draft.';

        return redirect()->route('dashboard.jurnal-pembuka.index')->with('success', $pesan);
    }

    public function tampilkanDetailJurnalPembuka(Jurnal $jurnalPembuka)
    {
        if (! request()->ajax()) {
            return redirect()->route('dashboard.jurnal-pembuka.index', ['buka' => $jurnalPembuka->id]);
        }

        $jurnalPembuka->load(['periode', 'detailJurnal.akun']);

        return response()->json([
            'success' => true,
            'data'    => $this->service->toDetailArray($jurnalPembuka),
        ]);
    }

    // Boleh diubah hanya jika belum diposting & belum ada transaksi turunan.
    public function ubahJurnalPembuka(Jurnal $jurnalPembuka)
    {
        if (! $this->service->dapatDiubah($jurnalPembuka, $alasan)) {
            return redirect()->route('dashboard.jurnal-pembuka.index')->with('error', $alasan);
        }

        $jurnalPembuka->load(['periode', 'detailJurnal.akun']);
        $akuns = $this->service->getAkunTransaksional();

        return view('pages.akuntansi.jurnal-pembuka.edit', compact('jurnalPembuka', 'akuns'));
    }

    /** Perbarui saldo awal. Periode dihitung ulang di service. */
    public function perbaruiJurnalPembuka(UpdateJurnalPembukaRequest $request, Jurnal $jurnalPembuka)
    {
        if (! $this->service->dapatDiubah($jurnalPembuka, $alasan)) {
            return redirect()->route('dashboard.jurnal-pembuka.index')->with('error', $alasan);
        }

        try {
            $jurnal = $this->service->perbaruiSaldoAwal($jurnalPembuka, $request->validated());
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['periode_bulan' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui jurnal pembuka: ' . $e->getMessage());
        }

        $pesan = $jurnal->status === 'POSTED'
            ? 'Jurnal pembuka berhasil diperbarui dan diposting.'
            : 'Perubahan jurnal pembuka disimpan sebagai draft.';

        return redirect()->route('dashboard.jurnal-pembuka.index')->with('success', $pesan);
    }

    public function hapusJurnalPembuka(Jurnal $jurnalPembuka)
    {
        $result = $this->service->hapusSaldoAwal($jurnalPembuka);

        return response()->json(
            ['success' => $result['ok'], 'message' => $result['message']],
            $result['status']
        );
    }
}