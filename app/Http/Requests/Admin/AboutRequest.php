<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AboutRequest extends FormRequest
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
        $bilingual = fn (int $max) => ['required', 'string', 'max:'.$max];

        return [
            'role.en' => $bilingual(200), 'role.bn' => $bilingual(200),
            'location.en' => $bilingual(120), 'location.bn' => $bilingual(120),
            'languages.en' => $bilingual(200), 'languages.bn' => $bilingual(200),
            'yearsInSoftware' => ['required', 'integer', 'min:0', 'max:80'],

            'story.en' => ['required', 'array', 'min:1', 'max:10'],
            'story.en.*' => ['string', 'max:2000'],
            'story.bn' => ['required', 'array', 'min:1', 'max:10'],
            'story.bn.*' => ['string', 'max:2000'],

            'skills' => ['array', 'max:20'],
            'skills.*.name.en' => ['required', 'string', 'max:100'],
            'skills.*.name.bn' => ['required', 'string', 'max:100'],
            'skills.*.items' => ['required', 'array', 'min:1', 'max:30'],
            'skills.*.items.*' => ['string', 'max:100'],

            'principles' => ['array', 'max:20'],
            'principles.*.title.en' => ['required', 'string', 'max:200'],
            'principles.*.title.bn' => ['required', 'string', 'max:200'],
            'principles.*.body.en' => ['required', 'string', 'max:1000'],
            'principles.*.body.bn' => ['required', 'string', 'max:1000'],

            'education' => ['array', 'max:10'],
            'education.*.degree.en' => ['required', 'string', 'max:200'],
            'education.*.degree.bn' => ['required', 'string', 'max:200'],
            'education.*.school.en' => ['required', 'string', 'max:200'],
            'education.*.school.bn' => ['required', 'string', 'max:200'],
            'education.*.period.en' => ['required', 'string', 'max:100'],
            'education.*.period.bn' => ['required', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $paragraphs = fn ($items) => array_values(array_filter(array_map('trim', (array) $items), fn ($item) => $item !== ''));

        $skills = collect((array) $this->input('skills', []))->map(fn ($skill) => [
            'name' => $skill['name'] ?? [],
            'items' => $paragraphs($skill['items'] ?? []),
        ])->values()->all();

        $this->merge([
            'story' => [
                'en' => $paragraphs($this->input('story.en')),
                'bn' => $paragraphs($this->input('story.bn')),
            ],
            'skills' => $skills,
            'yearsInSoftware' => (int) $this->input('yearsInSoftware', 0),
        ]);
    }
}
