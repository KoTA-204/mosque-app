<?php

namespace App\Services;

use App\Models\Dompet;
use App\Models\Kencleng;
use App\Models\KategoriTransaksi;
use App\Models\KenclengDetail;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KenclengService
{
    // Pecahan yang tersedia
    const PECAHAN = [100, 500, 1000, 2000, 5000, 10000, 20000, 50000, 100000];

    public function getList(string $search = '', int $perPage = 10)
    {
        return Kencleng::with(['transaksi.user', 'transaksi.dompet', 'detail'])
            ->whereHas('transaksi', fn($q) =>
                $q->where('user_id', auth()->id())
            )
            ->when($search, fn($q) =>
                $q->where('nomor_kwitansi', 'ilike', "%{$search}%")
                ->orWhereHas('transaksi', fn($q) =>
                    $q->where('deskripsi', 'ilike', "%{$search}%")
                )
            )
            ->orderBy('created_at', 'desc')
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
        return KategoriTransaksi::where('nama_kategori', 'ilike', '%kencleng%')
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->first();
    }

    public function generateNomorKwitansi(): string
    {
        $year  = now()->year;
        $count = Kencleng::whereYear('created_at', $year)->count() + 1;
        return 'KWT-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function store(array $data, string $statusApproval = 'PENDING'): Kencleng
    {
        return DB::transaction(function () use ($data, $statusApproval) {
            $kategori = $this->getKategoriKencleng();

            // Hitung total fisik dari pecahan
            $totalFisik = 0;
            foreach (self::PECAHAN as $pecahan) {
                $jumlah = (int) ($data['pecahan'][$pecahan] ?? 0);
                $totalFisik += $pecahan * $jumlah;
            }

            $jumlahSetor = (int) str_replace('.', '', $data['jumlah_disetor']);

            // Upload berita acara
            $pathBA = null;
            if (!empty($data['berita_acara'])) {
                $pathBA = $data['berita_acara']->store('berita_acara', 'public');
            }

            // Buat transaksi induk
            $transaksi = Transaksi::create([
                'dompet_id'             => $data['dompet_id'],
                'kegiatan_id'           => null,
                'user_id'               => auth()->id(),
                'kategori_transaksi_id' => $kategori?->id,
                'tanggal_transaksi'     => $data['tanggal_hitung'],
                'jumlah'                => $jumlahSetor,
                'deskripsi'             => $data['keterangan'] ?? null,
                'status_approval'       => $statusApproval,
                'status_jurnal'         => 'UNMAPPED',
            ]);

            // Buat kencleng
            $kencleng = Kencleng::create([
                'transaksi_id'      => $transaksi->id,
                'nomor_kwitansi'    => $this->generateNomorKwitansi(),
                'berita_acara'      => $pathBA,
            ]);

            // Buat detail pecahan
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
        });
    }

    public function update(Kencleng $kencleng, array $data): Kencleng
    {
        return DB::transaction(function () use ($kencleng, $data) {
            $transaksi  = $kencleng->transaksi;
            $jumlahSetor = (int) str_replace('.', '', $data['jumlah_disetor']);

            // Update berita acara kalau ada file baru
            $pathBA = $kencleng->berita_acara;
            if (!empty($data['berita_acara'])) {
                if ($pathBA) Storage::disk('public')->delete($pathBA);
                $pathBA = $data['berita_acara']->store('berita_acara', 'public');
            }

            // Update transaksi
            $transaksi->update([
                'dompet_id'         => $data['dompet_id'],
                'tanggal_transaksi' => $data['tanggal_hitung'],
                'jumlah'            => $jumlahSetor,
                'deskripsi'         => $data['keterangan'] ?? null,
                'status_approval'   => 'PENDING', // reset ke pending
            ]);

            // Update kencleng
            $kencleng->update([
                'berita_acara' => $pathBA,
            ]);

            // Hapus detail lama & buat ulang
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

            return $kencleng->fresh()->load('transaksi', 'detail');
        });
    }

    public function delete(Kencleng $kencleng): bool|string
    {
        $transaksi = $kencleng->transaksi;

        if (!in_array($transaksi->status_approval, ['PENDING', 'REVISION'])) {
            return 'Kencleng yang sudah diapprove tidak bisa dihapus';
        }

        if ($transaksi->user_id !== auth()->id()) {
            return 'Anda tidak bisa menghapus kencleng orang lain';
        }

        DB::transaction(function () use ($kencleng) {
            if ($kencleng->berita_acara) {
                Storage::disk('public')->delete($kencleng->berita_acara);
            }
            $kencleng->detail()->delete();
            $kencleng->delete();
            $kencleng->transaksi()->delete();
        });

        return true;
    }

    public function getTotalFisik(Kencleng $kencleng): int
    {
        return $kencleng->detail->sum(fn($d) => $d->pecahan * $d->jumlah_pecahan);
    }
}