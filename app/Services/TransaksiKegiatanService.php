<?php

namespace App\Services;

use App\Models\BuktiTransaksi;
use App\Models\Dompet;
use App\Models\KategoriTransaksi;
use App\Models\Kegiatan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransaksiKegiatanService
{
    // ── Kegiatan ──────────────────────────────────────────────

    public function getKegiatanList(?string $search = null, ?string $status = null, int $perPage = 10)
    {
        return Kegiatan::with('panitia')
            ->withCount('transaksi')
            ->withCount([
                'transaksi as transaksi_pending_count' => fn($q) =>
                    $q->where('status_approval', 'PENDING')
            ])
            ->when(auth()->user()->hasRole('panitia-khusus'), fn($q) =>
                $q->where('panitia_id', auth()->id())
            )
            ->when($search, fn($q) =>
                $q->where('nama_kegiatan', 'ilike', "%{$search}%")
            )
            ->when($status, fn($q) =>
                $q->where('status', strtoupper($status))
            )
            ->orderByRaw("
                CASE status
                    WHEN 'AKTIF'   THEN 1
                    WHEN 'DITUTUP' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('tanggal_mulai', 'desc')
            ->paginate($perPage);
    }

    public function getKegiatanById(Kegiatan $kegiatan): Kegiatan
    {
        return $kegiatan->load('panitia', 'transaksi.kategoriTransaksi', 'transaksi.user');
    }

    public function getSummary(): array
    {
        $query = Kegiatan::query()
            ->when(auth()->user()->hasRole('panitia-khusus'), fn($q) =>
                $q->where('panitia_id', auth()->id())
            );

        return [
            'total'   => (clone $query)->count(),
            'aktif'   => (clone $query)->where('status', 'BERJALAN')->count(),
            'pending' => Transaksi::whereHas('kegiatan', function ($q) {
                            $q->when(auth()->user()->hasRole('panitia-khusus'), fn($q) =>
                                $q->where('panitia_id', auth()->id())
                            );
                        })->where('status_approval', 'PENDING')->count(),
        ];
    }

    public function getPorsiAnggaran(Kegiatan $kegiatan): int
    {
        if ($kegiatan->anggaran <= 0) return 0;

        $totalTransaksi = $kegiatan->transaksi()
            ->whereHas('kategoriTransaksi', fn($q) =>
                $q->where('jenis_transaksi', 'PEMASUKAN')
            )
            ->where('status_approval', 'APPROVED')
            ->sum('jumlah');

        return min(100, (int) round(($totalTransaksi / $kegiatan->anggaran) * 100));
    }

    // ── Transaksi ──────────────────────────────────────────────

    public function getTransaksiByKegiatan(Kegiatan $kegiatan, string $search = '', int $perPage = 10)
    {
        return $kegiatan->transaksi()
            ->with(['dompet', 'kategoriTransaksi', 'user', 'buktiTransaksi'])
            ->when($search, fn($q) =>
                $q->where('deskripsi', 'ilike', "%{$search}%") // ilike untuk PostgreSQL
            )
            ->orderBy('tanggal_transaksi', 'desc')
            ->paginate($perPage);
    }

    public function getTransaksiById(Transaksi $transaksi): Transaksi
    {
        return $transaksi->load('dompet', 'kategoriTransaksi', 'user', 'kegiatan', 'buktiTransaksi');
    }

    public function getDompetList()
    {
        return Dompet::orderBy('nama_dompet')->get();
    }

    public function getKategoriList(string $jenis = '')
    {
        return KategoriTransaksi::when($jenis, fn($q) =>
            $q->where('jenis_transaksi', strtoupper($jenis))
        )->orderBy('nama_kategori')->get();
    }

    public function generateKodeTransaksi(): string
    {
        $year  = now()->year;
        $count = Transaksi::whereYear('created_at', $year)->count() + 1;
        return 'TRX-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function storeTransaksi(Kegiatan $kegiatan, array $data): Transaksi
    {
        return DB::transaction(function () use ($kegiatan, $data) {
            $transaksi = Transaksi::create([
                'dompet_id'             => $data['dompet_id'],
                'kegiatan_id'           => $kegiatan->id,
                'user_id'               => auth()->id(),
                'kategori_transaksi_id' => $data['kategori_transaksi_id'],
                'tanggal_transaksi'     => $data['tanggal_transaksi'],
                'jumlah'                => $data['jumlah'],
                'deskripsi'             => $data['deskripsi'] ?? null,
                'status_approval'       => 'PENDING',
                'status_jurnal'         => 'UNMAPPED',
            ]);

            // Upload bukti transaksi
            if (!empty($data['bukti_transaksi'])) {
                foreach ($data['bukti_transaksi'] as $file) {
                    $path = $file->store('bukti_transaksi', 'public');
                    BuktiTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'nama_file'    => $file->getClientOriginalName(),
                        'path_file'    => $path,
                    ]);
                }
            }

            return $transaksi->load('buktiTransaksi');
        });
    }

    public function deleteTransaksi(Transaksi $transaksi): bool|string
    {
        if ($transaksi->status_approval !== 'PENDING') {
            return 'Transaksi yang sudah diproses tidak bisa dihapus';
        }

        if ($transaksi->user_id !== auth()->id()) {
            return 'Anda tidak bisa menghapus transaksi orang lain';
        }

        DB::transaction(function () use ($transaksi) {
            foreach ($transaksi->buktiTransaksi as $bukti) {
                Storage::disk('public')->delete($bukti->path_file);
                $bukti->delete();
            }
            $transaksi->delete();
        });

        return true;
    }

    // ── Edit Transaksi (Panitia setelah REVISION) ──────────────

    public function updateTransaksi(Transaksi $transaksi, array $data): Transaksi
    {
        return DB::transaction(function () use ($transaksi, $data) {
            $transaksi->update([
                'dompet_id'             => $data['dompet_id'],
                'kategori_transaksi_id' => $data['kategori_transaksi_id'],
                'tanggal_transaksi'     => $data['tanggal_transaksi'],
                'jumlah'                => $data['jumlah'],
                'deskripsi'             => $data['deskripsi'] ?? null,
                'status_approval'       => 'PENDING', // reset ke PENDING
                'catatan_revisi'        => null,       // hapus catatan
            ]);

            // Tambah bukti baru kalau ada
            if (!empty($data['bukti_transaksi'])) {
                foreach ($data['bukti_transaksi'] as $file) {
                    $path = $file->store('bukti_transaksi', 'public');
                    BuktiTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'nama_file'    => $file->getClientOriginalName(),
                        'path_file'    => $path,
                    ]);
                }
            }

            // Hapus bukti yang dipilih untuk dihapus
            if (!empty($data['hapus_bukti'])) {
                foreach ($data['hapus_bukti'] as $buktiId) {
                    $bukti = BuktiTransaksi::find($buktiId);
                    if ($bukti && $bukti->transaksi_id === $transaksi->id) {
                        Storage::disk('public')->delete($bukti->path_file);
                        $bukti->delete();
                    }
                }
            }

            return $transaksi->fresh()->load('buktiTransaksi');
        });
    }
}