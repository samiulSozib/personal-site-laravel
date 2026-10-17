<?php

namespace App\Http\Requests\Admin;

use App\Support\Content;
use Illuminate\Validation\Validator;

/**
 * Checks each article block against the fields its type needs. The standard rules
 * cannot express "text is required unless the type is list", so this runs after them.
 */
trait ContentBlockValidation
{
    protected function validateContentBlocks(Validator $validator): void
    {
        foreach (Content::LANGUAGES as $lang) {
            $blocks = $this->input("content.$lang");
            if (! is_array($blocks)) {
                continue;
            }

            foreach ($blocks as $index => $block) {
                $path = "content.$lang.$index";
                $type = $block['type'] ?? null;

                if (! is_array($block) || ! in_array($type, Content::BLOCK_TYPES, true)) {
                    continue;
                }

                if ($type === 'list') {
                    $items = $block['items'] ?? [];
                    if (! is_array($items) || count(array_filter($items, fn ($item) => trim((string) $item) !== '')) === 0) {
                        $validator->errors()->add("$path.items", 'A list block needs at least one item.');
                    }

                    continue;
                }

                if ($type === 'code') {
                    if (trim((string) ($block['language'] ?? '')) === '') {
                        $validator->errors()->add("$path.language", 'A code block needs a language, such as ts or bash.');
                    }
                    if (trim((string) ($block['code'] ?? '')) === '') {
                        $validator->errors()->add("$path.code", 'A code block cannot be empty.');
                    }

                    continue;
                }

                if (trim((string) ($block['text'] ?? '')) === '') {
                    $validator->errors()->add("$path.text", 'This block cannot be empty.');
                }

                if ($type === 'heading' && ! in_array((int) ($block['level'] ?? 0), [2, 3], true)) {
                    $validator->errors()->add("$path.level", 'A heading must be level 2 or 3.');
                }
            }
        }
    }

    /**
     * Posts must have a body; for projects the write-up is optional.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function contentBlockRules(bool $required = true): array
    {
        return [
            'content.en' => $required ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'content.bn' => ['nullable', 'array'],
            'content.*.*' => ['array'],
            'content.*.*.type' => ['required', 'in:'.implode(',', Content::BLOCK_TYPES)],
            'content.*.*.text' => ['nullable', 'string', 'max:5000'],
            'content.*.*.source' => ['nullable', 'string', 'max:200'],
            'content.*.*.level' => ['nullable', 'integer', 'in:2,3'],
            'content.*.*.language' => ['nullable', 'string', 'max:30'],
            'content.*.*.code' => ['nullable', 'string', 'max:10000'],
            'content.*.*.items' => ['nullable', 'array', 'max:30'],
            'content.*.*.items.*' => ['string', 'max:500'],
        ];
    }
}
