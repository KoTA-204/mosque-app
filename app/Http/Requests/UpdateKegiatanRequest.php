<?php

namespace App\Http\Requests;

use App\Models\Kegiatan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKegiatanRequest extends FormRequest
{
    /**
     * Determine if the pengguna is authorized to make this request.
     */
    public function authorize(): bool
    {
        $pengguna = $this->user();
        return $pengguna && $pengguna->peran()
            ->whereHas('hak_akses', fn($q) => $q
                ->where('kode_hak_akses', 'kegiatan.edit')
                ->where('is_active', true))
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_kegiatan'   => ['required', 'string', 'max:255'],
            'deskripsi'       => ['nullable', 'string', 'max:2000'],
            'jenis_kegiatan'  => ['required', 'in:' . implode(',', Kegiatan::JENIS)],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'anggaran'        => ['nullable', 'numeric', 'min:0'],
            'status'          => ['required', 'in:' . implode(',', Kegiatan::STATUS)],
            'panitia_id'      => ['required', 'exists:pengguna,id'],
        ];
    }
 
    public function messages(): array
    {
        return [
            'nama_kegiatan.required'   => 'Nama kegiatan wajib diisi.',
            'jenis_kegiatan.required'  => 'Jenis kegiatan wajib dipilih.',
            'jenis_kegiatan.in'        => 'Jenis kegiatan tidak valid.',
            'tanggal_mulai.required'   => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'anggaran.numeric'         => 'Anggaran harus berupa angka.',
            'anggaran.min'             => 'Anggaran tidak boleh negatif.',
            'status.required'          => 'Status wajib dipilih.',
            'status.in'                => 'Status tidak valid.',
            'panitia_id.required'      => 'Panitia wajib dipilih.',
            'panitia_id.exists'        => 'Panitia yang dipilih tidak ditemukan.',
        ];
    }
}
