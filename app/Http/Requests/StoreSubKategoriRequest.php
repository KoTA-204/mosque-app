<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubKategoriRequest extends FormRequest
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
            'kategori_akun_id' => 'required|exists:kategori_akun,id',
            'kode_akun'        => 'required|string|max:20|unique:akun,kode_akun',
            'nama_akun'        => 'required|string|max:150',
            'saldo_normal'     => 'required|in:DEBIT,KREDIT',
            'deskripsi'        => 'nullable|string',
        ];
    }
 
    public function messages(): array
    {
        return [
            'kategori_akun_id.required' => 'Kategori wajib dipilih.',
            'kode_akun.required'        => 'Kode sub kategori wajib diisi.',
            'kode_akun.unique'          => 'Kode sudah digunakan.',
            'nama_akun.required'        => 'Nama sub kategori wajib diisi.',
            'saldo_normal.required'     => 'Saldo normal wajib dipilih.',
            'saldo_normal.in'           => 'Saldo normal tidak valid.',
        ];
    }
}
