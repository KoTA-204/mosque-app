<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubKategoriRequest extends FormRequest
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
        $subKategoriId = $this->route('subKategori')->id;
 
        return [
            'kategori_akun_id' => 'required|exists:kategori_akun,id',
            'kode_akun'        => 'required|string|max:20|unique:akun,kode_akun,' . $subKategoriId,
            'nama_akun'        => 'required|string|max:150',
        ];
    }
 
    public function messages(): array
    {
        return [
            'kategori_akun_id.required' => 'Kategori wajib dipilih.',
            'kode_akun.required'        => 'Kode sub kategori wajib diisi.',
            'kode_akun.unique'          => 'Kode sudah digunakan.',
            'nama_akun.required'        => 'Nama sub kategori wajib diisi.',
        ];
    }
}
