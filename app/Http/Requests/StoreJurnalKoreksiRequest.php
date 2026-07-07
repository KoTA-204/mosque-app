<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJurnalKoreksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periode_id'       => 'required|exists:periode,id',
            'tanggal'          => 'required|date',
            'jurnal_ref_id'    => 'required|exists:jurnal,id',
            'keterangan'       => 'required|string|max:500',
            'detail'           => 'required|array|min:2',
            'detail.*.akun_id' => 'required|exists:akun,id',
            'detail.*.tipe'    => 'required|in:DEBIT,KREDIT',
            'detail.*.nominal' => 'required|string',
            'submit_type'      => 'required|in:draft,posting',
        ];
    }
}
