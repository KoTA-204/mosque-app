<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransaksiRequest extends FormRequest
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
            'dompet_id'             => 'required|exists:dompet,id',
            'kategori_transaksi_id' => 'required|exists:kategori_transaksi,id',
            'kegiatan_id'           => 'nullable|exists:kegiatan,id',
            'tanggal_transaksi'     => 'required|date',
            'akun_debit_id'         => 'required|exists:akun,id',
            'akun_kredit_id'        => 'required|exists:akun,id',
            'deskripsi'             => 'nullable|string|max:500',
            'catatan'               => 'nullable|string|max:500',
            'bukti_transaksi'       => 'nullable|array|max:5',
            'bukti_transaksi.*'     => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'dompet_id.required'             => 'Dompet wajib dipilih.',
            'kategori_transaksi_id.required' => 'Kategori transaksi wajib dipilih.',
            'tanggal_transaksi.required'     => 'Tanggal transaksi wajib diisi.',
            'akun_debit_id.required'         => 'Akun debit wajib dipilih.',
            'akun_kredit_id.required'        => 'Akun kredit wajib dipilih.',
            'bukti_transaksi.*.mimes'        => 'File harus berformat JPG, PNG, atau PDF.',
            'bukti_transaksi.*.max'          => 'Ukuran file maksimal 5 MB.',
        ];
    }
}
