<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
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
            'permission_code' => 'required|string|max:100|unique:permissions,permission_code',
            'permission_name' => 'required|string|max:100',
            'module'          => 'required|string|max:100',
            'action'          => 'required|string|max:100',
            'description'     => 'nullable|string|max:255',
            'is_active'       => 'nullable|boolean',
        ];
    }
}
