<?php

namespace App\Services\Operasional;

use App\Models\Dompet;
use App\Models\Kencleng;
use App\Models\KategoriTransaksi;
use App\Models\KenclengDetail;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KenclengService
{
    const PECAHAN = [100, 200, 500, 1000, 2000, 5000, 10000, 20000, 50000, 100000];

    public function getList(?string $search = '', int $perPage = 10, ?string $sort = 'terbaru', ?string $status = '')
    {
        $search = $search ?? '';
        $sort   = $sort   ?? 'terbaru';
        $status = $status ?? '';
        $order  = $sort === 'terlama' ? 'asc' : 'desc';

        return Kencleng::with(['transaksi.user', 'transaksi.dompet', 'detail'])
            ->whereHas('transaksi', fn($q) => $q->where('user_id', auth()->id()))
            ->when($search, fn($q) =>
                $q->where(function ($q) use ($search) {         
                    $term = '%' . strtolower($search) . '%';
                    $q->whereRaw('LOWER(nomor_kwitansi) LIKE ?', [$term])
                    ->orWhereHas('transaksi', fn($q) =>
                        $q->whereRaw('LOWER(deskripsi) LIKE ?', [$term])
                    );
                })
            )
            ->when($status, fn($q) =>
                $q->whereHas('transaksi', fn($q) =>
                    $q->where('status_approval', $status)
                )
            )
            ->orderBy('created_at', $order)
            ->paginate($perPage);
    }
    
    public function getById(Kencleng $kencleng): Kencleng
    {
        return $kencleng->load('transaksi.user', 'transaksi.dompet', 'transaksi.kategoriTransaksi', 'detail');
    }

    public function getDompetList()
    {
        return Dompet::orderBy('nama_dompet')->get();
    }

    public function getKategoriKencleng(): ?KategoriTransaksi
    {
        return KategoriTransaksi::whereRaw('LOWER(nama_kategori) LIKE ?', ['%kencleng%'])
            ->first();
    }

    public function generateNomorKwitansi(): string
    {
        $year  = now()->year;
        $count = Kencleng::whereYear('created_at', $year)->count() + 1;
        return 'KWT-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function store(array $data): Kencleng
    {
        return DB::transaction(function () use ($data) {
            $kategori = $this->getKategoriKencleng();

            $totalFisik = 0;
            foreach (self::PECAHAN as $pecahan) {
                $jumlah      = (int) ($data['pecahan'][$pecahan] ?? 0);
                $totalFisik += $pecahan * $jumlah;
            }

            $pathBA = null;
            if (!empty($data['berita_acara'])) {
                $pathBA = $data['berita_acara']->store('berita_acara', 'public');
            }

            try {
                $transaksi = Transaksi::create([
                    'dompet_id'             => $data['dompet_id'],
                    'kegiatan_id'           => null,
                    'user_id'               => auth()->id(),
                    'kategori_transaksi_id' => $kategori?->id,
                    'tanggal_transaksi'     => $data['tanggal_hitung'],
                    'jenis_transaksi'       => 'PEMASUKAN',
                    'jumlah'                => $totalFisik,
                    'deskripsi'             => $data['keterangan'] ?? null,
                    'status_approval'       => 'PENDING',
                    'status_jurnal'         => 'UNMAPPED',
                ]);

                $kencleng = Kencleng::create([
                    'transaksi_id'   => $transaksi->id,
                    'nomor_kwitansi' => $this->generateNomorKwitansi(),
                    'berita_acara'   => $pathBA,
                ]);

                foreach (self::PECAHAN as $pecahan) {
                    $jumlah = (int) ($data['pecahan'][$pecahan] ?? 0);
                    if ($jumlah > 0) {
                        KenclengDetail::create([
                            'kencleng_id'    => $kencleng->id,
                            'pecahan'        => $pecahan,
                            'jumlah_pecahan' => $jumlah,
                        ]);
                    }
                }

                return $kencleng->load('transaksi', 'detail');
            } catch (\Throwable $e) {
                if ($pathBA) {
                    Storage::disk('public')->delete($pathBA);
                }
                Log::error('KenclengService::store gagal', ['error' => $e->getMessage()]);
                throw $e; 
            }
        });
    }

    public function update(Kencleng $kencleng, array $data): Kencleng
    {
        return DB::transaction(function () use ($kencleng, $data) {
            $transaksi = $kencleng->transaksi;

            $totalFisik = 0;
            foreach (self::PECAHAN as $pecahan) {
                $jumlah      = (int) ($data['pecahan'][$pecahan] ?? 0);
                $totalFisik += $pecahan * $jumlah;
            }

            $pathBA    = $kencleng->berita_acara;
            $oldPathBA = $pathBA;
            $newPathBA = null;

            if (!empty($data['berita_acara'])) {
                $newPathBA = $data['berita_acara']->store('berita_acara', 'public');
                $pathBA    = $newPathBA;
            }

            try {
                $transaksi->update([
                    'dompet_id'         => $data['dompet_id'],
                    'tanggal_transaksi' => $data['tanggal_hitung'],
                    'jumlah'            => $totalFisik,
                    'deskripsi'         => $data['keterangan'] ?? null,
                    'status_approval'   => 'PENDING',
                ]);

                $kencleng->update(['berita_acara' => $pathBA]);

                $kencleng->detail()->delete();
                foreach (self::PECAHAN as $pecahan) {
                    $jumlah = (int) ($data['pecahan'][$pecahan] ?? 0);
                    if ($jumlah > 0) {
                        KenclengDetail::create([
                            'kencleng_id'    => $kencleng->id,
                            'pecahan'        => $pecahan,
                            'jumlah_pecahan' => $jumlah,
                        ]);
                    }
                }

                if ($newPathBA && $oldPathBA) {
                    Storage::disk('public')->delete($oldPathBA);
                }

                return $kencleng->fresh()->load('transaksi', 'detail');
            } catch (\Throwable $e) {
                if ($newPathBA) {
                    Storage::disk('public')->delete($newPathBA);
                }
                Log::error('KenclengService::update gagal', ['id' => $kencleng->id, 'error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    public function delete(Kencleng $kencleng): bool|string
    {
        $transaksi = $kencleng->transaksi;

        if (!in_array($transaksi->status_approval, ['PENDING', 'REVISION', 'DRAFT'])) {
            return 'Kencleng yang sudah diapprove tidak bisa dihapus';
        }

        if ($transaksi->user_id !== auth()->id()) {
            return 'Anda tidak bisa menghapus kencleng orang lain';
        }

        DB::transaction(function () use ($kencleng) {
            $pathBA = $kencleng->berita_acara;

            $kencleng->detail()->delete();
            $kencleng->delete();
            $kencleng->transaksi()->delete();

            if ($pathBA) {
                Storage::disk('public')->delete($pathBA);
            }
        });

        return true;
    }

    public function getTotalFisik(Kencleng $kencleng): int
    {
        return $kencleng->detail->sum(fn($d) => $d->pecahan * $d->jumlah_pecahan);
    }
}