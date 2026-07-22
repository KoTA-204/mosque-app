<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NamaMiripRule;

class StorePeranRequest extends FormRequest
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
            'nama_peran' => [
                'required',
                'string',
                'max:255',
                'unique:peran,nama_peran',
                new NamaMiripRule('peran'),
            ],
            'deskripsi'      => 'nullable|string|max:255',
            'aktif'        => 'nullable|boolean',
            'hak_akses_ids'   => 'nullable|array',
            'hak_akses_ids.*' => 'integer|exists:hak_akses,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_peran.required' => 'Nama peran wajib diisi.',
            'nama_peran.unique'   => 'Nama peran sudah digunakan.',
        ];
    }
}
