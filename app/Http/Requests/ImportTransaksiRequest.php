<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportTransaksiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bank'            => 'required|in:BSI,BRI',
            'jenis_transaksi' => 'required|in:PEMASUKAN,PENGELUARAN',
            'file'            => 'required|file|mimes:xlsx,xls|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'bank.required'            => 'Bank wajib dipilih.',
            'bank.in'                  => 'Bank tidak valid. Pilih BSI atau BRI.',
            'jenis_transaksi.required' => 'Jenis transaksi wajib dipilih.',
            'jenis_transaksi.in'       => 'Jenis transaksi tidak valid.',
            'file.required'            => 'File mutasi bank wajib diunggah.',
            'file.mimes'               => 'File harus berformat Excel (.xlsx atau .xls).',
            'file.max'                 => 'Ukuran file maksimal 10 MB.',
        ];
    }
}
