<?php

namespace App\Http\Controllers\ManajemenAkses;

use App\Http\Controllers\Controller;
use App\Models\Peran;
use App\Models\Pengguna;
use App\Notifications\AkunDibuatNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PenggunaController extends Controller
{
    public function tampilkanDaftarPengguna(Request $request)
    {
        $stats = [
            'total'       => Pengguna::count(),
            'aktif'       => Pengguna::where('status', 'active')->count(),
            'tidak_aktif' => Pengguna::where('status', 'inactive')->count(),
        ];

        if ($request->get('stats_only')) {
            return response()->json(['stats' => $stats]);
        }

        $query = Pengguna::with('peran');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }
        if ($request->filled('peran')) {
            $query->whereHas('peran', fn($q) => $q->where('nama_peran', $request->peran));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 10;
        $pengguna   = $query->paginate($perPage)->withQueryString();
        $peran   = Peran::all();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('pages.manajemen-akses.pengguna.table', compact('pengguna', 'peran'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('pages.manajemen-akses.pengguna.index', compact('pengguna', 'peran', 'stats'));
    }

    public function tampilkanFormTambahPengguna(Request $request)
    {
        $peran = Peran::all();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.manajemen-akses.pengguna.create', compact('peran'))->render(),
            ]);
        }

        return view('pages.manajemen-akses.pengguna.create', compact('peran'));
    }

    public function simpanPenggunaBaru(Request $request)
    {
        $validated = $request->validate([
            'nama'     => 'required|string|max:100',
            'email'    => 'required|email|unique:pengguna,email',
            'password' => 'required|string|min:8|confirmed',
            'peran_id'  => 'required|exists:peran,id',
            'status'   => 'required|in:active,inactive',
        ], [
            'nama.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah digunakan.',
            'password.required'  => 'Password awal wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'peran_id.required'   => 'Peran wajib dipilih.',
            'peran_id.exists'     => 'Peran yang dipilih tidak valid.',
        ]);

        // password    -> hash untuk autentikasi (tidak bisa dibaca balik)
        // password_awal -> disimpan TERENKRIPSI (cast 'encrypted') agar admin
        //                     tetap dapat melihat password awal yang ia buat.
        // Email TIDAK dikirim otomatis: admin memverifikasi hak_akses dulu, lalu
        // mengirim kredensial manual lewat ikon email di tabel.
        $pengguna = Pengguna::create([
            'nama'             => $validated['nama'],
            'email'            => $validated['email'],
            'password'         => Hash::make($validated['password']),
            'password_awal' => $validated['password'],
            'peran_id'          => $validated['peran_id'],
            'status'           => $validated['status'],
        ]);

        $message = 'Pengguna berhasil dibuat. Periksa kembali hak_akses peran-nya, '
            . 'lalu klik ikon email pada baris pengguna untuk mengirim kredensial.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('dashboard.pengguna.index')->with('success', $message);
    }

    /**
     * Kirim kredensial (email + password awal) ke pengguna via email, lalu tandai
     * waktu pengirimannya. Dipicu manual oleh admin dari ikon email di tabel,
     * SETELAH admin memverifikasi hak_akses peran pengguna tersebut.
     */
    public function kirimKredensialPengguna(Request $request, Pengguna $pengguna)
    {
        $plainPassword = $pengguna->password_awal; // didekripsi otomatis oleh cast

        if (empty($plainPassword)) {
            $msg = 'Password awal pengguna ini tidak tersedia (mis. dibuat sebelum fitur ini). '
                . 'Buat ulang pengguna dengan password awal terlebih dahulu.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('dashboard.pengguna.index')->with('error', $msg);
        }

        $sudahDikirim = $pengguna->credentialsSent();

        try {
            $pengguna->notify(new AkunDibuatNotification($pengguna->nama, $plainPassword));
            $pengguna->forceFill(['kredensial_dikirim_pada' => now()])->save();
        } catch (\Throwable $e) {
            \Log::error('Gagal kirim kredensial pengguna #' . $pengguna->id . ': ' . $e->getMessage());
            $msg = 'Email kredensial gagal terkirim. Pastikan konfigurasi mail (.env) sudah benar.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }

            return redirect()->route('dashboard.pengguna.index')->with('error', $msg);
        }

        $msg = ($sudahDikirim ? 'Kredensial berhasil dikirim ulang ke ' : 'Kredensial berhasil dikirim ke ')
            . $pengguna->email . '.';

        if ($request->ajax()) {
            return response()->json([
                'success'          => true,
                'message'          => $msg,
                'credentials_sent' => true,
            ]);
        }

        return redirect()->route('dashboard.pengguna.index')->with('success', $msg);
    }

    public function tampilkanFormEditPengguna(Request $request, Pengguna $pengguna)
    {
        $pengguna->load('peran');
        $peran = Peran::all();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.manajemen-akses.pengguna.edit', compact('pengguna', 'peran'))->render(),
            ]);
        }

        return view('pages.manajemen-akses.pengguna.edit', compact('pengguna', 'peran'));
    }

    public function perbaruiPengguna(Request $request, Pengguna $pengguna)
    {
        $validated = $request->validate([
            'nama'    => 'required|string|max:100',
            'email'   => 'required|email|unique:pengguna,email,' . $pengguna->id,
            'peran_id' => 'required|exists:peran,id',
            'status'  => 'required|in:active,inactive',
        ], [
            'nama.required'    => 'Nama wajib diisi.',
            'email.required'   => 'Email wajib diisi.',
            'email.email'      => 'Format email tidak valid.',
            'email.unique'     => 'Email sudah digunakan.',
            'peran_id.required' => 'Peran wajib dipilih.',
            'peran_id.exists'   => 'Peran yang dipilih tidak valid.',
        ]);

        $pengguna->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil diupdate.',
            ]);
        }

        return redirect()->route('dashboard.pengguna.index')
            ->with('success', 'Pengguna berhasil diupdate.');
    }

    public function tampilkanKonfirmasiHapusPengguna(Request $request, Pengguna $pengguna)
    {
        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.manajemen-akses.pengguna.delete', compact('pengguna'))->render(),
            ]);
        }

        return redirect()->route('dashboard.pengguna.index');
    }

    public function hapusPengguna(Request $request, Pengguna $pengguna)
    {
        // Tolak hapus jika pengguna sudah mencatat transaksi.
        // Sebutkan jumlahnya & sarankan nonaktifkan agar jejak audit tetap utuh.
        $jumlahTransaksi = \App\Models\Transaksi::where('pengguna_id', $pengguna->id)->count();
        if ($jumlahTransaksi > 0) {
            $msg = 'Pengguna tidak dapat dihapus karena sudah memiliki ' . $jumlahTransaksi
                . ' riwayat transaksi. Untuk menjaga jejak audit, nonaktifkan akun ini '
                . '(buka Edit pengguna lalu ubah status menjadi Nonaktif) sebagai gantinya.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('dashboard.pengguna.index')->with('error', $msg);
        }

        // Cegah penghapusan diam-diam: kolom kegiatan.panitia_id memakai
        // cascadeOnDelete, sehingga menghapus pengguna yang menjadi panitia akan
        // ikut menghapus kegiatannya (kehilangan data). Blokir & beri pesan jelas
        // beserta daftar kegiatan terkait agar admin tahu apa yang harus diganti.
        $kegiatanPanitia = \App\Models\Kegiatan::where('panitia_id', $pengguna->id)
            ->pluck('nama_kegiatan');
        if ($kegiatanPanitia->isNotEmpty()) {
            $msg = 'Pengguna tidak dapat dihapus karena masih terdaftar sebagai panitia pada '
                . $kegiatanPanitia->count() . ' kegiatan: ' . $kegiatanPanitia->implode(', ')
                . '. Ganti panitia kegiatan tersebut terlebih dahulu, atau nonaktifkan akun ini.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('dashboard.pengguna.index')->with('error', $msg);
        }

        try {
            $pengguna->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::warning('Gagal menghapus pengguna karena relasi terkait', [
                'pengguna_id' => $pengguna->id,
                'error'   => $e->getMessage(),
            ]);

            $msg = 'Pengguna tidak dapat dihapus karena masih tertaut dengan data lain (mis. menjadi panitia kegiatan).';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('dashboard.pengguna.index')->with('error', $msg);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil dihapus.',
            ]);
        }

        return redirect()->route('dashboard.pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}