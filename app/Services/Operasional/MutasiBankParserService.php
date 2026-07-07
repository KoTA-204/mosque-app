<?php

namespace App\Services\Operasional;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\Transaksi;
use Smalot\PdfParser\Parser as PdfParser;

class MutasiBankParserService
{
    private const HEADERS = [
        'BSI' => [
            'no'              => 'no',
            'waktu_transaksi' => 'waktu transaksi',
            'no_referensi'    => 'no. referensi',
            'nama_pengirim'   => 'nama pengirim',
            'bank_pengirim'   => 'bank pengirim',
            'nama_penerima'   => 'nama penerima',
            'bank_penerima'   => 'bank penerima',
            'deskripsi'       => 'deskripsi',
            'debet'           => 'debet',
            'kredit'          => 'kredit',
            'saldo_riil'      => 'saldo riil',
            'kode'            => 'kode',
        ],
        'BRI' => [
            'no'              => 'no',
            'tanggal'         => 'tanggal',
            'no_referensi'    => 'no. referensi',
            'deskripsi'       => 'keterangan',
            'debet'           => 'debet',
            'kredit'          => 'kredit',
            'saldo_riil'      => 'saldo',
        ],

        // Ekspor mutasi BRImo/Internet Banking BRI dalam bentuk CSV.
        // Tidak punya kolom nomor referensi eksplisit maupun baris metadata
        // di atas header, sehingga dipetakan sebagai profil terpisah.
        'BRI_CSV' => [
            'no'              => 'id',
            'tanggal'         => 'tgl_tran',
            'seq'             => 'seq',
            'deskripsi'       => 'desk_tran',
            'debet'           => 'mutasi_debet',
            'kredit'          => 'mutasi_kredit',
            'saldo_riil'      => 'saldo_akhir_mutasi',
        ],
    ];

    /**
     * Beberapa bank punya lebih dari satu kemungkinan format file
     * (mis. laporan Excel resmi vs ekspor CSV internet banking).
     * Setiap kandidat dicoba berurutan sampai salah satu cocok.
     */
    private const BANK_FORMAT_ALIASES = [
        'BSI' => ['BSI'],
        'BRI' => ['BRI', 'BRI_CSV'],
    ];

    /**
     * Kolom yang dijadikan penanda untuk mendeteksi baris header,
     * per profil format (bukan per bank).
     */
    private const HEADER_TARGETS = [
        'BSI'     => ['waktu transaksi', 'no. referensi'],
        'BRI'     => ['tanggal'],
        'BRI_CSV' => ['tgl_tran', 'desk_tran'],
    ];

    /**
     * Parse file Excel mutasi bank.
     *
     * @return array{ rows: array, meta: array, errors: array }
     */
    public function uraikanFileMutasiBank(UploadedFile $file, string $bank = 'BSI', ?string $jenisTransaksi = null): array
    {
        $bank = strtoupper($bank);

        if (!isset(self::BANK_FORMAT_ALIASES[$bank])) {
            return ['rows' => [], 'meta' => [], 'errors' => ["Bank '$bank' tidak didukung."]];
        }

        // PDF (mis. e-Statement BRImo) tidak berbentuk grid seperti
        // Excel/CSV, sehingga tidak bisa dibaca lewat PhpSpreadsheet.
        // Dialihkan ke parser berbasis teks yang terpisah.
        $ekstensi = strtolower($file->getClientOriginalExtension());
        if ($ekstensi === 'pdf') {
            if ($bank !== 'BRI') {
                return [
                    'rows'   => [],
                    'meta'   => [],
                    'errors' => ["Impor PDF saat ini hanya didukung untuk bank BRI (format e-Statement BRImo)."],
                ];
            }

            return $this->uraikanFilePdfBri($file, $jenisTransaksi);
        }

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $rawRows     = $sheet->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return ['rows' => [], 'meta' => [], 'errors' => ['File tidak dapat dibaca: ' . $e->getMessage()]];
        }

        // Bank tertentu (mis. BRI) punya lebih dari satu kemungkinan format
        // file (laporan Excel resmi vs ekspor CSV internet banking).
        // Coba tiap kandidat sampai salah satu cocok dengan isi file.
        $headerRowIdx = null;
        $formatKey    = null;

        foreach (self::BANK_FORMAT_ALIASES[$bank] as $candidate) {
            $idx = $this->cariBarisHeader($rawRows, $candidate);
            if ($idx !== null) {
                $headerRowIdx = $idx;
                $formatKey    = $candidate;
                break;
            }
        }

