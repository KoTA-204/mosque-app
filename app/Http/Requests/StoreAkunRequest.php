<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAkunRequest extends FormRequest
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
            'parent_id'    => 'required|exists:akun,id',
            'nama_akun'    => 'required|string|max:150',
            'saldo_normal' => 'required|in:DEBIT,KREDIT',
            'deskripsi'    => 'nullable|string',
            'status'       => 'required|in:aktif,tidak_aktif',
        ];
    }
 
    public function messages(): array
    {
        return [
            'parent_id.required'    => 'Sub kategori wajib dipilih.',
            'nama_akun.required'    => 'Nama akun wajib diisi.',
            'saldo_normal.required' => 'Saldo normal wajib dipilih.',
            'deskripsi.string'      => 'Deskripsi harus berupa teks.',
            'status.required'       => 'Status wajib dipilih.',
        ];
    }
}
