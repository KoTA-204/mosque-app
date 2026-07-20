<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKategoriRequest extends FormRequest
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
            'kode_kategori' => [
                'required',
                'string',
                Rule::in(['1', '2', '3', '4', '5']),
                'unique:kategori_akun,kode_kategori',
            ],
            'nama_kategori' => [
                'required',
                'string',
                Rule::in(['Aset', 'Liabilitas', 'Aset Neto', 'Pendapatan', 'Beban']),
                'unique:kategori_akun,nama_kategori',
            ],
        ];
    }
 
    public function messages(): array
    {
        return [
            'kode_kategori.in'   => 'Kode kategori harus salah satu dari 1–5 sesuai 5 unsur laporan keuangan ISAK 35.',
            'nama_kategori.in'   => 'Nama kategori harus salah satu dari: Aset, Liabilitas, Aset Neto, Pendapatan, atau Beban sesuai ISAK 35.',
            'kode_kategori.unique' => 'Kategori dengan kode ini sudah ada.',
            'nama_kategori.unique' => 'Kategori dengan nama ini sudah ada.',
        ];
    }
}