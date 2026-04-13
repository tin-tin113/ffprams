<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'      => ['required', 'in:admin,staff,viewer,partner'],
            'agency_id' => ['nullable', 'exists:agencies,id'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
