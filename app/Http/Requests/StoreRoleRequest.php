<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NamaMiripRule;

class StoreRoleRequest extends FormRequest
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
            'role_name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,role_name',
                new NamaMiripRule('role'),
            ],
            'description'      => 'nullable|string|max:255',
            'is_active'        => 'nullable|boolean',
            'permission_ids'   => 'nullable|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'role_name.required' => 'Nama role wajib diisi.',
            'role_name.unique'   => 'Nama role sudah digunakan.',
        ];
    }
}
