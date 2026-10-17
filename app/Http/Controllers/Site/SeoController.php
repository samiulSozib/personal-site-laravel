<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use App\Support\Content;
use App\Support\TagSlug;
use Illuminate\Http\Response;

/**
 * Feed, sitemap, and robots.txt for the public site. Only published content is listed.
 */
class SeoController extends Controller
{
    public function feed(): Response
    {
        $base = $this->base();
        $site = Setting::document('site');
        $items = Post::query()->published()->orderByDesc('date')->orderByDesc('id')->get()
            ->map(function (Post $post) use ($base) {
                $url = "{$base}/en/blog/{$post->slug}";
                $date = $post->date;

                return "    <item>\n"
                    .'      <title>'.$this->xml($post->title['en'] ?? '')."</title>\n"
                    ."      <link>{$url}</link>\n"
                    ."      <guid isPermaLink=\"true\">{$url}</guid>\n"
                    .'      <pubDate>'.gmdate('D, d M Y H:i:s', strtotime($date.' 00:00:00 UTC'))." GMT</pubDate>\n"
                    .'      <description>'.$this->xml($post->excerpt['en'] ?? '')."</description>\n"
                    .'      <category>'.$this->xml($post->category)."</category>\n"
                    .'    </item>';
            })
            ->implode("\n");

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n"
            ."  <channel>\n"
            .'    <title>'.$this->xml($site['title']['en'] ?? '')."</title>\n"
            ."    <link>{$base}/en</link>\n"
            .'    <description>'.$this->xml($site['description']['en'] ?? '')."</description>\n"
            ."    <language>en</language>\n"
            ."    <atom:link href=\"{$base}/feed.xml\" rel=\"self\" type=\"application/rss+xml\" />\n"
            .$items."\n"
            ."  </channel>\n"
            .'</rss>';

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function sitemap(): Response
    {
        $base = $this->base();
        $entries = [];

        foreach (['' => 1, '/blog' => 0.6, '/about' => 0.8, '/experience' => 0.8, '/projects' => 0.7, '/archive' => 0.6] as $path => $priority) {
            $entries[] = $this->entry($base, $path, null, $priority);
        }

        foreach (Project::query()->orderBy('sort_order')->get(['slug', 'updated_at']) as $project) {
            $entries[] = $this->entry($base, "/projects/{$project->slug}", $project->updated_at?->format('Y-m-d'), 0.6);
        }

        $posts = Post::query()->published()->orderByDesc('date')->get(['slug', 'date', 'tags']);
        foreach ($posts as $post) {
            $entries[] = $this->entry($base, "/blog/{$post->slug}", $post->date, 0.8);
        }

        $tags = [];
        foreach ($posts as $post) {
            foreach ((array) $post->tags as $tag) {
                $tags[TagSlug::of((string) $tag)] = true;
            }
        }
        foreach (array_keys($tags) as $slug) {
            if ($slug !== '') {
                $entries[] = $this->entry($base, "/tags/{$slug}", null, 0.3);
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n"
            .implode("\n", array_merge(...$entries))
            ."\n</urlset>";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    public function robots(): Response
    {
        $base = $this->base();

        return response("User-agent: *\nAllow: /\n\nSitemap: {$base}/sitemap.xml\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    /**
     * One <url> per language, each linking to the other language versions.
     *
     * @return array<int, string>
     */
    private function entry(string $base, string $path, ?string $lastModified, float $priority): array
    {
        $alternates = '';
        foreach (Content::LANGUAGES as $alt) {
            $alternates .= "      <xhtml:link rel=\"alternate\" hreflang=\"{$alt}\" href=\"{$base}/{$alt}{$path}\" />\n";
        }

        $rows = [];
        foreach (Content::LANGUAGES as $lang) {
            $rows[] = "  <url>\n"
                ."    <loc>{$base}/{$lang}{$path}</loc>\n"
                .($lastModified ? "    <lastmod>{$lastModified}</lastmod>\n" : '')
                ."    <changefreq>weekly</changefreq>\n"
                ."    <priority>{$priority}</priority>\n"
                .$alternates
                .'  </url>';
        }

        return $rows;
    }

    private function base(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
