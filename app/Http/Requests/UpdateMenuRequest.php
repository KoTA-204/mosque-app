<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class UpdateMenuRequest extends FormRequest
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
        $menuId = $this->route('menu');

        return [
            'menu_name'  => 'sometimes|string|max:100',
            'route_name' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($this->parent_id && $value && !Route::has($value)) {
                        $fail("Route '{$value}' tidak ditemukan di sistem.");
                    }
                }
            ],
            'icon'       => 'nullable|string|max:100',
            'parent_id'  => 'nullable|integer|exists:menus,id|not_in:' . $menuId,
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.not_in' => 'Menu tidak bisa menjadi parent dari dirinya sendiri',
            'parent_id.exists' => 'Parent menu tidak ditemukan',
        ];
    }
}
