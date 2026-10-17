<?php

namespace App\Http\Requests\Admin;

use App\Support\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:200'],
            'company_url' => ['nullable', 'url', 'max:255'],
            'role.en' => ['required', 'string', 'max:200'],
            'role.bn' => ['nullable', 'string', 'max:200'],
            'employment_type' => ['required', Rule::in(Content::EMPLOYMENT_TYPES)],
            'location.en' => ['nullable', 'string', 'max:200'],
            'location.bn' => ['nullable', 'string', 'max:200'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'summary.en' => ['required', 'string', 'max:1000'],
            'summary.bn' => ['nullable', 'string', 'max:1000'],
            'highlights.en' => ['array', 'max:12'],
            'highlights.en.*' => ['string', 'max:300'],
            'highlights.bn' => ['array', 'max:12'],
            'highlights.bn.*' => ['string', 'max:300'],
            'stack' => ['array', 'max:20'],
            'stack.*' => ['string', 'max:40'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $clean = fn ($items) => array_values(array_filter(array_map('trim', (array) $items), fn ($item) => $item !== ''));

        $this->merge([
            'company_url' => $this->input('company_url') ?: null,
            // An unticked "current role" box sends an end date; a ticked one clears it.
            'end_date' => $this->boolean('current') ? null : ($this->input('end_date') ?: null),
            'highlights' => [
                'en' => $clean($this->input('highlights.en')),
                'bn' => $clean($this->input('highlights.bn')),
            ],
            'stack' => $clean($this->input('stack')),
        ]);
    }
}
