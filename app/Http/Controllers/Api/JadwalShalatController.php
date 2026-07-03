<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class JadwalShalatController extends Controller
{
    /**
     * Daftar kota dengan ID EQuran API v2.
     * Sumber: https://equran.id/api/v2/shalat/kabkota
     */
    protected array $kotaMap = [
        '1101' => ['provinsi' => 'Dki Jakarta',      'kabkota' => 'Kota Jakarta Pusat'],
        '1201' => ['provinsi' => 'Sumatera Utara',   'kabkota' => 'Kota Medan'],
        '1301' => ['provinsi' => 'Jawa Barat',       'kabkota' => 'Kota Bandung'],
        '1401' => ['provinsi' => 'Sumatera Selatan', 'kabkota' => 'Kota Palembang'],
        '1501' => ['provinsi' => 'Jawa Timur',       'kabkota' => 'Kota Surabaya'],
        '1601' => ['provinsi' => 'D.I. Yogyakarta',  'kabkota' => 'Kota Yogyakarta'],
        '1701' => ['provinsi' => 'Jawa Tengah',      'kabkota' => 'Kota Semarang'],
        '1801' => ['provinsi' => 'Bali',             'kabkota' => 'Kota Denpasar'],
        '2001' => ['provinsi' => 'Sulawesi Selatan', 'kabkota' => 'Kota Makassar'],
    ];

    /**
     * GET /api/jadwal-shalat
     * Query params:
     *   - kota  : ID kota (default: 1301 = Bandung)
     *   - tanggal: format YYYY-MM-DD (default: hari ini)
     */
    public function index(Request $request)
    {
        $provinsi = $request->query('provinsi', 'Jawa Barat');
        $kabkota  = $request->query('kabkota', 'Kota Bandung');
        $tanggal  = $request->query('tanggal', now()->format('Y-m-d'));

        [$tahun, $bulan] = explode('-', $tanggal);

        $cacheKey = 'jadwal_shalat_bulan_' . md5($provinsi . $kabkota) . "_{$tahun}_{$bulan}";

        $jadwalBulan = Cache::get($cacheKey);

        if (!$jadwalBulan) {
            $jadwalBulan = $this->fetchBulanFromEquran($provinsi, $kabkota, (int) $tahun, (int) $bulan);
            if ($jadwalBulan) {
                Cache::put($cacheKey, $jadwalBulan, 86400);
            }
        }

        $data = $jadwalBulan
            ? collect($jadwalBulan)->firstWhere('tanggal_lengkap', $tanggal)
            : null;

        if ($data) {
            $data = [
                'tanggal' => \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y'),
                'imsak'   => $data['imsak']   ?? '--:--',
                'subuh'   => $data['subuh']   ?? '--:--',
                'terbit'  => $data['terbit']  ?? '--:--',
                'dhuha'   => $data['dhuha']   ?? '--:--',
                'dzuhur'  => $data['dzuhur']  ?? '--:--',
                'ashar'   => $data['ashar']   ?? '--:--',
                'maghrib' => $data['maghrib'] ?? '--:--',
                'isya'    => $data['isya']    ?? '--:--',
            ];
        } else {
            $data = $this->getFallbackData($tanggal);
        }

        return response()->json(['status' => 'ok', 'data' => $data]);
    }

    /**
     * Ambil data jadwal dari API MyQuran.
     * Endpoint: GET https://equran.id/api/v2/shalat/kabkota
     */
    protected function fetchBulanFromEquran(string $provinsi, string $kabkota, int $tahun, int $bulan): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://equran.id/api/v2/shalat', [
                    'provinsi' => $provinsi,
                    'kabkota'  => $kabkota,
                    'bulan'    => $bulan,
                    'tahun'    => $tahun,
                ]);

            if (!$response->successful()) return null;

            $body = $response->json();
            if (($body['code'] ?? null) !== 200 || empty($body['data']['jadwal'])) return null;

            return $body['data']['jadwal'];
        } catch (\Exception $e) {
            \Log::error("fetchBulanFromEquran: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Data fallback jika API tidak dapat diakses.
     * Data perkiraan untuk wilayah Bandung/WIB.
     */
    protected function getFallbackData(string $tanggal): array
    {
        $dt         = \Carbon\Carbon::parse($tanggal);
        $namaBulan  = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $namaHari   = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $tanggalFmt = $namaHari[$dt->dayOfWeek] . ', ' . $dt->day . ' ' . $namaBulan[$dt->month] . ' ' . $dt->year;

        return [
            'tanggal' => $tanggalFmt . ' (data fallback)',
            'imsak'   => '04:23',
            'subuh'   => '04:33',
            'terbit'  => '05:52',
            'dhuha'   => '06:18',
            'dzuhur'  => '11:57',
            'ashar'   => '15:17',
            'maghrib' => '17:57',
            'isya'    => '19:09',
        ];
    }

    /**
     * GET /api/jadwal-shalat/kota
     * Mengembalikan daftar kota yang tersedia.
     */
    public function listKota()
    {
        $data = Cache::get('daftar_provinsi_kabkota');

        if (!$data) {
            // fallback
            $data = collect($this->kotaMap)
                ->map(fn($k, $id) => [
                    'provinsi' => $k['provinsi'],
                    'kabkota'  => $k['kabkota'],
                    'label'    => "{$k['kabkota']}, {$k['provinsi']}",
                ])
                ->values();
        }

        return response()->json(['status' => 'ok', 'data' => $data]);
    }
}