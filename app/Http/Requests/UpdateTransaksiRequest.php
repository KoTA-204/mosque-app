<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransaksiRequest extends FormRequest
{
    protected $errorBag = 'editTransaksi'; 
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dompet_id'             => 'required|exists:dompet,id',
            'tanggal_transaksi'     => 'required|date',
            'jenis_transaksi'       => 'required|in:PEMASUKAN,PENGELUARAN',
            'jumlah'                => 'required|numeric|min:1',
            'akun_debit_id'         => 'required|exists:akun,id',
            'akun_kredit_id'        => 'required|exists:akun,id',
            'deskripsi'             => 'nullable|string|max:500',
            'bukti_transaksi'       => 'nullable|array|max:5',
            'bukti_transaksi.*'     => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'dompet_id.required'             => 'Dompet wajib dipilih.',
            'tanggal_transaksi.required'     => 'Tanggal transaksi wajib diisi.',
            'jenis_transaksi.required'       => 'Jenis transaksi wajib dipilih',
            'jumlah.required'                => 'Jumlah wajib diisi',
            'jumlah.min'                     => 'Jumlah harus lebih dari 0',
            'akun_debit_id.required'         => 'Akun debit wajib dipilih.',
            'akun_kredit_id.required'        => 'Akun kredit wajib dipilih.',
            'bukti_transaksi.*.mimes'        => 'File harus berformat JPG, PNG, atau PDF.',
            'bukti_transaksi.*.max'          => 'Ukuran file maksimal 5 MB.',
        ];
    }
}
