<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Models\Jurnal;
use App\Services\Akuntansi\JurnalKoreksiService;
use Illuminate\Http\Request;

class JurnalKoreksiController extends Controller
{
    public function __construct(
        protected JurnalKoreksiService $service
    ) {}

    public function index(Request $request)
    {
        $search    = $request->get('search', '');
        $periodeId = $request->get('periode_id', '');
        $status    = $request->get('status', '');
        $perPage   = (int) $request->get('per_page', 10);

        $jurnal       = $this->service->getList($search, $periodeId, '', $status, $perPage);
        $periodeList  = $this->service->getPeriodeList();
        $periodeAktif = $this->service->getPeriodeAktif();

        return view('pages.akuntansi.jurnal-koreksi.index', compact(
            'jurnal', 'periodeList', 'periodeAktif',
            'search', 'periodeId', 'status', 'perPage'
        ));
    }

    public function create()
    {
        $periodeAktif  = $this->service->getPeriodeAktif();
        $periodeList   = $this->service->getPeriodeList();
        $jurnalData    = $this->service->getJurnalData();
        $akunList      = $this->service->getAkunList();

        return view('pages.akuntansi.jurnal-koreksi.create', compact(
            'periodeAktif', 'periodeList', 'akunList', 'jurnalData'
        ));
    }

    public function store(Request $request)
    {
        $rules = [
            'periode_id'       => 'required|exists:periode,id',
            'tanggal'          => 'required|date',
            'jurnal_ref_id'    => 'required|exists:jurnal,id',
            'keterangan'       => 'required|string|max:500',
            'detail'           => 'required|array|min:2',
            'detail.*.akun_id' => 'required|exists:akun,id',
            'detail.*.tipe'    => 'required|in:DEBIT,KREDIT',
            'detail.*.nominal' => 'required|string',
            'submit_type'      => 'required|in:draft,posting',
        ];

        $request->validate($rules);

        $status = $request->submit_type === 'posting' ? 'POSTED' : 'DRAFT';

        // Validasi balance sebelum posting
        if ($status === 'POSTED') {
            $totalDebit  = 0;
            $totalKredit = 0;
            foreach ($request->detail as $d) {
                $nominal = (float) str_replace(['.', ','], ['', '.'], $d['nominal'] ?? 0);
                if ($d['tipe'] === 'DEBIT')  $totalDebit  += $nominal;
                if ($d['tipe'] === 'KREDIT') $totalKredit += $nominal;
            }
            if (round($totalDebit, 2) !== round($totalKredit, 2)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['balance' => 'Total debit dan kredit harus sama sebelum diposting.']);
            }
        }

        $this->service->store($request->all(), $status);

        $msg = $status === 'POSTED'
            ? 'Jurnal koreksi berhasil diposting ke buku besar.'
            : 'Jurnal koreksi berhasil disimpan sebagai draft.';

        return redirect()->route('dashboard.jurnal-koreksi.index')
            ->with('success', $msg);
    }

    public function show(Jurnal $jurnal)
    {
        // Load semua relasi yang dibutuhkan drawer
        $jurnal = $this->service->getById($jurnal);

        // FIX: generate nomor jurnal manual (tidak ada kolom nomor_jurnal di model)
        $nomorJurnal = 'JK-'
            . ($jurnal->periode->tanggal_awal->format('Y') ?? '0000') . '-'
            . ($jurnal->periode->tanggal_awal->format('m') ?? '00') . '-'
            . str_pad($jurnal->id, 4, '0', STR_PAD_LEFT);

        // FIX: format data untuk JSON response agar drawer bisa render dengan benar
        return response()->json([
            'jurnal' => [
                'id'             => $jurnal->id,
                'nomor_jurnal'   => $nomorJurnal,
                'tanggal'        => $jurnal->tanggal?->format('j M Y'),
                'keterangan'     => $jurnal->keterangan,
                'status'         => $jurnal->status,
                'jenis_jurnal'   => $jurnal->jenis_jurnal,
                'jurnal_ref_id'  => $jurnal->jurnal_ref_id,
                'jurnal_ref'     => $jurnal->jurnalRef ? [
                    'id'         => $jurnal->jurnalRef->id,
                    'keterangan' => $jurnal->jurnalRef->keterangan,
                    'tanggal'    => $jurnal->jurnalRef->tanggal?->format('j M Y'),
                ] : null,
                'periode'        => [
                    'nama_periode' => $jurnal->periode->nama_periode ?? '—',
                ],
                // detail_jurnal dalam snake_case agar konsisten dengan JS (j.detail_jurnal)
                'detail_jurnal'  => $jurnal->detailJurnal->map(fn($d) => [
                    'tipe'    => $d->tipe,
                    'nominal' => (float) $d->nominal,
                    'akun'    => [
                        'kode_akun' => $d->akun->kode_akun ?? '',
                        'nama_akun' => $d->akun->nama_akun ?? '—',
                    ],
                ]),
                'aset' => $jurnal->aset->map(fn($a) => [
                    'nama_aset' => $a->nama_aset,
                    'pivot'     => ['nominal' => (float) ($a->pivot->nominal ?? 0)],
                ]),
            ],
        ]);
    }

    public function bulkPost(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:jurnal,id',
        ]);

        $berhasil   = 0;
        $gagal      = 0;
        $pesanGagal = [];

        foreach ($request->ids as $id) {
            $jurnal = Jurnal::find($id);

            if (!$jurnal) {
                $gagal++;
                continue;
            }

            $result = $this->service->post($jurnal);

            if ($result === true) {
                $berhasil++;
            } else {
                $gagal++;
                $pesanGagal[] = $result;
            }
        }

        if ($berhasil === 0) {
            $detail = !empty($pesanGagal) ? ' (' . implode(', ', array_unique($pesanGagal)) . ')' : '';
            return redirect()->route('dashboard.jurnal-koreksi.index')
                ->with('error', "Tidak ada jurnal yang berhasil diposting.{$detail}");
        }

        $msg = "Berhasil memposting {$berhasil} jurnal.";
        if ($gagal > 0) {
            $msg .= " {$gagal} jurnal gagal (debit/kredit tidak balance atau sudah diposting).";
        }

        return redirect()->route('dashboard.jurnal-koreksi.index')
            ->with($gagal > 0 ? 'error' : 'success', $msg);
    }

    public function destroy(Jurnal $jurnal)
    {
        $result = $this->service->delete($jurnal);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.jurnal-koreksi.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }
}