<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
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
        $permissionId = $this->route('permission')->id;

        return [
            'permission_code' => 'sometimes|string|max:100|unique:permissions,permission_code,' . $permissionId,
            'permission_name' => 'sometimes|string|max:100',
            'module'          => 'sometimes|string|max:100',
            'action'          => 'sometimes|string|max:100',
            'description'     => 'nullable|string|max:255',
            'is_active'       => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'permission_code.required' => 'Kode permission wajib diisi.',
            'permission_code.unique'   => 'Kode permission sudah digunakan.',
        ];
    }
}
