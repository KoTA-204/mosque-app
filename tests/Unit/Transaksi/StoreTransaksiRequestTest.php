<?php

namespace Tests\Unit\Transaksi;

use App\Http\Requests\StoreTransaksiRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTransaksiRequestTest extends TestCase
{
    private function validate(array $data, array $files = [])
    {
        $rules = (new StoreTransaksiRequest())->rules();
        return Validator::make(array_merge($data, $files), $rules);
    }

    /** UT-F23-01 — Nominal valid lolos */
    public function test_UT_F23_01_nominal_valid_lolos(): void
    {
        $v = $this->validate(['jumlah' => 50000]);
        $this->assertFalse($v->errors()->has('jumlah'));
    }

    /** UT-F24-01 — Tolak nominal nol (divalidasi di jurnal.*.nominal, min:1) */
    public function test_UT_F24_01_tolak_nominal_nol(): void
    {
        $v = $this->validate([
            'jurnal' => [
                ['tipe' => 'DEBIT',  'nominal' => 0],
                ['tipe' => 'KREDIT', 'nominal' => 0],
            ],
        ]);

        $this->assertTrue($v->errors()->has('jurnal.0.nominal'));
    }

    /** UT-F26-01 — Tolak bukti format / ukuran invalid */
    public function test_UT_F26_01_tolak_bukti_format_atau_ukuran_invalid(): void
    {
        // Rule ada di bukti_transaksi.* (mimes jpg,jpeg,png,pdf | max:5120)
        $file = UploadedFile::fake()->create('bukti.docx', 6144); // 6MB & mime salah
        $v = $this->validate(['jumlah' => 1000], ['bukti_transaksi' => [$file]]);
        $this->assertTrue($v->errors()->has('bukti_transaksi.0'));
    }
}