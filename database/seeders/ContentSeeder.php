<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Loads the starter posts, projects, work experience, and site/About content from database/seed-data.
 * Re-running it updates records in place (by slug, or by company and start date for experience).
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPosts();
        $this->seedProjects();
        $this->seedExperience();

        Setting::store('site', $this->read('site'));
        Setting::store('profile', $this->read('profile'));
    }

    private function seedPosts(): void
    {
        foreach ($this->read('posts') as $post) {
            Post::query()->updateOrCreate(['slug' => $post['slug']], [
                'title' => $post['title'],
                'excerpt' => $post['excerpt'],
                'date' => $post['date'],
                'category' => $post['category'],
                'tags' => $post['tags'],
                'cover' => $post['cover'] ?? null,
                'published' => $post['published'],
                'content' => $post['content'],
            ]);
        }
    }

    private function seedProjects(): void
    {
        foreach ($this->read('projects') as $index => $project) {
            Project::query()->updateOrCreate(['slug' => $project['slug']], [
                'name' => $project['name'],
                'summary' => $project['summary'],
                'role' => $project['role'] ?? null,
                'period' => $project['period'] ?? null,
                'highlights' => $project['highlights'] ?? null,
                'content' => $project['content'] ?? null,
                'stack' => $project['stack'],
                'status' => $project['status'],
                'cover' => $project['cover'] ?? null,
                'url' => $project['url'] ?? null,
                'repo' => $project['repo'] ?? null,
                'featured' => $project['featured'] ?? false,
                'sort_order' => $index,
            ]);
        }
    }

    private function seedExperience(): void
    {
        foreach ($this->read('experiences') as $experience) {
            Experience::query()->updateOrCreate(
                ['company' => $experience['company'], 'start_date' => $experience['start_date']],
                [
                    'company_url' => $experience['company_url'] ?? null,
                    'role' => $experience['role'],
                    'employment_type' => $experience['employment_type'],
                    'location' => $experience['location'] ?? null,
                    'end_date' => $experience['end_date'] ?? null,
                    'summary' => $experience['summary'],
                    'highlights' => $experience['highlights'],
                    'stack' => $experience['stack'],
                ],
            );
        }
    }

    /**
     * @return array<int|string, mixed>
     */
    private function read(string $name): array
    {
        return json_decode(File::get(database_path("seed-data/{$name}.json")), true, 512, JSON_THROW_ON_ERROR);
    }
}
