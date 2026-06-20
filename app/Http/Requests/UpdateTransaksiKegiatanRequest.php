<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransaksiKegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_transaksi'       => 'required|in:PEMASUKAN,PENGELUARAN',
            'tanggal_transaksi'     => 'required|date',
            'jumlah'                => 'required|numeric|min:1',
            'dompet_id'             => 'required|exists:dompet,id',
            'kategori_transaksi_id' => 'required|exists:kategori_transaksi,id',
            'deskripsi'             => 'nullable|string|max:500',
            // bukti bersifat opsional saat edit
            'bukti_transaksi'       => 'nullable|array|max:5',
            'bukti_transaksi.*'     => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
            // daftar id bukti lama yang ingin dihapus
            'hapus_bukti'           => 'nullable|array',
            'hapus_bukti.*'         => 'integer',
        ];
    }
}