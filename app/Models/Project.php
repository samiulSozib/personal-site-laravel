<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'name', 'summary', 'role', 'period', 'highlights', 'content', 'stack', 'status', 'cover', 'url', 'repo', 'featured', 'sort_order'])]
class Project extends Model
{
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'summary' => 'array',
            'role' => 'array',
            'period' => 'array',
            'highlights' => 'array',
            'content' => 'array',
            'stack' => 'array',
            'featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
