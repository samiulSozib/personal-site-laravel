<?php

namespace Tests\Feature\Site;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::store('site', [
            'name' => ['en' => 'Test Writer', 'bn' => 'টেস্ট'],
            'title' => ['en' => 'Test Writer — Notes', 'bn' => 'টেস্ট'],
            'description' => ['en' => 'Notes.', 'bn' => 'নোট।'],
        ]);
    }

    public function test_root_redirects_to_english_home(): void
    {
        $this->get('/')->assertRedirect('/en');
    }

    public function test_pages_render_for_both_languages(): void
    {
        foreach (['/en', '/bn', '/en/blog', '/en/experience', '/bn/experience', '/en/projects', '/en/about', '/en/archive'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_home_page_sends_published_posts_as_props(): void
    {
        Post::factory()->create(['slug' => 'visible-post']);
        Post::factory()->draft()->create(['slug' => 'hidden-draft']);

        $this->get('/en')->assertInertia(fn ($page) => $page
            ->component('Home')
            ->where('lang', 'en')
            ->has('posts', 1)
            ->where('posts.0.slug', 'visible-post'));
    }

    public function test_published_post_page_renders(): void
    {
        Post::factory()->create(['slug' => 'a-post']);

        $this->get('/en/blog/a-post')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Post')
            ->where('post.slug', 'a-post'));
    }

    public function test_draft_post_returns_a_real_404(): void
    {
        Post::factory()->draft()->create(['slug' => 'draft-post']);

        $this->get('/en/blog/draft-post')->assertNotFound();
        $this->get('/en/blog/no-such-post')->assertNotFound();
    }

    public function test_unknown_tag_returns_404_and_known_tag_renders(): void
    {
        Post::factory()->create(['slug' => 'tagged', 'tags' => ['Next.js']]);

        $this->get('/en/tags/next-js')->assertOk();
        $this->get('/en/tags/nothing-here')->assertNotFound();
    }

    public function test_unsupported_language_is_not_found(): void
    {
        $this->get('/fr/blog')->assertNotFound();
    }

    public function test_search_query_is_passed_to_the_blog_page(): void
    {
        $this->get('/en/blog?q=rag&category=ai')->assertInertia(fn ($page) => $page
            ->component('Blog')
            ->where('q', 'rag')
            ->where('category', 'ai'));
    }

    public function test_travel_pages_are_gone(): void
    {
        $this->get('/en/travel')->assertNotFound();
    }

    public function test_feed_lists_only_published_posts(): void
    {
        Post::factory()->create(['slug' => 'public-one', 'title' => ['en' => 'Public one', 'bn' => '']]);
        Post::factory()->draft()->create(['slug' => 'secret-draft']);

        $feed = $this->get('/feed.xml')->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=utf-8');
        $this->assertStringContainsString('/en/blog/public-one', $feed->getContent());
        $this->assertStringNotContainsString('secret-draft', $feed->getContent());
    }

    public function test_sitemap_lists_both_languages_and_skips_drafts(): void
    {
        Post::factory()->create(['slug' => 'mapped']);
        Post::factory()->draft()->create(['slug' => 'unmapped']);

        $sitemap = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('/bn/blog/mapped', $sitemap);
        $this->assertStringNotContainsString('unmapped', $sitemap);
    }

    public function test_robots_points_to_the_sitemap(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap:', false);
    }
}
