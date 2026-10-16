<?php

namespace App\Http\Requests\Admin;

use App\Models\Grievance;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGrievanceRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'sector_id' => ['required', 'integer', 'exists:sectors,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'description' => ['required', 'string'],
            'status' => ['nullable', Rule::in(Grievance::STATUSES)],
            'complained_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'documents' => ['nullable', 'array', 'max:10'],
            'documents.*' => ['file', 'max:51200'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (!$this->filled('complained_at') || !$this->filled('due_at')) return;
            try {
                if (Carbon::parse($this->input('due_at'))->lessThanOrEqualTo(Carbon::parse($this->input('complained_at')))) {
                    $validator->errors()->add('due_at', 'La date d’échéance doit être postérieure à la date de la doléance.');
                }
            } catch (\Throwable) { }
        });
    }
}
