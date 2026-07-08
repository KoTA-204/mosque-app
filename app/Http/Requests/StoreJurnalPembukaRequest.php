<?php

namespace App\Http\Requests;

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
            'tanggal_awal'     => ['required', 'date'],
            'keterangan'       => ['nullable', 'string', 'max:500'],
            'submit_type'      => ['required', 'in:draft,posting'],
            'detail'           => ['required', 'array', 'min:2'],
            'detail.*.akun_id' => ['required', 'exists:akun,id'],
            'detail.*.tipe'    => ['required', 'in:DEBIT,KREDIT'],
            'detail.*.nominal' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Validasi keseimbangan double-entry (single source of truth).
     * Draft boleh belum seimbang; saat 'posting' wajib seimbang & harus punya
     * minimal satu sisi Debit dan satu sisi Kredit.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
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
