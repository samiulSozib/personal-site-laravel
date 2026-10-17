<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Single-document settings: "site" (name, intro, links), "profile" (the About page),
 * "cv" (the uploaded CV file and its download count) and "photo" (the profile photo).
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function document(string $key): array
    {
        return static::query()->where('key', $key)->value('value') ?? [];
    }

    /**
     * @param  array<string, mixed>  $value
     */
    public static function store(string $key, array $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
