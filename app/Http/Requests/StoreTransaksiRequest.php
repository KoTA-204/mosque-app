<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransaksiRequest extends FormRequest
{
    protected $errorBag = 'createTransaksi';    

    public function authorize(): bool
    {
        return true; // otorisasi detail ditangani di controller
    }

    public function rules(): array
    {
        return [
            'jenis_transaksi'       => ['required', Rule::in(['PEMASUKAN', 'PENGELUARAN'])],
            'tanggal_transaksi'     => ['required', 'date'],
            'jumlah'                => ['required', 'numeric', 'min:1'],
            'dompet_id'             => ['required', 'exists:dompet,id'],
            'kategori_transaksi_id' => ['required', 'exists:kategori_transaksi,id'],
            'deskripsi'             => ['nullable', 'string', 'max:500'],
            'bukti_transaksi'       => ['nullable', 'array'],
            'bukti_transaksi.*'     => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'jumlah.min'            => 'Jumlah minimal Rp 1.',
            'bukti_transaksi.*.max' => 'Ukuran tiap bukti maksimal 5MB.',
        ];
    }
}
