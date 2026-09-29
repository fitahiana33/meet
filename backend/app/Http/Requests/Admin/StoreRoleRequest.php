<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('roles', 'name')],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