        if ($headerRowIdx === null) {
            return [
                'rows'   => [],
                'meta'   => [],
                'errors' => ['Struktur file tidak dikenali. Pastikan format file sesuai mutasi bank ' . $bank . '.'],
            ];
        }

        $headerMap = $this->petakanKolomHeader($rawRows[$headerRowIdx], $formatKey);
        \Log::debug('Header row raw:', $rawRows[$headerRowIdx]);
        \Log::debug('Header map result (format: ' . $formatKey . '):', $headerMap);
        $meta      = $this->uraikanMetadata($rawRows, $headerRowIdx);

        $rows   = [];
        $errors = [];

        $existingRefs = Transaksi::whereNotNull('no_referensi')
            ->pluck('no_referensi')
            ->toArray();

        for ($i = $headerRowIdx + 1; $i < count($rawRows); $i++) {
            $row = $rawRows[$i];

            if ($this->apakahBarisKosong($row)) break;

            try {
                $parsed = $this->uraikanBarisTransaksi($row, $headerMap, $formatKey);

                $parsed['is_duplikat'] = in_array($parsed['no_referensi'], $existingRefs);
                $parsed['is_jenis_mismatch'] = $jenisTransaksi !== null
                    && $parsed['jenis_transaksi'] !== strtoupper($jenisTransaksi);

                $rows[] = $parsed;
            } catch (\Throwable $e) {
                $errors[] = "Baris " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        return ['rows' => $rows, 'meta' => $meta, 'errors' => $errors];
    }

    /**
     * Parse e-Statement BRImo (PDF) mutasi bank BRI.
     *
     * Tidak seperti Excel/CSV yang berbentuk grid rapi, teks hasil ekstraksi
     * PDF ini berbentuk baris demi baris tanpa kolom eksplisit, dan satu
     * transaksi bisa "terpecah" jadi beberapa baris fisik ketika deskripsinya
     * panjang. Header kolom & footer halaman juga berulang di tiap halaman.
     *
     * Pendekatan: setiap baris yang diawali pola "dd/mm/yy hh:mm:ss" dianggap
     * sebagai AWAL transaksi baru. Baris-baris sesudahnya digabung ke baris
     * itu sampai ditemukan 3 angka nominal berurutan di akhir gabungan teks
     * (debet, kredit, saldo) — pada titik itu transaksi dianggap "selesai".
     * Baris di luar pola ini (header kolom, watermark, info nasabah, dsb)
     * diabaikan karena tidak pernah membuka atau menyambung transaksi apa pun.
     *
     * @return array{ rows: array, meta: array, errors: array }
     */
    private function uraikanFilePdfBri(UploadedFile $file, ?string $jenisTransaksi = null): array
    {
        try {
            $parser   = new PdfParser();
            $document = $parser->parseFile($file->getRealPath());
            $fullText = $document->getText();
        } catch (\Throwable $e) {
            return ['rows' => [], 'meta' => [], 'errors' => ['File PDF tidak dapat dibaca: ' . $e->getMessage()]];
        }

        $meta = $this->uraikanMetadataPdfBri($fullText);

        $lines = preg_split('/\r\n|\r|\n/', $fullText);
        $lines = array_values(array_filter(array_map('trim', $lines), fn($l) => $l !== ''));

        $polaAwalBaris   = '/^(\d{2}\/\d{2}\/\d{2})\s+(\d{2}:\d{2}:\d{2})\s*(.*)$/';
        $polaTigaNominal = '/([\d]{1,3}(?:[.,]\d{3})*[.,]\d{2})\s+([\d]{1,3}(?:[.,]\d{3})*[.,]\d{2})\s+([\d]{1,3}(?:[.,]\d{3})*[.,]\d{2})\s*$/';

        $rows       = [];
        $errors     = [];
        $bufferBaris = [];
        $batasBarisTerbuka = 6; 

        $existingRefs = Transaksi::whereNotNull('no_referensi')
            ->pluck('no_referensi')
            ->toArray();

        $tutupTransaksi = function () use (&$bufferBaris, &$rows, &$errors, $polaTigaNominal, $existingRefs, $jenisTransaksi) {
            if (empty($bufferBaris)) return;

            $gabungan = implode(' ', $bufferBaris);
            $bufferBaris = [];

            if (!preg_match($polaTigaNominal, $gabungan, $mNominal)) {
                $errors[] = 'Baris tidak dapat diuraikan (nominal tidak ditemukan): ' . $gabungan;
                return;
            }

            try {
                $parsed = $this->uraikanBarisTransaksiPdfBri($gabungan, $mNominal);
                $parsed['is_duplikat'] = in_array($parsed['no_referensi'], $existingRefs);
                $parsed['is_jenis_mismatch'] = $jenisTransaksi !== null
                    && $parsed['jenis_transaksi'] !== strtoupper($jenisTransaksi);

                $rows[] = $parsed;
            } catch (\Throwable $e) {
                $errors[] = 'Gagal menguraikan baris "' . $gabungan . '": ' . $e->getMessage();
            }
        };

        foreach ($lines as $line) {
            $isAwalBaris = preg_match($polaAwalBaris, $line);

            if ($isAwalBaris) {
                // Transaksi sebelumnya belum "ditutup" (tak ketemu 3 nominal)
                // sebelum baris tanggal baru muncul → anggap gagal, catat lalu lanjut.
                if (!empty($bufferBaris)) {
                    $errors[] = 'Baris tidak dapat diuraikan (nominal tidak ditemukan): ' . implode(' ', $bufferBaris);
                    $bufferBaris = [];
                }
                $bufferBaris[] = $line;
            } elseif (!empty($bufferBaris)) {
                $bufferBaris[] = $line;
                if (count($bufferBaris) > $batasBarisTerbuka) {
                    $errors[] = 'Baris tidak dapat diuraikan (terlalu banyak baris sambungan): ' . implode(' ', $bufferBaris);
                    $bufferBaris = [];
                    continue;
                }
            } else {
                continue;
            }

            $gabunganSaatIni = implode(' ', $bufferBaris);
            if (preg_match($polaTigaNominal, $gabunganSaatIni)) {
                $tutupTransaksi();
            }
        }

        // Sisa buffer di akhir file yang tak pernah tertutup 3 nominal.
        if (!empty($bufferBaris)) {
            $errors[] = 'Baris tidak dapat diuraikan (nominal tidak ditemukan): ' . implode(' ', $bufferBaris);
        }

        return ['rows' => $rows, 'meta' => $meta, 'errors' => $errors];
    }

    private function uraikanBarisTransaksiPdfBri(string $gabungan, array $mNominal): array
    {
        if (!preg_match('/^(\d{2}\/\d{2}\/\d{2})\s+(\d{2}:\d{2}:\d{2})\s*(.*)$/', $gabungan, $mAwal)) {
            throw new \RuntimeException('Format tanggal/waktu tidak dikenali.');
        }

        $dt = \DateTime::createFromFormat('d/m/y H:i:s', $mAwal[1] . ' ' . $mAwal[2]);
        $waktu = $dt !== false ? $dt->format('Y-m-d H:i:s') : null;

        $debet      = $this->uraikanAngka($mNominal[1]);
        $kredit     = $this->uraikanAngka($mNominal[2]);
        $saldoRiil  = $this->uraikanAngka($mNominal[3]);
        $jumlah     = max($debet, $kredit);

        $sisaSetelahWaktu  = trim($mAwal[3]);
        $posisiNominal     = strrpos($gabungan, $mNominal[0]);
        $sisaSebelumNominal = trim(substr($gabungan, strlen($mAwal[1] . ' ' . $mAwal[2]), $posisiNominal - strlen($mAwal[1] . ' ' . $mAwal[2])));

        $token = $sisaSebelumNominal === '' ? [] : preg_split('/\s+/', $sisaSebelumNominal);
        $teller = '';
        if (!empty($token)) {
            $tokenAkhir = end($token);
            // Heuristik: teller/user ID berupa digit (≥4 angka) atau kode
            // huruf kapital khusus seperti "BRIMDBT" (biaya SMS notifikasi).
            if (preg_match('/^\d{4,}$/', $tokenAkhir) || preg_match('/^[A-Z]{4,10}$/', $tokenAkhir)) {
                $teller = $tokenAkhir;
                array_pop($token);
            }
        }
        $deskripsi = trim(implode(' ', $token));

        $fingerprint = implode('|', [$waktu, $jumlah, $deskripsi, $teller]);
        $noRef = 'AUTO-' . substr(md5($fingerprint), 0, 16);

        return [
            'no'              => 0,
            'waktu_transaksi' => $waktu,
            'no_referensi'    => $noRef,
            'nama_pengirim'   => '',
            'bank_pengirim'   => '',
            'nama_penerima'   => '',
            'bank_penerima'   => '',
            'deskripsi'       => $deskripsi,
            'debet'           => $debet,
            'kredit'          => $kredit,
            'jumlah'          => $jumlah,
            'saldo_riil'      => $saldoRiil,
            'kode'            => $teller,
            'jenis_transaksi' => $debet > 0 ? 'PENGELUARAN' : 'PEMASUKAN',

            'akun_debit_id'  => null,
            'akun_kredit_id' => null,
            'is_duplikat'    => false,
        ];
    }

    private function uraikanMetadataPdfBri(string $fullText): array
    {
        $meta = [
            'periode'      => null,
            'total_debet'  => 0,
            'total_kredit' => 0,
            'saldo_awal'   => 0,
            'saldo_akhir'  => 0,
        ];

        if (preg_match(
            '/Periode Transaksi[\s\S]{0,80}?(\d{2}\/\d{2}\/\d{2})\s*-\s*(\d{2}\/\d{2}\/\d{2})/i',
            $fullText,
            $mPeriode
        )) {
            $meta['periode'] = $mPeriode[1] . ' s/d ' . $mPeriode[2];
        }

        // Tabel ringkasan di akhir laporan selalu berurutan:
        // Saldo Awal, Total Transaksi Debet, Total Transaksi Kredit, Saldo Akhir.
        if (preg_match(
            '/Saldo Awal[\s\S]*?Saldo Akhir[\s\S]*?([\d.,]+)\s+([\d.,]+)\s+([\d.,]+)\s+([\d.,]+)/i',
            $fullText,
            $mRingkasan
        )) {
            $meta['saldo_awal']   = $this->uraikanAngka($mRingkasan[1]);
            $meta['total_debet']  = $this->uraikanAngka($mRingkasan[2]);
            $meta['total_kredit'] = $this->uraikanAngka($mRingkasan[3]);
            $meta['saldo_akhir']  = $this->uraikanAngka($mRingkasan[4]);
        }

        return $meta;
    }

    private function cariBarisHeader(array $rows, string $formatKey): ?int
    {
        $targets = self::HEADER_TARGETS[$formatKey] ?? [];

        foreach ($rows as $i => $row) {
            $lower = array_map(fn($v) => strtolower(trim((string) $v)), $row);
            if (count(array_intersect($targets, $lower)) === count($targets)) {
                return $i;
            }
        }

        return null;
    }

    private function petakanKolomHeader(array $headerRow, string $formatKey): array
    {
        $map = [];
        $expected = self::HEADERS[$formatKey];

        foreach ($headerRow as $colIdx => $cell) {
            $normalized = strtolower(trim((string) $cell));
            foreach ($expected as $key => $label) {
                if ($normalized === $label) {
                    $map[$key] = $colIdx;
                }
            }
        }

        return $map;
    }

    private function uraikanMetadata(array $rows, int $headerIdx): array
    {
        $meta = [
            'periode'      => null,
            'total_debet'  => 0,
            'total_kredit' => 0,
            'saldo_awal'   => 0,
            'saldo_akhir'  => 0,
        ];

        for ($i = 0; $i < $headerIdx; $i++) {
            $str = implode(' ', array_map('strval', $rows[$i]));

            if (stripos($str, 'Periode') !== false &&
                preg_match('/(\d{2}\/\d{2}\/\d{4})\s+s\/d\s+(\d{2}\/\d{2}\/\d{4})/', $str, $m)) {
                $meta['periode'] = $m[1] . ' s/d ' . $m[2];
            }

            if (stripos($str, 'Total Debet') !== false) {
                $meta['total_debet'] = $this->ekstrakAngka($str);
            }

            if (stripos($str, 'Total Kredit') !== false) {
                $meta['total_kredit'] = $this->ekstrakAngka($str);
            }

            if (stripos($str, 'Saldo riil awal') !== false) {
                preg_match_all('/([\d.,]+)/', $str, $nums);
                $vals = array_values(array_filter(
                    array_map(fn($n) => $this->uraikanAngka($n), $nums[0]),
                    fn($n) => $n > 0
                ));
                $meta['saldo_awal']  = $vals[0] ?? 0;
                $meta['saldo_akhir'] = $vals[1] ?? 0;
            }
        }

        return $meta;
    }

    private function uraikanBarisTransaksi(array $row, array $map, string $formatKey): array
    {
        $get = fn(string $key) => isset($map[$key]) ? $row[$map[$key]] : null;

        $debet  = $this->uraikanAngka((string) ($get('debet')  ?? '0'));
        $kredit = $this->uraikanAngka((string) ($get('kredit') ?? '0'));
        $jumlah = max($debet, $kredit);

        $waktuRaw = $get('waktu_transaksi') ?? $get('tanggal');
        $waktu    = $this->uraikanTanggalWaktu($waktuRaw);

        $noRef = trim((string) ($get('no_referensi') ?? ''));
        if (!$noRef) {
            // Format seperti BRI_CSV tidak punya kolom nomor referensi sama
            // sekali (satu transaksi bisa terpecah jadi beberapa baris,
            // mis. nominal transfer + biaya admin, dengan 'seq' yang sama).
            // Sertakan 'seq' pada fingerprint agar tetap unik per baris.
            $fingerprint = implode('|', [
                $waktu,
                $jumlah,
                trim((string) ($get('deskripsi') ?? '')),
                trim((string) ($get('kode') ?? '')),
                trim((string) ($get('seq') ?? '')),
            ]);
            $noRef = 'AUTO-' . substr(md5($fingerprint), 0, 16);
        }

        return [
            'no'              => (int) ($get('no') ?? 0),
            'waktu_transaksi' => $waktu,
            'no_referensi' => $noRef,
            'nama_pengirim'   => trim((string) ($get('nama_pengirim') ?? '')),
            'bank_pengirim'   => trim((string) ($get('bank_pengirim') ?? '')),
            'nama_penerima'   => trim((string) ($get('nama_penerima') ?? '')),
            'bank_penerima'   => trim((string) ($get('bank_penerima') ?? '')),
            'deskripsi'       => trim((string) ($get('deskripsi') ?? '')),
            'debet'           => $debet,
            'kredit'          => $kredit,
            'jumlah'          => $jumlah,
            'saldo_riil'      => $this->uraikanAngka((string) ($get('saldo_riil') ?? '0')),
            'kode'            => trim((string) ($get('kode') ?? '')),
            'jenis_transaksi' => $debet > 0 ? 'PENGELUARAN' : 'PEMASUKAN',

            // Akan diisi saat klasifikasi
            'akun_debit_id'  => null,
            'akun_kredit_id' => null,
            'is_duplikat'    => false,
        ];
    }

    private function uraikanTanggalWaktu(mixed $value): ?string
    {
        if (is_null($value)) return null;

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d H:i:s');
        }

        $str = trim((string) $value);
        foreach (['d-m-Y H.i', 'd-m-Y H:i', 'd/m/Y H:i', 'Y-m-d H:i:s', 'd-m-Y'] as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $str);
            if ($dt !== false) return $dt->format('Y-m-d H:i:s');
        }

