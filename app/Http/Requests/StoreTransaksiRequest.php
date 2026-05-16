<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransaksiRequest extends FormRequest
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
            'jenis_transaksi'       => 'required|in:PEMASUKAN,PENGELUARAN',
            'tanggal_transaksi'     => 'required|date',
            'jumlah'                => 'required|numeric|min:1',
            'dompet_id'             => 'required|exists:dompet,id',
            'kategori_transaksi_id' => 'required|exists:kategori_transaksi,id',
            'deskripsi'             => 'nullable|string|max:500',
            'bukti_transaksi'       => 'nullable|array',
            'bukti_transaksi.*'     => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_transaksi.required'       => 'Jenis transaksi wajib dipilih',
            'tanggal_transaksi.required'     => 'Tanggal wajib diisi',
            'jumlah.required'                => 'Jumlah wajib diisi',
            'jumlah.min'                     => 'Jumlah harus lebih dari 0',
            'dompet_id.required'             => 'Dompet wajib dipilih',
            'kategori_transaksi_id.required' => 'Kategori wajib dipilih',
            'bukti_transaksi.*.mimes'        => 'File harus berformat JPG, PNG, atau PDF',
            'bukti_transaksi.*.max'          => 'Ukuran file maksimal 5MB',
        ];
    }
}
