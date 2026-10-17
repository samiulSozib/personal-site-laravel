<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Post;
use App\Support\Content;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        $posts = Post::query()
            ->orderByDesc('date')
            ->paginate(20)
            ->through(fn (Post $post) => [
                'id' => $post->id,
                'slug' => $post->slug,
                'title' => $post->title['en'] ?? '',
                'category' => $post->category,
                'date' => $post->date,
                'published' => $post->published,
                'cover' => $post->cover,
            ]);

        return Inertia::render('Admin/Posts/Index', ['posts' => $posts]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Posts/Form', $this->formProps(new Post([
            'date' => now()->format('Y-m-d'),
            'category' => 'ai',
            'published' => false,
            'title' => ['en' => '', 'bn' => ''],
            'excerpt' => ['en' => '', 'bn' => ''],
            'tags' => [],
            'content' => ['en' => [], 'bn' => []],
        ])));
    }

    public function store(PostRequest $request): RedirectResponse
    {
        Post::query()->create($request->validated());

        return redirect()->route('admin.posts.index')->with('success', 'Post created.');
    }

    public function edit(Post $post): Response
    {
        return Inertia::render('Admin/Posts/Form', $this->formProps($post));
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->validated());

        return redirect()->route('admin.posts.index')->with('success', 'Post saved.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(Post $post): array
    {
        return [
            'post' => [
                'id' => $post->id,
                'slug' => $post->slug,
                'title' => $post->title ?? ['en' => '', 'bn' => ''],
                'excerpt' => $post->excerpt ?? ['en' => '', 'bn' => ''],
                'date' => $post->date,
                'category' => $post->category,
                'tags' => $post->tags ?? [],
                'cover' => $post->cover,
                'published' => $post->published,
                'content' => $post->content ?? ['en' => [], 'bn' => []],
            ],
            'options' => [
                'categories' => Content::CATEGORIES,
                'categoryLabels' => Content::CATEGORY_LABELS,
                'covers' => Content::COVERS,
                'blockTypes' => Content::BLOCK_TYPES,
            ],
        ];
    }
}
