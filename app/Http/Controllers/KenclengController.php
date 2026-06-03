<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKenclengRequest;
use App\Http\Requests\UpdateKenclengRequest;
use App\Models\Kencleng;
use App\Services\KenclengService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KenclengController extends Controller
{
    public function __construct(
        protected KenclengService $kenclengService
    ) {}

    public function index(Request $request)
    {
        $search  = $request->get('search') ?? '';
        $perPage = (int) ($request->get('per_page') ?? 10);
        $sort    = $request->get('sort') ?? 'terbaru';
        $status  = $request->get('status') ?? '';

        $kencleng = $this->kenclengService->getList($search, $perPage, $sort, $status);

        return view('pages.kencleng.index', compact('kencleng', 'search', 'perPage', 'sort', 'status'));
    }

    public function create()
    {
        $dompetList = $this->kenclengService->getDompetList();
        $pecahan    = KenclengService::PECAHAN;

        return view('pages.kencleng.create', compact('dompetList', 'pecahan'));
    }

    public function store(StoreKenclengRequest $request)
    {
        try {
            $this->kenclengService->store($request->validated());

            return redirect()->route('dashboard.kencleng.index')
                ->with('success', 'Kencleng berhasil diajukan');
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan kencleng', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan kencleng. Silakan coba lagi.');
        }
    }

    public function show(Kencleng $kencleng)
    {
        if ($kencleng->transaksi->user_id !== auth()->id()) {
            abort(403);
        }

        $kencleng   = $this->kenclengService->getById($kencleng);
        $totalFisik = $this->kenclengService->getTotalFisik($kencleng);

        return view('pages.kencleng.show', compact('kencleng', 'totalFisik'));
    }

    public function edit(Kencleng $kencleng)
    {
        if ($kencleng->transaksi->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($kencleng->transaksi->status_approval, ['PENDING', 'REVISION', 'DRAFT'])) {
            return redirect()->back()
                ->with('error', 'Kencleng yang sudah diapprove tidak bisa diedit');
        }

        $kencleng  = $this->kenclengService->getById($kencleng);
        $dompetList = $this->kenclengService->getDompetList();
        $pecahan   = KenclengService::PECAHAN;
        $detailMap = $kencleng->detail->pluck('jumlah_pecahan', 'pecahan')->toArray();

        return view('pages.kencleng.edit', compact('kencleng', 'dompetList', 'pecahan', 'detailMap'));
    }

    public function update(UpdateKenclengRequest $request, Kencleng $kencleng)
    {
        if ($kencleng->transaksi->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($kencleng->transaksi->status_approval, ['PENDING', 'REVISION', 'DRAFT'])) {
            return redirect()->back()
                ->with('error', 'Kencleng yang sudah diapprove tidak bisa diedit');
        }

        try {
            $this->kenclengService->update($kencleng, $request->validated());

            return redirect()->route('dashboard.kencleng.index')
                ->with('success', 'Kencleng berhasil diperbarui dan diajukan');
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui kencleng', ['id' => $kencleng->id, 'error' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui kencleng. Silakan coba lagi.');
        }
    }

    public function destroy(Kencleng $kencleng)
    {
        try {
            $result = $this->kenclengService->delete($kencleng);

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