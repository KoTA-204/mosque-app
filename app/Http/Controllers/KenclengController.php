<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKenclengRequest;
use App\Http\Requests\UpdateKenclengRequest;
use App\Models\Kencleng;
use App\Services\KenclengService;
use Illuminate\Http\Request;

class KenclengController extends Controller
{
    public function __construct(
        protected KenclengService $kenclengService
    ) {}

    public function index(Request $request)
    {
        $search   = $request->get('search', '');
        $perPage  = $request->get('per_page', 10);
        $kencleng = $this->kenclengService->getList($search, $perPage);

        return view('pages.kencleng.index', compact('kencleng', 'search', 'perPage'));
    }

    public function create()
    {
        $dompetList   = $this->kenclengService->getDompetList();
        $pecahan      = KenclengService::PECAHAN;

        return view('pages.kencleng.create', compact('dompetList', 'pecahan'));
    }

    public function store(StoreKenclengRequest $request)
    {
        $statusApproval = $request->submit_type === 'ajukan' ? 'PENDING' : 'DRAFT';

        // Validasi berita acara wajib saat ajukan
        if ($request->submit_type === 'ajukan' && !$request->hasFile('berita_acara')) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['berita_acara' => 'Berita acara wajib diupload saat mengajukan']);
        }

        $this->kenclengService->store($request->validated(), $statusApproval);

        $message = $request->submit_type === 'ajukan'
            ? 'Kencleng berhasil diajukan'
            : 'Kencleng berhasil disimpan sebagai draf';

        return redirect()->route('dashboard.kencleng.index')
            ->with('success', $message);
    }

    public function show(Kencleng $kencleng)
    {
        // Pastikan hanya pemilik yang bisa lihat
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

        $kencleng   = $this->kenclengService->getById($kencleng);
        $dompetList = $this->kenclengService->getDompetList();
        $pecahan    = KenclengService::PECAHAN;

        // Map detail ke array pecahan
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

        $this->kenclengService->update($kencleng, $request->validated());

        $message = $request->submit_type === 'ajukan'
            ? 'Kencleng berhasil diperbarui dan diajukan'
            : 'Kencleng berhasil disimpan sebagai draf';

        return redirect()->route('dashboard.kencleng.index')
            ->with('success', $message);
    }

    public function destroy(Kencleng $kencleng)
    {
        $result = $this->kenclengService->delete($kencleng);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.kencleng.index')
            ->with('success', 'Kencleng berhasil dihapus');
    }
}