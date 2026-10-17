<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * One role in the work history. A null end_date means the role is current.
 */
#[Fillable(['company', 'company_url', 'role', 'employment_type', 'location', 'start_date', 'end_date', 'summary', 'highlights', 'stack'])]
class Experience extends Model
{
    // The date columns are left uncast so they always read back as YYYY-MM-DD.
    protected function casts(): array
    {
        return [
            'role' => 'array',
            'location' => 'array',
            'summary' => 'array',
            'highlights' => 'array',
            'stack' => 'array',
        ];
    }

    /**
     * Current roles first, then by start date, newest first.
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderByRaw('end_date is not null')->orderByDesc('start_date')->orderByDesc('id');
    }
}
