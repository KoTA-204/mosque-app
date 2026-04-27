<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class JadwalShalatController extends Controller
{
    /**
     * Daftar kota dengan ID MyQuran API v2.
     * Sumber: https://api.myquran.com/v2/sholat/kota/semua
     */
    protected array $kotaMap = [
        '1101' => 'JAKARTA',
        '1201' => 'MEDAN',
        '1301' => 'BANDUNG',
        '1401' => 'PALEMBANG',
        '1501' => 'SURABAYA',
        '1601' => 'YOGYAKARTA',
        '1701' => 'SEMARANG',
        '1801' => 'DENPASAR',
        '2001' => 'MAKASSAR',
    ];

    /**
     * GET /api/jadwal-shalat
     * Query params:
     *   - kota  : ID kota (default: 1301 = Bandung)
     *   - tanggal: format YYYY-MM-DD (default: hari ini)
     */
    public function index(Request $request)
    {
        $kotaId  = $request->query('kota', '1301');
        $tanggal = $request->query('tanggal', now()->format('Y-m-d'));

        // Validasi kota
        if (!array_key_exists($kotaId, $this->kotaMap)) {
            $kotaId = '1301';
        }

        // Cache key
        $cacheKey = "jadwal_shalat_{$kotaId}_{$tanggal}";

        $data = Cache::remember($cacheKey, 3600, function () use ($kotaId, $tanggal) {
            return $this->fetchFromMyQuran($kotaId, $tanggal);
        });

        if (!$data) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil jadwal shalat. Silakan coba lagi.',
            ], 503);
        }

        return response()->json([
            'status' => 'ok',
            'data'   => $data,
        ]);
    }

    /**
     * Ambil data jadwal dari API MyQuran.
     * Endpoint: GET https://api.myquran.com/v2/sholat/jadwal/{kotaId}/{tahun}/{bulan}/{hari}
     */
    protected function fetchFromMyQuran(string $kotaId, string $tanggal): ?array
    {
        [$tahun, $bulan, $hari] = explode('-', $tanggal);

        try {
            $url = "https://api.myquran.com/v2/sholat/jadwal/{$kotaId}/{$tahun}/{$bulan}/{$hari}";

            $response = Http::timeout(10)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($url);

            if (!$response->successful()) {
                \Log::warning("MyQuran API error: HTTP {$response->status()} for kota={$kotaId} tanggal={$tanggal}");
                return $this->getFallbackData($tanggal);
            }

            $body = $response->json();

            if (!isset($body['status']) || $body['status'] !== true) {
                return $this->getFallbackData($tanggal);
            }

            $jadwal = $body['data']['jadwal'] ?? null;

            if (!$jadwal) {
                return $this->getFallbackData($tanggal);
            }

            $dt         = \Carbon\Carbon::parse($tanggal);
            $namaBulan  = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $namaHari   = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $tanggalFmt = $namaHari[$dt->dayOfWeek] . ', ' . $dt->day . ' ' . $namaBulan[$dt->month] . ' ' . $dt->year;

            return [
                'tanggal' => $tanggalFmt,
                'imsak'   => $jadwal['imsak']   ?? '--:--',
                'subuh'   => $jadwal['subuh']   ?? '--:--',
                'terbit'  => $jadwal['terbit']  ?? '--:--',
                'dhuha'   => $jadwal['dhuha']   ?? '--:--',
                'dzuhur'  => $jadwal['dzuhur']  ?? '--:--',
                'ashar'   => $jadwal['ashar']   ?? '--:--',
                'maghrib' => $jadwal['maghrib'] ?? '--:--',
                'isya'    => $jadwal['isya']    ?? '--:--',
            ];
        } catch (\Exception $e) {
            \Log::error("JadwalShalat fetchFromMyQuran error: {$e->getMessage()}");
            return $this->getFallbackData($tanggal);
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
        $kota = Cache::remember('daftar_kota_shalat', 86400, function () {
            try {
                $res = Http::timeout(10)->get('https://api.myquran.com/v2/sholat/kota/semua');
                if ($res->successful() && isset($res->json()['data'])) {
                    return collect($res->json()['data'])
                        ->map(fn($k) => ['id' => $k['id'], 'nama' => $k['lokasi']])
                        ->values()
                        ->toArray();
                }
            } catch (\Exception $e) {}

            // Fallback ke daftar statis
            return collect($this->kotaMap)
                ->map(fn($nama, $id) => ['id' => $id, 'nama' => $nama])
                ->values()
                ->toArray();
        });

        return response()->json(['status' => 'ok', 'data' => $kota]);
    }
}