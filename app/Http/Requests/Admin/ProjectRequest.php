<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use App\Support\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProjectRequest extends FormRequest
{
    use ContentBlockValidation;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Project|null $project */
        $project = $this->route('project');

        return [
            'slug' => [
                'required', 'string', 'max:120', 'alpha_dash',
                Rule::unique('projects', 'slug')->ignore($project?->id),
            ],
            'name.en' => ['required', 'string', 'max:200'],
            'name.bn' => ['nullable', 'string', 'max:200'],
            'summary.en' => ['required', 'string', 'max:500'],
            'summary.bn' => ['nullable', 'string', 'max:500'],
            'role.en' => ['nullable', 'string', 'max:200'],
            'role.bn' => ['nullable', 'string', 'max:200'],
            'period.en' => ['nullable', 'string', 'max:100'],
            'period.bn' => ['nullable', 'string', 'max:100'],
            'highlights.en' => ['array', 'max:12'],
            'highlights.en.*' => ['string', 'max:300'],
            'highlights.bn' => ['array', 'max:12'],
            'highlights.bn.*' => ['string', 'max:300'],
            ...$this->contentBlockRules(required: false),
            'stack' => ['nullable', 'array', 'max:20'],
            'stack.*' => ['string', 'max:40'],
            'status' => ['required', Rule::in(Content::PROJECT_STATUSES)],
            'cover' => ['nullable', Rule::in(Content::COVERS)],
            'url' => ['nullable', 'url', 'max:255'],
            'repo' => ['nullable', 'url', 'max:255'],
            'featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateContentBlocks($validator)];
    }

    protected function prepareForValidation(): void
    {
        $clean = fn ($items) => array_values(array_filter(array_map('trim', (array) $items), fn ($item) => $item !== ''));
        $text = fn (string $key) => [
            'en' => trim((string) $this->input("$key.en")),
            'bn' => trim((string) $this->input("$key.bn")),
        ];

        $stack = array_values(array_filter(array_map('trim', (array) $this->input('stack', [])), fn ($item) => $item !== ''));
        $this->merge([
            'stack' => $stack,
            'url' => $this->input('url') ?: null,
            'repo' => $this->input('repo') ?: null,
            'cover' => $this->input('cover') ?: null,
            'featured' => $this->boolean('featured'),
            'role' => $text('role'),
            'period' => $text('period'),
            'highlights' => [
                'en' => $clean($this->input('highlights.en')),
                'bn' => $clean($this->input('highlights.bn')),
            ],
            'content' => [
                'en' => array_values((array) $this->input('content.en', [])),
                'bn' => array_values((array) $this->input('content.bn', [])),
            ],
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);
    }
}
