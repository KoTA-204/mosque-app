<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:jurnal,id',
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Tidak ada jurnal yang dipilih.',
            'ids.min'      => 'Tidak ada jurnal yang dipilih.',
        ];
    }
}
