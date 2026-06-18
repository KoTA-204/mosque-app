<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAsetRequest extends FormRequest
{
    // otorisasi request
    public function authorize(): bool
    {
        return true;
    }

    // aturan validasi
    public function rules(): array
    {
        return [
            'nama_aset'                => 'required|string|max:255',
            'kondisi_aset'             => 'required|in:BAIK,RUSAK RINGAN,RUSAK BERAT',
            'lokasi_aset'              => 'required|string|max:255',
            'sumber_perolehan'         => 'required|string',
            'tanggal_perolehan'        => 'required|date',
            'nilai_tercatat'           => 'required|numeric|min:0',
            'nama_pemberi'             => 'nullable|string|max:255',
            'jumlah_unit'              => 'nullable|integer|min:1',
            'dokumen_pendukung'        => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:5120',
            'tanggal_mulai_penyusutan' => 'nullable|date',
            'umur_manfaat'             => 'nullable|integer|min:1',
            'keterangan'               => 'nullable|string',
        ];
    }

    // pesan error custom
    public function messages(): array
    {
        return [
            'nama_aset.required'         => 'Nama aset wajib diisi.',
            'kondisi_aset.required'      => 'Kondisi aset wajib dipilih.',
            'kondisi_aset.in'            => 'Kondisi aset tidak valid.',
            'lokasi_aset.required'       => 'Lokasi aset wajib diisi.',
            'sumber_perolehan.required'  => 'Sumber perolehan wajib dipilih.',
            'tanggal_perolehan.required' => 'Tanggal perolehan wajib diisi.',
            'nilai_tercatat.required'    => 'Nilai aset wajib diisi.',
            'nilai_tercatat.numeric'     => 'Nilai aset harus berupa angka.',
            'dokumen_pendukung.mimes'    => 'Dokumen harus PNG, JPG, atau PDF.',
            'dokumen_pendukung.max'      => 'Ukuran dokumen maksimal 5MB.',
        ];
    }
}