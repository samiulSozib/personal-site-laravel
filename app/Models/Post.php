<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'title', 'excerpt', 'date', 'category', 'tags', 'cover', 'published', 'content'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    // "date" is a DATE column. It is left uncast so it always reads back as YYYY-MM-DD.
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'excerpt' => 'array',
            'tags' => 'array',
            'content' => 'array',
            'published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('published', true);
    }
}
