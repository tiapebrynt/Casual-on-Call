<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'company']) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('payment_type')) {
            $this->merge(['payment_type' => 'daily']);
        }
        if (!$this->filled('status')) {
            $this->merge(['status' => 'published']);
        }
    }

    public function rules(): array
    {
        return [
            'job_category_id' => ['required', 'exists:job_categories,id'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'min:20'],
            'location' => ['required', 'string', 'max:150'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'daily_rate' => ['required', 'numeric', 'min:10000'],
            'payment_type' => ['required', 'in:daily,project'],
            'vacancies' => ['required', 'integer', 'min:1', 'max:1000'],
            'status' => ['required', 'in:draft,published,expired,closed,completed,cancelled'],
            'application_deadline' => ['required', 'date', 'before:starts_at'],
            'requirements' => ['nullable', 'array'],
            'requirements.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
