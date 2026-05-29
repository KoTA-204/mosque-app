<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKenclengRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_hitung' => 'required|date',
            'dompet_id'      => 'required|exists:dompet,id',
            'pecahan'        => 'nullable|array',
            'pecahan.*'      => 'nullable|integer|min:0',
            'jumlah_disetor' => 'required|string',
            'berita_acara'   => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'keterangan'     => 'nullable|string|max:500',
            'submit_type'    => 'required|in:ajukan',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_hitung.required' => 'Tanggal hitung wajib diisi',
            'dompet_id.required'      => 'Dompet wajib dipilih',
            'dompet_id.exists'        => 'Dompet tidak ditemukan',
            'jumlah_disetor.required' => 'Jumlah disetor wajib diisi',
            'berita_acara.required'   => 'Berita acara wajib diupload',
            'berita_acara.mimes'      => 'File harus berformat JPG, PNG, atau PDF',
            'berita_acara.max'        => 'Ukuran file maksimal 5MB',
        ];
    }
}