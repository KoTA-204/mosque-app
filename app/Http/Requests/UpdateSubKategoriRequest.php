<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NamaMiripRule;

class UpdateSubKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subKategori   = $this->route('subKategori');
        $subKategoriId = $subKategori->id;

        $kategoriId = $this->input('kategori_akun_id', $subKategori->kategori_akun_id);

        return [
            'kategori_akun_id' => 'sometimes|required|exists:kategori_akun,id',
            'kode_akun'        => 'required|string|max:20|unique:akun,kode_akun,' . $subKategoriId,
            'nama_akun'        => [
                'required', 'string',
                new NamaMiripRule(
                    level: 'subkategori',
                    scopeId: $kategoriId,
                    exceptId: $subKategoriId,
                ),
            ],
            'saldo_normal'     => 'sometimes|required|in:DEBIT,KREDIT',
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
