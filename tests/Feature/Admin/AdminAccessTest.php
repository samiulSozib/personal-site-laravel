<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_signed_in_users_who_are_not_admins_get_forbidden(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admins_can_open_the_dashboard(): void
    {
        Post::factory()->create(['slug' => 'one']);
        Post::factory()->draft()->create(['slug' => 'two']);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
                ->where('stats.posts', 2)
                ->where('stats.publishedPosts', 1));
    }

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_admin_can_create_a_post_with_content_blocks(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/posts', $this->validPostPayload(['slug' => 'new-post']))
            ->assertRedirect('/admin/posts')
            ->assertSessionHas('success', 'Post created.');

        $post = Post::query()->where('slug', 'new-post')->firstOrFail();
        $this->assertSame('heading', $post->content['en'][1]['type']);
        $this->assertSame('ai', $post->category);
    }

    public function test_post_slug_must_be_unique_but_can_be_kept_on_update(): void
    {
        $existing = Post::factory()->create(['slug' => 'taken']);
        $other = Post::factory()->create(['slug' => 'other']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put("/admin/posts/{$other->slug}", $this->validPostPayload(['slug' => 'taken']))
            ->assertSessionHasErrors('slug');

        $this->actingAs($admin)
            ->put("/admin/posts/{$existing->slug}", $this->validPostPayload(['slug' => 'taken', 'title' => ['en' => 'Renamed']]))
            ->assertRedirect('/admin/posts');

        $this->assertSame('Renamed', $existing->refresh()->title['en']);
    }

    public function test_blocks_are_checked_for_their_required_fields(): void
    {
        $payload = $this->validPostPayload([
            'content' => [
                'en' => [
                    ['type' => 'paragraph', 'text' => '   '],
                    ['type' => 'heading', 'level' => 4, 'text' => 'Bad level'],
                    ['type' => 'list', 'items' => []],
                    ['type' => 'code', 'language' => '', 'code' => 'x'],
                ],
                'bn' => [],
            ],
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/posts', $payload)
            ->assertSessionHasErrors([
                'content.en.0.text',
                'content.en.1.level',
                'content.en.2.items',
                'content.en.3.language',
            ]);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_travel_is_no_longer_a_post_category(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/posts', $this->validPostPayload(['slug' => 'old-trip', 'category' => 'travel']))
            ->assertSessionHasErrors('category');
    }

    public function test_site_settings_are_saved_and_validated(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/site', $this->validSitePayload(['year' => 1900]))
            ->assertSessionHasErrors('year');

        $this->actingAs($admin)->put('/admin/site', $this->validSitePayload([
            'name' => ['en' => 'New Name', 'bn' => 'নতুন নাম'],
            'email' => '',
        ]))->assertRedirect('/admin/site');

        $site = Setting::document('site');
        $this->assertSame('New Name', $site['name']['en']);
        $this->assertNull($site['email']);
        $this->assertSame(['en' => '', 'bn' => ''], $site['availability']);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPostPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'slug' => 'a-post',
            'title' => ['en' => 'A post', 'bn' => ''],
            'excerpt' => ['en' => 'Short', 'bn' => ''],
            'date' => '2026-10-10',
            'category' => 'ai',
            'tags' => ['Web'],
            'cover' => 'neural',
            'published' => true,
            'content' => [
                'en' => [
                    ['type' => 'paragraph', 'text' => 'Hello there'],
                    ['type' => 'heading', 'level' => 2, 'text' => 'Section'],
                ],
                'bn' => [],
            ],
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validSitePayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'name' => ['en' => 'Samiul', 'bn' => 'সামিউল'],
            'title' => ['en' => 'Title', 'bn' => 'শিরোনাম'],
            'description' => ['en' => 'Desc', 'bn' => 'বর্ণনা'],
            'intro' => ['en' => 'Intro', 'bn' => 'ভূমিকা'],
            'about' => ['en' => 'About', 'bn' => 'পরিচিতি'],
            'interests' => ['en' => ['Web'], 'bn' => ['ওয়েব']],
            'email' => 'hi@example.com',
            'social' => ['github' => '', 'linkedin' => '', 'x' => ''],
            'year' => 2026,
        ], $overrides);
    }
}
