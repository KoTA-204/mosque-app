<?php

namespace App\Services\Operasional;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\Transaksi;

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
    ];

    /**
     * Parse file Excel mutasi bank.
     *
     * @return array{ rows: array, meta: array, errors: array }
     */
    public function uraikanFileMutasiBank(UploadedFile $file, string $bank = 'BSI', ?string $jenisTransaksi = null): array
    {
        $bank = strtoupper($bank);

        if (!isset(self::HEADERS[$bank])) {
            return ['rows' => [], 'meta' => [], 'errors' => ["Bank '$bank' tidak didukung."]];
        }

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $rawRows     = $sheet->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return ['rows' => [], 'meta' => [], 'errors' => ['File tidak dapat dibaca: ' . $e->getMessage()]];
        }

        $headerRowIdx = $this->cariBarisHeader($rawRows, $bank);

        if ($headerRowIdx === null) {
            return [
                'rows'   => [],
                'meta'   => [],
                'errors' => ['Struktur file tidak dikenali. Pastikan format file sesuai mutasi bank ' . $bank . '.'],
            ];
        }

        $headerMap = $this->petakanKolomHeader($rawRows[$headerRowIdx], $bank);
        \Log::debug('Header row raw:', $rawRows[$headerRowIdx]);
        \Log::debug('Header map result:', $headerMap);
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
                $parsed = $this->uraikanBarisTransaksi($row, $headerMap, $bank);

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

    private function cariBarisHeader(array $rows, string $bank): ?int
    {
        $targets = match ($bank) {
            'BSI' => ['waktu transaksi', 'no. referensi'],
            'BRI' => ['tanggal'],
            default => [],
        };

        foreach ($rows as $i => $row) {
            $lower = array_map(fn($v) => strtolower(trim((string) $v)), $row);
            if (count(array_intersect($targets, $lower)) === count($targets)) {
                return $i;
            }
        }

        return null;
    }

    private function petakanKolomHeader(array $headerRow, string $bank): array
    {
        $map = [];
        $expected = self::HEADERS[$bank];

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

    private function uraikanBarisTransaksi(array $row, array $map, string $bank): array
    {
        $get = fn(string $key) => isset($map[$key]) ? $row[$map[$key]] : null;

        $debet  = $this->uraikanAngka((string) ($get('debet')  ?? '0'));
        $kredit = $this->uraikanAngka((string) ($get('kredit') ?? '0'));
        $jumlah = max($debet, $kredit);

        $waktuRaw = $get('waktu_transaksi') ?? $get('tanggal');
        $waktu    = $this->uraikanTanggalWaktu($waktuRaw);

        $noRef = trim((string) ($get('no_referensi') ?? ''));
        if (!$noRef) {
            $fingerprint = implode('|', [
                $waktu,
                $jumlah,
                trim((string) ($get('deskripsi') ?? '')),
                trim((string) ($get('kode') ?? '')),
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