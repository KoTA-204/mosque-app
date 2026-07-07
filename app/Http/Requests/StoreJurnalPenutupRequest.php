<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJurnalPenutupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periode_id' => 'required|exists:periode,id',
            'tanggal'    => 'required|date',
            'aksi'       => 'required|in:draft,posting',
        ];
    }
}
