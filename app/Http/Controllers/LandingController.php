<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        // Hero Banner

        $banners = [
            [
                'type'     => 'image',
                'image'    => asset('images/hero/banner-1.jpg'), 
                'title'    => 'MARI AMBIL BAGIAN<br><span class="text-yellow-400">DALAM KEBAIKAN</span>',
                'subtitle' => '"Barang siapa menunjukkan kepada kebaikan, maka ia mendapatkan pahala seperti orang yang melakukannya." (HR. Muslim)',
                'align'    => 'right',
                'badge'    => 'Program Infaq Aktif',
                'cta'      => [
                    ['label' => 'Donasi Sekarang', 'url' => url('/donasi'), 'style' => 'primary'],
                    ['label' => 'Program Kami', 'url' => url('/program'), 'style' => 'outline'],
                ],
            ],
            [
                'type'     => 'image',
                'image'    => asset('images/hero/banner-2.jpg'),
                'title'    => 'BERSAMA MEMBANGUN<br><span class="text-green-400">MASJID YANG LEBIH BAIK</span>',
                'subtitle' => 'Jadikan masjid sebagai pusat ibadah dan kegiatan umat yang nyaman dan berkah.',
                'align'    => 'left',
                'badge'    => 'Infaq Pembangunan',
                'cta'      => [
                    ['label' => 'Infaq Pembangunan', 'url' => url('/donasi/pembangunan'), 'style' => 'primary'],
                ],
            ],
            [
                'type'     => 'gradient',
                'gradient' => 'bg-gradient-to-br from-green-900 via-green-800 to-emerald-900',
                'title'    => 'TRANSPARANSI<br><span class="text-yellow-400">KEUANGAN MASJID</span>',
                'subtitle' => 'Setiap rupiah yang Anda infaqkan dikelola dengan amanah dan transparan untuk kemaslahatan umat.',
                'align'    => 'left',
                'badge'    => 'Laporan Keuangan Terbuka',
                'cta'      => [
                    ['label' => 'Lihat Laporan', 'url' => url('/laporan'), 'style' => 'primary'],
                    ['label' => 'Tentang Kami', 'url' => url('/tentang-kami'), 'style' => 'outline'],
                ],
            ],
        ];

        // Program Infaq & Shadaqah

        $programInfaq = [
            [
                'title'       => 'Solidaritas untuk Palestina',
                'description' => 'Salurkan kepedulian Anda untuk membantu rakyat Palestina melalui bantuan kemanusiaan, kesehatan, dan kebutuhan dasar.',
                'image'       => asset('images/program/palestina.jpeg'),
                'url'         => url('/donasi/palestina'),
                'category'    => 'Kemanusiaan',
                'progress'    => 72,
                'target'      => 'Rp 50.000.000',
            ],
            [
                'title'       => 'Sumatra Peduli Negeri',
                'description' => 'Bersama kami, saudara-saudara kita di Sumatra yang terdampak bencana membutuhkan bantuan pangan, medis, dan pemulihan kehidupan.',
                'image'       => asset('images/program/sumatra.jpg'),
                'url'         => url('/donasi/sumatra'),
                'category'    => 'Bencana Alam',
                'progress'    => 45,
                'target'      => 'Rp 30.000.000',
            ],
            [
                'title'       => 'Infaq Pembangunan Masjid',
                'description' => 'Mari wujudkan masjid yang nyaman dan berkah melalui infaq terbaik Anda untuk pembangunan dan pengembangan fasilitas ibadah.',
                'image'       => asset('images/program/masjid.jpg'),
                'url'         => url('/donasi/pembangunan'),
                'category'    => 'Pembangunan',
                'progress'    => 60,
                'target'      => 'Rp 200.000.000',
            ],
        ];

        // Program Kegiatan

        $programKegiatan = [
            [
                'title'       => 'Kajian Rutin Mingguan',
                'description' => 'Kajian Islam yang membahas berbagai topik Al-Qur\'an, hadits, dan fiqih yang dipandu oleh ustadz berpengalaman. Terbuka untuk semua kalangan.',
                'icon'        => '📖',
                'tag'         => 'Kajian',
                'tag_class'   => 'bg-green-100 text-green-700',
                'bg_class'    => 'bg-gradient-to-br from-green-500 to-emerald-700',
                'accent_class'=> 'bg-green-500',
                'schedule'    => 'Setiap Ahad, Ba\'da Subuh',
                'time'        => 'Pukul 05:30 - 07:00 WIB',
                'url'         => url('/kegiatan/kajian'),
                'images'      => [], 
            ],
            [
                'title'       => 'Tahsin & Tahfizh Al-Qur\'an',
                'description' => 'Program pembinaan membaca dan menghafal Al-Qur\'an dengan metode yang terstruktur dan sistematis untuk semua usia.',
                'icon'        => '🕌',
                'tag'         => 'Pendidikan',
                'tag_class'   => 'bg-blue-100 text-blue-700',
                'bg_class'    => 'bg-gradient-to-br from-blue-500 to-indigo-700',
                'accent_class'=> 'bg-blue-500',
                'schedule'    => 'Senin, Rabu, Jumat',
                'time'        => 'Pukul 16:00 - 17:30 WIB',
                'url'         => url('/kegiatan/tahsin'),
                'images'      => [],
            ],
            [
                'title'       => 'Kegiatan Sosial & Bakti Sosial',
                'description' => 'Kegiatan sosial yang melibatkan masyarakat sekitar dalam berbagai program bantuan, pemberdayaan, dan silaturahmi.',
                'icon'        => '🤝',
                'tag'         => 'Sosial',
                'tag_class'   => 'bg-amber-100 text-amber-700',
                'bg_class'    => 'bg-gradient-to-br from-amber-400 to-orange-600',
                'accent_class'=> 'bg-amber-500',
                'schedule'    => 'Bulanan (Minggu ke-3)',
                'time'        => 'Pukul 08:00 - 12:00 WIB',
                'url'         => url('/kegiatan/sosial'),
                'images'      => [],
            ],
            [
                'title'       => 'Kegiatan Ramadhan',
                'description' => 'Berbagai kegiatan spesial di bulan suci Ramadhan: tarawih, tadarus, buka bersama, zakat fitrah, dan santunan anak yatim.',
                'icon'        => '🌙',
                'tag'         => 'Ramadhan',
                'tag_class'   => 'bg-purple-100 text-purple-700',
                'bg_class'    => 'bg-gradient-to-br from-purple-600 to-indigo-800',
                'accent_class'=> 'bg-purple-500',
                'schedule'    => 'Setiap Malam Ramadhan',
                'time'        => 'Pukul 19:30 - 22:00 WIB',
                'url'         => url('/kegiatan/ramadhan'),
                'images'      => [],
            ],
        ];

        // Kilas Laporan Keuangan

        $laporan = [
            'periode_awal'  => 'Per Senin, 20 April 2026',
            'periode_akhir' => 'Saldo hari ini (25 April 2026)',
            'saldo_awal'    => 14580000,
            'pemasukan'     => 14580000,
            'pengeluaran'   => 14580000,
            'saldo_akhir'   => 14580000,
        ];

        return view('landing.index', compact('banners', 'programInfaq', 'programKegiatan', 'laporan'));
    }
}