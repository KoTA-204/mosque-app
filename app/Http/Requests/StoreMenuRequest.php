<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NamaMiripRule;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Checkbox: pastikan selalu boolean walaupun tidak dicentang.
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'menu_name'     => ['required', 'string', 'max:255', 'unique:menus,menu_name', new NamaMiripRule('menu'),],
            'parent_id'     => ['nullable', 'exists:menus,id'],
            // Route hanya wajib untuk sub-menu (yang punya parent).
            'route_name'    => ['nullable', 'required_with:parent_id', 'string', 'max:255'],
            'icon'          => ['nullable', 'string', 'max:255'],
            'permission_id' => ['nullable', 'exists:permissions,id'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'menu_name.required'       => 'Nama menu wajib diisi.',
            'menu_name.unique'         => 'Nama menu sudah digunakan.',
            'route_name.required_with' => 'Route wajib dipilih untuk sub-menu.',
            'parent_id.exists'         => 'Parent menu tidak valid.',
        ];
    }
}
