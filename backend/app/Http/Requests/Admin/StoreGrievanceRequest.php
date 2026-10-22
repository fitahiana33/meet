<?php

namespace App\Http\Requests\Admin;

use App\Models\Grievance;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'sector_id' => ['required', 'integer', 'exists:sectors,id'],
            'entity_id' => ['nullable', 'integer', 'exists:entities,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'description' => ['required', 'string'],
            'status' => ['nullable', Rule::in(Grievance::STATUSES)],
            'complained_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'documents' => ['nullable', 'array', 'max:10'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx,odt,rtf,txt,csv,xls,xlsx,ods,ppt,pptx,odp,jpg,jpeg,png,gif,webp,bmp,tif,tiff,avif,heic,heif,mp4,mov,avi,mkv,webm,mpeg,mpg,3gp,m4v,wmv,flv,ogv,mp3,wav,m4a,aac,ogg,oga,opus,flac,wma,amr,zip,rar,7z,tar,gz,bz2,xz', 'max:51200'],
            'declarants' => ['nullable', 'array', 'max:20'],
            'declarants_present' => ['sometimes', 'boolean'],
            'declarants.*.id' => ['nullable', 'integer', 'exists:declarants,id'],
            'declarants.*.first_name' => ['required_without:declarants.*.id', 'nullable', 'string', 'max:120'],
            'declarants.*.last_name' => ['required_without:declarants.*.id', 'nullable', 'string', 'max:120'],
            'declarants.*.email' => ['nullable', 'email', 'max:255'],
            'declarants.*.address' => ['nullable', 'string', 'max:255'],
            'declarants.*.phone' => ['nullable', 'string', 'max:40'],
            'declarants.*.photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->filled('complained_at') || ! $this->filled('due_at')) {
                return;
            }
            try {
                if (Carbon::parse($this->input('due_at'))->lessThanOrEqualTo(Carbon::parse($this->input('complained_at')))) {
                    $validator->errors()->add('due_at', 'La date d’échéance doit être postérieure à la date de la doléance.');
                }
            } catch (\Throwable) {
            }
        });
    }
}
