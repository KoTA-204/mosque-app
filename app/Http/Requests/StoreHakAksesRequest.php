<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NamaMiripRule;

class StoreHakAksesRequest extends FormRequest
{
    /**
     * Determine if the pengguna is authorized to make this request.
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
            'kode_hak_akses' => 'bail|required|string|max:100|unique:hak_akses,kode_hak_akses',
            'nama_hak_akses' => [
                'bail',
                'required',
                'string',
                'max:100',
                new NamaMiripRule('hak_akses'),
            ],
            'modul'          => 'required|string|max:100',
            'aksi'          => 'required|string|max:100',
            'deskripsi'     => 'nullable|string|max:255',
            'aktif'       => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_hak_akses.required' => 'Kode hak_akses wajib diisi.',
            'kode_hak_akses.unique'   => 'Kode hak_akses sudah digunakan.',
        ];
    }
}
