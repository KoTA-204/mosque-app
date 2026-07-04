<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKenclengRequest;
use App\Http\Requests\UpdateKenclengRequest;
use App\Models\Kencleng;
use App\Services\Operasional\KenclengService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KenclengController extends Controller
{
    public function __construct(
        protected KenclengService $kenclengService
    ) {}

    public function tampilkanDaftarKencleng(Request $request)
    {
        $search  = $request->get('search') ?? '';
        $perPage = (int) ($request->get('per_page') ?? 10);
        $sort    = $request->get('sort') ?? 'terbaru';
        $status  = $request->get('status') ?? '';

        $kencleng = $this->kenclengService->getDaftarKencleng($search, $perPage, $sort, $status);

        return view('pages.operasional.kencleng.index', compact('kencleng', 'search', 'perPage', 'sort', 'status'));
    }

    public function tampilkanFormTambahKencleng()
    {
        $dompetList = $this->kenclengService->getDaftarDompet();
        $pecahan    = KenclengService::PECAHAN;

        return view('pages.operasional.kencleng.create', compact('dompetList', 'pecahan'));
    }

    public function simpanKenclengBaru(StoreKenclengRequest $request)
    {
        try {
            $this->kenclengService->simpanKenclengBaru($request->validated());

            return redirect()->route('dashboard.kencleng.index')
                ->with('success', 'Kencleng berhasil diajukan');
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan kencleng', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan kencleng. Silakan coba lagi.');
        }
    }

    public function tampilkanDetailKencleng(Kencleng $kencleng)
    {
        if ($kencleng->transaksi->user_id !== auth()->id()) {
            abort(403);
        }

        $kencleng   = $this->kenclengService->getDetailKencleng($kencleng);
        $totalFisik = $this->kenclengService->hitungTotalFisik($kencleng);

        return view('pages.operasional.kencleng.show', compact('kencleng', 'totalFisik'));
    }

    public function tampilkanFormEditKencleng(Kencleng $kencleng)
    {
        if ($kencleng->transaksi->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($kencleng->transaksi->status_approval, ['PENDING', 'REVISION', 'DRAFT'])) {
            return redirect()->back()
                ->with('error', 'Kencleng yang sudah diapprove tidak bisa diedit');
        }

        $kencleng  = $this->kenclengService->getDetailKencleng($kencleng);
        $dompetList = $this->kenclengService->getDaftarDompet();
        $pecahan   = KenclengService::PECAHAN;
        $detailMap = $kencleng->detail->pluck('jumlah_pecahan', 'pecahan')->toArray();

        return view('pages.operasional.kencleng.edit', compact('kencleng', 'dompetList', 'pecahan', 'detailMap'));
    }

    public function perbaruiKencleng(UpdateKenclengRequest $request, Kencleng $kencleng)
    {
        if ($kencleng->transaksi->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($kencleng->transaksi->status_approval, ['PENDING', 'REVISION', 'DRAFT'])) {
            return redirect()->back()
                ->with('error', 'Kencleng yang sudah diapprove tidak bisa diedit');
        }

        try {
            $this->kenclengService->perbaruiKencleng($kencleng, $request->validated());

            return redirect()->route('dashboard.kencleng.index')
                ->with('success', 'Kencleng berhasil diperbarui dan diajukan');
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui kencleng', ['id' => $kencleng->id, 'error' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui kencleng. Silakan coba lagi.');
        }
    }

    public function hapusKencleng(Kencleng $kencleng)
    {
        try {
            $result = $this->kenclengService->hapusKencleng($kencleng);

            if ($result !== true) {
                return redirect()->back()->with('error', $result);
            }

            return redirect()->route('dashboard.kencleng.index')
                ->with('success', 'Kencleng berhasil dihapus');
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus kencleng', ['id' => $kencleng->id, 'error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus kencleng. Silakan coba lagi.');
        }
    }
}