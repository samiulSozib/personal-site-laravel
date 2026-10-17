<?php

namespace App\Http\Resources;

use App\Support\Localized;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Post */
class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => Localized::text($this->title),
            'excerpt' => Localized::text($this->excerpt),
            'date' => $this->date,
            'category' => $this->category,
            'tags' => $this->tags,
            'cover' => $this->cover,
            'content' => Localized::list($this->content),
        ];
    }
}
