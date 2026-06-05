<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKategoriRequest extends FormRequest
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
        $kategoriId = $this->route('kategori')->id;
 
        return [
            'kode_kategori' => 'required|string|max:10|unique:kategori_akun,kode_kategori,' . $kategoriId,
            'nama_kategori' => 'required|string|max:100',
        ];
    }
 
    public function messages(): array
    {
        return [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.unique'   => 'Kode kategori sudah digunakan.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
        ];
    }
}
