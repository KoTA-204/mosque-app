<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\NamaMiripRule;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $menu   = $this->route('menu');
        $menuId = is_object($menu) ? $menu->id : $menu;

        return [
            'menu_name' => [
                'bail',
                'required',
                'string',
                'max:255',
                Rule::unique('menus', 'menu_name')->ignore($menuId),
                new NamaMiripRule('menu', exceptId: $menuId),
            ],
            // Tidak boleh menjadi parent dari dirinya sendiri.
            'parent_id'     => ['nullable', 'exists:menus,id', Rule::notIn([$menuId])],
            'route_name'    => ['nullable', 'required_with:parent_id', 'string', 'max:255'],
            'icon'          => ['nullable', 'string', 'max:255'],
            'hak_akses_id' => ['nullable', 'exists:hak_akses,id'],
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
            'parent_id.not_in'         => 'Menu tidak boleh menjadi parent dari dirinya sendiri.',
        ];
    }
}