<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SiteRequest extends FormRequest
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
            'name.en' => $bilingual(120), 'name.bn' => $bilingual(120),
            'title.en' => $bilingual(200), 'title.bn' => $bilingual(200),
            'description.en' => $bilingual(300), 'description.bn' => $bilingual(300),
            'intro.en' => $bilingual(1000), 'intro.bn' => $bilingual(1000),
            'about.en' => $bilingual(1000), 'about.bn' => $bilingual(1000),
            // Shown as a badge in the hero, for example "Open to new opportunities". Empty hides it.
            'availability.en' => ['nullable', 'string', 'max:120'],
            'availability.bn' => ['nullable', 'string', 'max:120'],
            'interests.en' => ['required', 'array', 'max:10'],
            'interests.en.*' => ['string', 'max:200'],
            'interests.bn' => ['required', 'array', 'max:10'],
            'interests.bn.*' => ['string', 'max:200'],
            'email' => ['nullable', 'email', 'max:254'],
            'social.github' => ['nullable', 'url', 'max:255'],
            'social.linkedin' => ['nullable', 'url', 'max:255'],
            'social.x' => ['nullable', 'url', 'max:255'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $clean = fn ($items) => array_values(array_filter(array_map('trim', (array) $items), fn ($item) => $item !== ''));

        $this->merge([
            'interests' => [
                'en' => $clean($this->input('interests.en')),
                'bn' => $clean($this->input('interests.bn')),
            ],
            'availability' => [
                'en' => trim((string) $this->input('availability.en')),
                'bn' => trim((string) $this->input('availability.bn')),
            ],
            'email' => $this->input('email') ?: null,
            'social' => array_map(fn ($value) => $value ?: null, (array) $this->input('social', [])),
        ]);
    }
}
