<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJurnalPembukaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periode_id'       => 'required|exists:periode,id',
            'tanggal_mulai'    => 'required|date',
            'tanggal_akhir'    => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'       => 'nullable|string|max:500',
            'submit_type'      => 'required|in:draft,posting',
            'detail'           => 'required|array|min:2',
            'detail.*.akun_id' => 'required|exists:akun,id',
            'detail.*.tipe'    => 'required|in:DEBIT,KREDIT',
            'detail.*.nominal' => 'required',
        ];
    }
}
