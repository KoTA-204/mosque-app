<?php

namespace App\Services\Operasional;

use App\Models\BuktiTransaksi;
use App\Models\Dompet;
use App\Models\KategoriTransaksi;
use App\Models\Kegiatan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransaksiKegiatanService
{
    // ── Kegiatan ───────────────────────────────────────────────
    public function getDaftarKegiatan(?string $search = null, ?string $status = null, int $perPage = 10)
    {
        return Kegiatan::with('panitia')
            ->withCount('transaksi')
            ->withCount([
                'transaksi as transaksi_pending_count' => fn ($q) =>
                    $q->where('status_approval', 'PENDING'),
            ])
            ->when(auth()->user()->hasRole('panitia-kegiatan-khusus'), fn ($q) =>
                $q->where('panitia_id', auth()->id()))
            // 'like' agar portable. Jika pakai PostgreSQL boleh ganti 'ilike'.
            ->when($search, fn ($q) =>
                $q->where('nama_kegiatan', 'like', "%{$search}%"))
            ->when($status, fn ($q) =>
                $q->where('status', strtoupper($status)))
            ->orderByRaw("CASE status WHEN 'AKTIF' THEN 1 WHEN 'DITUTUP' THEN 2 ELSE 3 END")
            ->orderBy('tanggal_mulai', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function hitungRingkasanKegiatan(): array
    {
        $query = Kegiatan::query()
            ->when(auth()->user()->hasRole('panitia-kegiatan-khusus'), fn ($q) =>
                $q->where('panitia_id', auth()->id()));

        return [
            'total'   => (clone $query)->count(),
            'aktif'   => (clone $query)->where('status', Kegiatan::STATUS_AKTIF)->count(),
            'pending' => Transaksi::whereHas('kegiatan', fn ($q) =>
                    $q->when(auth()->user()->hasRole('panitia-kegiatan-khusus'), fn ($q) =>
                        $q->where('panitia_id', auth()->id())))
                ->where('status_approval', 'PENDING')
                ->count(),
        ];
    }

    public function hitungPorsiAnggaran(Kegiatan $kegiatan): int
    {
        // Sumber tunggal kebenaran ada di model, agar konsisten dengan
        // perhitungan di halaman index (hanya PEMASUKAN APPROVED).
        return $kegiatan->persenRealisasiPemasukan();
    }

    // ── Transaksi ────────────────────────────────────────────
    public function getTransaksiPerKegiatan(Kegiatan $kegiatan, ?string $search = null, ?string $jenis = null, ?string $status = null, int $perPage = 10)
    {
        return $kegiatan->transaksi()
            ->with(['dompet', 'kategoriTransaksi', 'user', 'buktiTransaksi'])
            ->when($search, fn ($q) =>
                $q->where('deskripsi', 'like', "%{$search}%"))
            ->when($jenis, fn ($q) =>
                $q->where('jenis_transaksi', strtoupper($jenis)))
            ->when($status, fn ($q) =>
                $q->where('status_approval', strtoupper($status)))
            ->orderBy('tanggal_transaksi', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getDetailTransaksi(Transaksi $transaksi): Transaksi
    {
        return $transaksi->load('dompet', 'kategoriTransaksi', 'user', 'kegiatan', 'buktiTransaksi');
    }

    public function getDaftarDompet()
    {
        return Dompet::orderBy('nama_dompet')->get();
    }

    public function getDaftarKategori()
    {
        return KategoriTransaksi::orderBy('nama_kategori')->get();
    }

    public function buatKodeTransaksi(): string
    {
        $year  = now()->year;
        $count = Transaksi::whereYear('created_at', $year)->count() + 1;

        return 'TRX-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function simpanTransaksiKegiatan(Kegiatan $kegiatan, array $data): Transaksi
    {
        return DB::transaction(function () use ($kegiatan, $data) {
            $transaksi = Transaksi::create([
                'dompet_id'             => $data['dompet_id'],
                'kegiatan_id'           => $kegiatan->id,
                'user_id'               => auth()->id(),
                'kategori_transaksi_id' => $data['kategori_transaksi_id'],
                'tanggal_transaksi'     => $data['tanggal_transaksi'],
                'jenis_transaksi'       => $data['jenis_transaksi'],
                'jumlah'                => $data['jumlah'],
                'deskripsi'             => $data['deskripsi'] ?? null,
                'status_approval'       => 'PENDING',
                'status_jurnal'         => 'UNMAPPED',
            ]);

            $this->simpanBuktiTransaksi($transaksi, $data['bukti_transaksi'] ?? []);

            return $transaksi->load('buktiTransaksi');
        });
    }

    public function perbaruiTransaksiKegiatan(Transaksi $transaksi, array $data): Transaksi
    {
        return DB::transaction(function () use ($transaksi, $data) {
            $transaksi->update([
                'dompet_id'             => $data['dompet_id'],
                'kategori_transaksi_id' => $data['kategori_transaksi_id'],
                'tanggal_transaksi'     => $data['tanggal_transaksi'],
                'jenis_transaksi'       => $data['jenis_transaksi'],
                'jumlah'                => $data['jumlah'],
                'deskripsi'             => $data['deskripsi'] ?? null,
                // setelah revisi diperbaiki, kembalikan ke PENDING untuk ditinjau ulang
                'status_approval'       => 'PENDING',
                'catatan'               => null, // ✅ diperbaiki (sebelumnya 'catatan_revisi' yg tak ada)
            ]);

            // Hapus bukti lama yang dipilih
            foreach ($data['hapus_bukti'] ?? [] as $buktiId) {
                $bukti = BuktiTransaksi::where('transaksi_id', $transaksi->id)->find($buktiId);
                if ($bukti) {
                    Storage::delete($bukti->path_file);
                    $bukti->delete();
                }
            }

            $this->simpanBuktiTransaksi($transaksi, $data['bukti_transaksi'] ?? []);

            return $transaksi->fresh()->load('buktiTransaksi');
        });
    }

    public function hapusTransaksiKegiatan(Transaksi $transaksi): bool|string
    {
        if (! $transaksi->bisaDiedit()) {
            return 'Transaksi yang sudah diproses tidak bisa dihapus';
        }
        if ($transaksi->user_id !== auth()->id()) {
            return 'Anda tidak bisa menghapus transaksi orang lain';
        }

        DB::transaction(function () use ($transaksi) {
            foreach ($transaksi->buktiTransaksi as $bukti) {
                Storage::delete($bukti->path_file);
                $bukti->delete();
            }
            $transaksi->delete();
        });

        return true;
    }

    // Helper privat untuk menyimpan file bukti
    private function simpanBuktiTransaksi(Transaksi $transaksi, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('bukti_transaksi');
            BuktiTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'nama_file'    => $file->getClientOriginalName(),
                'path_file'    => $path,
            ]);
        }
    }
}
