<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => ['sometimes', 'required', 'string', 'alpha_dash', 'max:50', Rule::unique('roles', 'name')->ignore($role)],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
