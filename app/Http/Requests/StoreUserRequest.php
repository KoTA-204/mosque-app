<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id'  => 'required|exists:roles,id',
            'status'   => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama wajib diisi',
            'email.required'    => 'Email wajib diisi',
            'email.unique'      => 'Email sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min'      => 'Password minimal 6 karakter',
            'role_id.required'  => 'Role wajib dipilih',
            'role_id.exists'    => 'Role tidak ditemukan',
            'status.required'   => 'Status wajib dipilih',
            'status.in'         => 'Status tidak valid',
        ];
    }
}
