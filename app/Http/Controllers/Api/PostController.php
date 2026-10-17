<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Support\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /**
     * Published posts, newest first. Drafts never leave the backend.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'category' => ['nullable', Rule::in(Content::CATEGORIES)],
        ]);

        $posts = Post::query()
            ->published()
            ->when($request->query('category'), fn ($query, $category) => $query->where('category', $category))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return PostResource::collection($posts);
    }

    public function show(string $slug): PostResource
    {
        $post = Post::query()->published()->where('slug', $slug)->firstOrFail();

        return new PostResource($post);
    }
}
