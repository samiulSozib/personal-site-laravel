<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Support\Content;
use App\Support\Resume;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $site = Setting::document('site');
        $profile = Setting::document('profile');
        $cv = Setting::document('cv');
        $hasCv = Resume::cvExists($cv);
        $current = Experience::query()->whereNull('end_date')->orderByDesc('start_date')->first();
        $monthStart = now()->startOfMonth();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'posts' => Post::query()->count(),
                'publishedPosts' => Post::query()->published()->count(),
                'drafts' => Post::query()->where('published', false)->count(),
                'postsThisMonth' => Post::query()->published()->where('date', '>=', $monthStart->format('Y-m-d'))->count(),
                'projects' => Project::query()->count(),
                'liveProjects' => Project::query()->where('status', 'live')->count(),
                'featuredProjects' => Project::query()->where('featured', true)->count(),
                'roles' => Experience::query()->count(),
                'yearsExperience' => (int) ($profile['yearsInSoftware'] ?? 0),
                'subscribers' => Subscriber::query()->count(),
                'subscribersThisMonth' => Subscriber::query()->where('created_at', '>=', $monthStart)->count(),
                'cvDownloads' => (int) ($cv['downloads'] ?? 0),
            ],
            'postsByMonth' => $this->postsByMonth(),
            'postsByCategory' => collect(Content::CATEGORIES)->map(fn (string $category) => [
                'category' => $category,
                'label' => Content::CATEGORY_LABELS[$category],
                'count' => Post::query()->published()->where('category', $category)->count(),
            ])->values(),
            'recentPosts' => Post::query()->latest('updated_at')->limit(6)->get()
                ->map(fn (Post $post) => [
                    'id' => $post->id,
                    'slug' => $post->slug,
                    'title' => $post->title['en'] ?? '',
                    'category' => $post->category,
                    'published' => $post->published,
                    'date' => $post->date,
                ]),
            'recentSubscribers' => Subscriber::query()->latest()->limit(5)->get(['id', 'email', 'lang', 'created_at']),
            'currentRole' => $current ? [
                'id' => $current->id,
                'role' => $current->role['en'] ?? '',
                'company' => $current->company,
                'start_date' => $current->start_date,
            ] : null,
            'cv' => $hasCv ? [
                'name' => $cv['name'] ?? 'cv.pdf',
                'uploaded_at' => $cv['uploaded_at'] ?? null,
                'downloads' => (int) ($cv['downloads'] ?? 0),
            ] : null,
            'photoUrl' => Resume::photoUrl(),
            'checklist' => $this->checklist($site, $profile, $hasCv),
        ]);
    }

    /**
     * Published posts per month for the last 12 months, oldest first.
     *
     * @return array<int, array{month: string, label: string, count: int}>
     */
    private function postsByMonth(): array
    {
        $start = now()->startOfMonth()->subMonths(11);
        $counts = Post::query()->published()
            ->where('date', '>=', $start->format('Y-m-d'))
            ->pluck('date')
            ->countBy(fn ($date) => substr((string) $date, 0, 7));

        return collect(range(0, 11))->map(function (int $offset) use ($start, $counts) {
            $month = $start->copy()->addMonths($offset);

            return [
                'month' => $month->format('Y-m'),
                'label' => $month->format('M'),
                'count' => (int) ($counts[$month->format('Y-m')] ?? 0),
            ];
        })->all();
    }

    /**
     * What is still missing from a complete professional profile, with a link to fix each item.
     *
     * @param  array<string, mixed>  $site
     * @param  array<string, mixed>  $profile
     * @return array<int, array{label: string, done: bool, route: string}>
     */
    private function checklist(array $site, array $profile, bool $hasCv): array
    {
        $social = array_filter((array) ($site['social'] ?? []));

        return [
            ['label' => 'Upload your CV', 'done' => $hasCv, 'route' => 'admin.resume.edit'],
            ['label' => 'Add a profile photo', 'done' => Resume::photoUrl() !== null, 'route' => 'admin.resume.edit'],
            ['label' => 'Add a contact email', 'done' => ! empty($site['email']), 'route' => 'admin.site.edit'],
            ['label' => 'Link GitHub or LinkedIn', 'done' => $social !== [], 'route' => 'admin.site.edit'],
            ['label' => 'Add work experience', 'done' => Experience::query()->exists(), 'route' => 'admin.experience.index'],
            ['label' => 'List your skills', 'done' => ! empty($profile['skills']), 'route' => 'admin.about.edit'],
            ['label' => 'Feature a project', 'done' => Project::query()->where('featured', true)->exists(), 'route' => 'admin.projects.index'],
            ['label' => 'Publish a post', 'done' => Post::query()->published()->exists(), 'route' => 'admin.posts.create'],
        ];
    }
}
