<?php

namespace App\Http\Resources;

use App\Support\Localized;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Project */
class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => Localized::text($this->name),
            'summary' => Localized::text($this->summary),
            'role' => Localized::text($this->role),
            'period' => Localized::text($this->period),
            'highlights' => Localized::list($this->highlights),
            'content' => Localized::list($this->content),
            'stack' => $this->stack,
            'status' => $this->status,
            'cover' => $this->cover,
            'url' => $this->url,
            'repo' => $this->repo,
            'featured' => (bool) $this->featured,
        ];
    }
}
