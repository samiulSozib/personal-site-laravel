<?php

namespace Tests\Feature\Site;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_page_renders_with_its_details(): void
    {
        Project::query()->create([
            'slug' => 'docchat',
            'name' => ['en' => 'DocChat', 'bn' => ''],
            'summary' => ['en' => 'Answers with citations.', 'bn' => ''],
            'role' => ['en' => 'Creator', 'bn' => ''],
            'period' => ['en' => '2026', 'bn' => ''],
            'highlights' => ['en' => ['Cites sources'], 'bn' => []],
            'content' => ['en' => [['type' => 'paragraph', 'text' => 'How it works.']], 'bn' => []],
            'stack' => ['Laravel'],
            'status' => 'live',
        ]);

        foreach (['en', 'bn'] as $lang) {
            $this->get("/{$lang}/projects/docchat")->assertOk()->assertInertia(fn ($page) => $page
                ->component('Project')
                ->where('project.slug', 'docchat')
                ->where('project.role.bn', 'Creator')
                ->where('project.highlights.bn', ['Cites sources'])
                ->where('project.content.en.0.text', 'How it works.'));
        }
    }

    public function test_projects_without_details_still_render(): void
    {
        Project::query()->create([
            'slug' => 'bare', 'name' => ['en' => 'Bare', 'bn' => ''], 'summary' => ['en' => 'S', 'bn' => ''],
            'stack' => [], 'status' => 'archived',
        ]);

        $this->get('/en/projects/bare')->assertOk()->assertInertia(fn ($page) => $page
            ->where('project.role', ['en' => '', 'bn' => ''])
            ->where('project.content', ['en' => [], 'bn' => []]));
    }

    public function test_unknown_project_is_a_real_404(): void
    {
        $this->get('/en/projects/nope')->assertNotFound()->assertInertia(fn ($page) => $page->component('NotFound'));
    }

    public function test_sitemap_lists_project_pages(): void
    {
        Project::query()->create([
            'slug' => 'mapped-project', 'name' => ['en' => 'M', 'bn' => ''], 'summary' => ['en' => 'S', 'bn' => ''],
            'stack' => [], 'status' => 'live',
        ]);

        $this->assertStringContainsString('/bn/projects/mapped-project', $this->get('/sitemap.xml')->getContent());
    }

    public function test_admin_saves_project_details_and_checks_blocks(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $payload = [
            'slug' => 'new-thing',
            'name' => ['en' => 'New thing', 'bn' => ''],
            'summary' => ['en' => 'Summary', 'bn' => ''],
            'role' => ['en' => ' Lead ', 'bn' => ''],
            'period' => ['en' => '2026', 'bn' => ''],
            'highlights' => ['en' => ['Fast', ' '], 'bn' => []],
            'content' => ['en' => [['type' => 'heading', 'level' => 2, 'text' => 'Why']], 'bn' => []],
            'stack' => ['Go'],
            'status' => 'live',
            'featured' => true,
        ];

        $this->actingAs($admin)->post('/admin/projects', $payload)->assertRedirect('/admin/projects');

        $project = Project::query()->where('slug', 'new-thing')->firstOrFail();
        $this->assertSame('Lead', $project->role['en']);
        $this->assertSame(['Fast'], $project->highlights['en']);
        $this->assertSame('Why', $project->content['en'][0]['text']);

        $this->actingAs($admin)->put('/admin/projects/new-thing', [
            ...$payload,
            'content' => ['en' => [['type' => 'paragraph', 'text' => '  ']], 'bn' => []],
        ])->assertSessionHasErrors('content.en.0.text');
    }
}
