<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use App\Support\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PostRequest extends FormRequest
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
        /** @var Post|null $post */
        $post = $this->route('post');

        return [
            'slug' => [
                'required', 'string', 'max:120', 'alpha_dash',
                Rule::unique('posts', 'slug')->ignore($post?->id),
            ],
            'title.en' => ['required', 'string', 'max:200'],
            'title.bn' => ['nullable', 'string', 'max:200'],
            'excerpt.en' => ['required', 'string', 'max:500'],
            'excerpt.bn' => ['nullable', 'string', 'max:500'],
            'date' => ['required', 'date_format:Y-m-d'],
            'category' => ['required', Rule::in(Content::CATEGORIES)],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:40'],
            'cover' => ['nullable', Rule::in(Content::COVERS)],
            'published' => ['boolean'],
            ...$this->contentBlockRules(),
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
        $tags = $this->input('tags', []);
        $this->merge([
            'tags' => array_values(array_filter(array_map('trim', (array) $tags), fn ($tag) => $tag !== '')),
            'published' => $this->boolean('published'),
        ]);
    }
}
