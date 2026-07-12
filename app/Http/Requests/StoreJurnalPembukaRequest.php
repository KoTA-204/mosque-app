<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreJurnalPembukaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_periode'      => ['required', 'string', 'max:100'],
            'tanggal_awal'      => ['required', 'date'],
            'tanggal_akhir'     => ['required', 'date', 'after_or_equal:tanggal_awal'],
            'keterangan'       => ['nullable', 'string', 'max:500'],
            'submit_type'      => ['required', 'in:draft,posting'],
            'detail'           => ['required', 'array', 'min:2'],
            'detail.*.akun_id' => ['required', 'exists:akun,id'],
            'detail.*.tipe'    => ['required', 'in:DEBIT,KREDIT'],
            'detail.*.nominal' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_awal.required'  => 'Tanggal awal periode wajib diisi.',
            'tanggal_awal.date'      => 'Tanggal awal periode harus berupa tanggal yang valid.',
            'tanggal_akhir.required' => 'Tanggal akhir periode wajib diisi.',
            'tanggal_akhir.date'     => 'Tanggal akhir periode harus berupa tanggal yang valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir periode harus sama atau setelah tanggal awal periode.',
            'detail.required'        => 'Detail jurnal pembuka wajib diisi.',
        ];
    }

    /**
     * Validasi keseimbangan double-entry (single source of truth) +
     * validasi periode bulan tidak boleh yang sudah berlalu.
     *
     * Draft boleh belum seimbang; saat 'posting' wajib seimbang & harus punya
     * minimal satu sisi Debit dan satu sisi Kredit.
     *
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $periodeBulan = $this->input('periode_bulan');

            if ($periodeBulan && preg_match('/^\d{4}-\d{2}$/', $periodeBulan)) {
                $awal     = Carbon::createFromFormat('Y-m', $periodeBulan)->startOfMonth();
                $bulanIni = Carbon::now()->startOfMonth();

                if ($awal->lt($bulanIni)) {
                    $v->errors()->add('periode_bulan', 'Periode yang sudah berlalu tidak dapat dipilih untuk jurnal pembuka.');
                }
            }

            if ($this->input('submit_type') !== 'posting') {
                return;
            }

            $detail      = collect($this->input('detail', []));
            $totalDebit  = $detail->where('tipe', 'DEBIT')->sum('nominal');
            $totalKredit = $detail->where('tipe', 'KREDIT')->sum('nominal');

            if ($totalDebit <= 0 || $totalKredit <= 0) {
                $v->errors()->add('detail', 'Jurnal pembuka harus memiliki minimal satu baris Debit dan satu baris Kredit.');
            }

            if (round($totalDebit, 2) !== round($totalKredit, 2)) {
                $v->errors()->add('balance', 'Total Debit dan Kredit harus seimbang sebelum dapat diposting.');
            }
        });
    }
}