        return $str;
    }

    private function uraikanAngka(string $value): float
    {
        $clean = trim(preg_replace('/[^0-9,.]/', '', $value));

        if ($clean === '') return 0.0;

        // Deteksi format berdasarkan posisi koma dan titik terakhir
        $lastComma = strrpos($clean, ',');
        $lastDot   = strrpos($clean, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastDot > $lastComma) {
                // Format: 1,234,567.89 (titik = desimal, koma = ribuan) → Amerika
                $clean = str_replace(',', '', $clean);
            } else {
                // Format: 1.234.567,89 (koma = desimal, titik = ribuan) → Indonesia
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            }
        } elseif ($lastComma !== false) {
            // Hanya ada koma
            $commaCount = substr_count($clean, ',');
            if ($commaCount === 1) {
                $afterComma = strlen($clean) - $lastComma - 1;
                if ($afterComma <= 2) {
                    // Contoh: 20,00 → koma sebagai desimal
                    $clean = str_replace(',', '.', $clean);
                } else {
                    // Contoh: 20,000 → koma sebagai ribuan
                    $clean = str_replace(',', '', $clean);
                }
            } else {
                // Banyak koma → semua koma adalah ribuan
                $clean = str_replace(',', '', $clean);
            }
        } elseif ($lastDot !== false) {
            // Hanya ada titik
            $dotCount = substr_count($clean, '.');
            if ($dotCount === 1) {
                $afterDot = strlen($clean) - $lastDot - 1;
                if ($afterDot <= 2) {
                    // Contoh: 20000.00 → titik sebagai desimal, biarkan
                } else {
                    // Contoh: 20.000 → titik sebagai ribuan
                    $clean = str_replace('.', '', $clean);
                }
            } else {
                // Banyak titik → semua titik adalah ribuan
                $clean = str_replace('.', '', $clean);
            }
        }

        return (float) $clean;
    }
    private function ekstrakAngka(string $str): float
    {
        if (preg_match('/([\d.,]+)/', $str, $m)) {
            return $this->uraikanAngka($m[1]);
        }
        return 0;
    }

    private function apakahBarisKosong(array $row): bool
    {
        return empty(array_filter($row, fn($v) => !is_null($v) && trim((string) $v) !== ''));
    }
}