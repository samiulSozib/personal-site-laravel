<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\Experience;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_posts_are_listed_newest_first(): void
    {
        Post::factory()->create(['slug' => 'older', 'date' => '2026-01-01']);
        Post::factory()->create(['slug' => 'newer', 'date' => '2026-03-01']);
        Post::factory()->draft()->create(['slug' => 'secret-draft', 'date' => '2026-04-01']);

        $response = $this->getJson('/api/posts');

        $response->assertOk();
        $this->assertSame(['newer', 'older'], array_column($response->json(), 'slug'));
    }

    public function test_posts_can_be_filtered_by_category(): void
    {
        Post::factory()->create(['slug' => 'code-post', 'category' => 'engineering']);
        Post::factory()->create(['slug' => 'ai-post', 'category' => 'ai']);

        $response = $this->getJson('/api/posts?category=ai');

        $this->assertSame(['ai-post'], array_column($response->json(), 'slug'));
    }

    public function test_draft_post_is_not_found_by_slug(): void
    {
        Post::factory()->draft()->create(['slug' => 'draft-post']);

        $this->getJson('/api/posts/draft-post')->assertNotFound();
    }

    public function test_post_bangla_falls_back_to_english_when_empty(): void
    {
        Post::factory()->create([
            'slug' => 'english-only',
            'title' => ['en' => 'Hello', 'bn' => ''],
        ]);

        $this->getJson('/api/posts/english-only')
            ->assertOk()
            ->assertJsonPath('title.en', 'Hello')
            ->assertJsonPath('title.bn', 'Hello')
            ->assertJsonPath('content.en.0.type', 'paragraph');
    }

    public function test_experience_lists_current_roles_first_with_plain_iso_dates(): void
    {
        Experience::query()->create($this->experience(['company' => 'Old Co', 'start_date' => '2018-01-01', 'end_date' => '2020-06-30']));
        Experience::query()->create($this->experience(['company' => 'Now Co', 'start_date' => '2021-03-01', 'end_date' => null]));

        $this->getJson('/api/experience')
            ->assertOk()
            ->assertJsonPath('0.company', 'Now Co')
            ->assertJsonPath('0.current', true)
            ->assertJsonPath('0.endDate', null)
            ->assertJsonPath('1.startDate', '2018-01-01')
            ->assertJsonPath('1.endDate', '2020-06-30')
            ->assertJsonPath('1.role.bn', 'Engineer');
    }

    public function test_dates_are_returned_as_plain_iso_dates(): void
    {
        Post::factory()->create(['slug' => 'dated', 'date' => '2026-10-10']);

        $this->getJson('/api/posts/dated')->assertJsonPath('date', '2026-10-10');
    }

    public function test_projects_are_ordered_featured_first_then_by_sort_order(): void
    {
        Project::query()->create([
            'slug' => 'second', 'name' => ['en' => 'Second', 'bn' => ''], 'summary' => ['en' => 's', 'bn' => ''],
            'stack' => ['PHP'], 'status' => 'live', 'sort_order' => 2,
        ]);
        Project::query()->create([
            'slug' => 'first', 'name' => ['en' => 'First', 'bn' => ''], 'summary' => ['en' => 'f', 'bn' => ''],
            'stack' => ['Go'], 'status' => 'archived', 'sort_order' => 1,
        ]);

        Project::query()->create([
            'slug' => 'starred', 'name' => ['en' => 'Starred', 'bn' => ''], 'summary' => ['en' => 's', 'bn' => ''],
            'stack' => ['PHP'], 'status' => 'live', 'sort_order' => 9, 'featured' => true,
        ]);

        $projects = $this->getJson('/api/projects')->json();
        $this->assertSame(['starred', 'first', 'second'], array_column($projects, 'slug'));
        $this->assertTrue($projects[0]['featured']);
    }

    public function test_site_returns_site_and_profile_documents(): void
    {
        Setting::store('site', ['name' => ['en' => 'Test Name', 'bn' => 'নাম']]);
        Setting::store('profile', ['yearsInSoftware' => 6]);

        $this->getJson('/api/site')
            ->assertOk()
            ->assertJsonPath('site.name.en', 'Test Name')
            ->assertJsonPath('profile.yearsInSoftware', 6);
    }

    public function test_newsletter_stores_a_normalised_email(): void
    {
        $this->postJson('/api/newsletter', ['email' => '  Reader@Example.com ', 'lang' => 'bn'])
            ->assertCreated();

        $this->assertDatabaseHas('subscribers', ['email' => 'reader@example.com', 'lang' => 'bn', 'source' => 'blog']);
    }

    public function test_newsletter_rejects_an_invalid_email(): void
    {
        $this->postJson('/api/newsletter', ['email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_newsletter_honeypot_pretends_success_without_storing(): void
    {
        $this->postJson('/api/newsletter', ['email' => 'bot@example.com', 'website' => 'http://spam'])
            ->assertCreated();

        $this->assertSame(0, Subscriber::query()->count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function experience(array $overrides = []): array
    {
        return [
            'company' => 'Acme',
            'role' => ['en' => 'Engineer', 'bn' => ''],
            'employment_type' => 'full-time',
            'start_date' => '2020-01-01',
            'end_date' => null,
            'summary' => ['en' => 'Built things.', 'bn' => ''],
            'highlights' => ['en' => [], 'bn' => []],
            'stack' => ['PHP'],
            ...$overrides,
        ];
    }
}
