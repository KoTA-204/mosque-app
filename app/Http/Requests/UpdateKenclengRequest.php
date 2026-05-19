<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKenclengRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_hitung'  => 'required|date',
            'dompet_id'       => 'required|exists:dompet,id',
            'pecahan'         => 'nullable|array',
            'pecahan.*'       => 'nullable|integer|min:0',
            'jumlah_disetor'  => 'required|string',
            'berita_acara'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'keterangan'      => 'nullable|string|max:500',
            'submit_type'     => 'required|in:draf,ajukan',
        ];
    }
}