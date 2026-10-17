<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ExperienceResource;
use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Experience;
use App\Support\Resume;
use App\Support\TagSlug;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * The public, bilingual site (/en and /bn). Each page gets the same shared content
 * as props, and the Inertia React pages in resources/js/frontend/pages render it.
 */
class PublicSiteController extends Controller
{
    public function home(string $lang): Response
    {
        return Inertia::render('Home', $this->props($lang));
    }

    public function blog(Request $request, string $lang): Response
    {
        return Inertia::render('Blog', $this->props($lang) + [
            'q' => (string) $request->query('q', ''),
            'category' => $request->query('category'),
        ]);
    }

    public function post(string $lang, string $slug): Response|HttpResponse
    {
        $post = Post::query()->published()->where('slug', $slug)->first();
        if (! $post) {
            return $this->notFound($lang);
        }

        return Inertia::render('Post', $this->props($lang) + [
            'post' => (new PostResource($post))->resolve(),
        ]);
    }

    public function tag(string $lang, string $tag): Response|HttpResponse
    {
        $label = $this->tagLabel($tag);
        if ($label === null) {
            return $this->notFound($lang);
        }

        return Inertia::render('Tag', $this->props($lang) + ['slug' => $tag]);
    }

    public function experience(string $lang): Response
    {
        return Inertia::render('Experience', $this->props($lang));
    }

    public function projects(string $lang): Response
    {
        return Inertia::render('Projects', $this->props($lang));
    }

    public function project(string $lang, string $slug): Response|HttpResponse
    {
        $project = Project::query()->where('slug', $slug)->first();
        if (! $project) {
            return $this->notFound($lang);
        }

        return Inertia::render('Project', $this->props($lang) + [
            'project' => (new ProjectResource($project))->resolve(),
        ]);
    }

    public function about(string $lang): Response
    {
        return Inertia::render('About', $this->props($lang));
    }

    public function archive(string $lang): Response
    {
        return Inertia::render('Archive', $this->props($lang));
    }

    /**
     * Content shared by every public page: the site identity, About page, posts, projects,
     * work experience, and the CV and photo links.
     * Only published posts are included.
     *
     * @return array<string, mixed>
     */
    public static function props(string $lang): array
    {
        $posts = Post::query()->published()->orderByDesc('date')->orderByDesc('id')->get();

        return [
            'lang' => $lang,
            'siteUrl' => rtrim((string) config('app.url'), '/'),
            'site' => Setting::document('site'),
            'profile' => Setting::document('profile'),
            'posts' => PostResource::collection($posts)->resolve(),
            'projects' => ProjectResource::collection(
                Project::query()->orderByDesc('featured')->orderBy('sort_order')->orderBy('id')->get(),
            )->resolve(),
            'experiences' => ExperienceResource::collection(Experience::query()->ordered()->get())->resolve(),
            'cv' => Resume::publicCv(),
            'photoUrl' => Resume::photoUrl(),
        ];
    }

    private function notFound(string $lang): HttpResponse
    {
        return Inertia::render('NotFound', $this->props($lang))
            ->toResponse(request())
            ->setStatusCode(404);
    }

    private function tagLabel(string $tag): ?string
    {
        foreach (Post::query()->published()->pluck('tags') as $tags) {
            foreach ((array) $tags as $label) {
                if (TagSlug::of((string) $label) === $tag) {
                    return (string) $label;
                }
            }
        }

        return null;
    }
}
