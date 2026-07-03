<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kegiatan;
use App\Services\DashboardService;

class LandingController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}
    
    public function index()
    {
        // Hero Banner

        $banners = [
            [
                'type'     => 'image',
                'image'    => asset('images/hero/banner-1.jpg'),
                'title'    => 'PENGELOLAAN KEUANGAN<br><span class="text-yellow-400">MASJID YANG AMANAH</span>',
                'subtitle' => 'Setiap pemasukan dan pengeluaran masjid dicatat, dikelola, dan dilaporkan secara terbuka agar dapat dipertanggungjawabkan kepada jamaah.',
                'align'    => 'right',
                'badge'    => 'Transparansi Keuangan',
                'cta'      => [
                    ['label' => 'Lihat Transparansi Keuangan', 'url' => route('laporan-keuangan.index'), 'style' => 'primary'],
                ],
            ],
            [
                'type'     => 'image',
                'image'    => asset('images/hero/banner-2.jpg'),
                'title'    => 'PENCATATAN & PELAPORAN<br><span class="text-green-400">TRANSAKSI YANG TERTIB</span>',
                'subtitle' => 'Integrasi pencatatan transaksi dengan pelaporan keuangan yang rapi dan akurat, dengan menggunakan standar ISAK 335.',
                'align'    => 'left',
                'badge'    => 'Pencatatan Transaksi',
            
            ],
            [
                'type'     => 'gradient',
                'gradient' => 'bg-gradient-to-br from-green-900 via-green-800 to-emerald-900',
                'title'    => 'LAPORAN KEUANGAN<br><span class="text-yellow-400">TERBUKA UNTUK UMAT</span>',
                'subtitle' => 'Akses ringkasan dashboard publik dan laporan keuangan masjid, mulai dari posisi keuangan, arus kas, hingga program kegiatan, kapan saja.',
                'align'    => 'left',
                'badge'    => 'Laporan Keuangan Terbuka',
                'cta'      => [
                    ['label' => 'Lihat Laporan Keuangan', 'url' => route('laporan.posisi-keuangan'), 'style' => 'primary'],
                    ['label' => 'Program Kegiatan', 'url' => '#program-kegiatan', 'style' => 'outline'],
                ],
            ],
        ];

        // Program Kegiatan (diambil dari data nyata pada tabel `kegiatan`)
        //
        // Menampilkan kegiatan yang MASIH AKTIF, ditambah kegiatan yang BARU
        // saja ditutup (tanggal pelaksanaannya dalam 30 hari terakhir).
        // - Ubah nilai 30 di bawah untuk mengatur seberapa lama kegiatan yang
        //   sudah ditutup masih ikut ditampilkan.
        // - Untuk hanya menampilkan yang aktif: sisakan ->aktif() saja.
        $batasTutup = now()->subDays(30)->toDateString();

        $programKegiatan = Kegiatan::with('panitia')
            ->where(function ($q) use ($batasTutup) {
                $q->where('status', Kegiatan::STATUS_AKTIF)
                  ->orWhere(function ($q2) use ($batasTutup) {
                      $q2->where('status', Kegiatan::STATUS_DITUTUP)
                         ->whereRaw('COALESCE(tanggal_selesai, tanggal_mulai) >= ?', [$batasTutup]);
                  });
            })
            ->orderByRaw("CASE WHEN status = 'AKTIF' THEN 0 ELSE 1 END")
            ->orderByDesc('tanggal_mulai')
            ->take(6)
            ->get();

        // Kilas Laporan Keuangan

        $laporan = $this->dashboard->ringkasanPublik();

        return view('landing.index', compact('banners', 'programKegiatan', 'laporan'));
    }
}