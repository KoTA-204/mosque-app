<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NamaMiripRule;

class StoreSubKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_akun_id' => 'required|exists:kategori_akun,id',
            'nama_akun'        => [
                'required', 'string',
                new NamaMiripRule(level: 'subkategori', scopeId: $this->input('kategori_akun_id')),
            ],
            'saldo_normal'     => 'required|in:DEBIT,KREDIT',
        ];
    }

    public function messages(): array
    {
        return [
            'kategori_akun_id.required' => 'Kategori wajib dipilih.',
            'nama_akun.required'        => 'Nama sub kategori wajib diisi.',
            'saldo_normal.required'     => 'Saldo normal wajib dipilih.',
            'saldo_normal.in'           => 'Saldo normal tidak valid.',
        ];
    }
}