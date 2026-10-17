<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Setting;
use App\Support\Content;
use Illuminate\Http\Response;

/**
 * The 1200x630 preview card shared when a post link is posted on social media.
 * Drawn with GD and the bundled Geist font, so it does not depend on fonts installed on the server.
 */
class OpenGraphController extends Controller
{
    private const WIDTH = 1200;

    private const HEIGHT = 630;

    private const PADDING = 72;

    private const MAX_TITLE_LINES = 4;

    public function post(string $lang, string $slug): Response
    {
        $post = Post::query()->published()->where('slug', $slug)->first();
        if (! $post) {
            abort(404);
        }

        $site = Setting::document('site');
        $title = (string) data_get($post->title, 'en', data_get($site, 'title.en', ''));
        $label = Content::CATEGORY_LABELS[$post->category] ?? 'Blog';
        $siteName = (string) data_get($site, 'name.en', config('app.name'));

        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagefill($image, 0, 0, $this->color($image, '#0b0d1a'));

        $light = $this->color($image, '#f4f4f8');
        $muted = $this->color($image, '#a5a8c7');
        $regular = resource_path('fonts/Geist-Regular.ttf');
        $bold = resource_path('fonts/Geist-Bold.ttf');

        imagettftext($image, 28, 0, self::PADDING, self::PADDING + 28, $muted, $regular, $label);

        $titleSize = 68;
        $lineHeight = 78;
        $y = 300;
        foreach ($this->wrap($title, $bold, $titleSize, self::WIDTH - 2 * self::PADDING) as $line) {
            imagettftext($image, $titleSize, 0, self::PADDING, $y, $light, $bold, $line);
            $y += $lineHeight;
        }

        imagettftext($image, 28, 0, self::PADDING, self::HEIGHT - self::PADDING, $muted, $regular, $siteName);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Splits the title into lines that fit the width. Stops at the last allowed line and adds an ellipsis.
     *
     * @return array<int, string>
     */
    private function wrap(string $text, string $font, int $size, int $maxWidth): array
    {
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;
            if ($this->width($candidate, $font, $size) <= $maxWidth) {
                $current = $candidate;

                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }
            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        if (count($lines) > self::MAX_TITLE_LINES) {
            $lines = array_slice($lines, 0, self::MAX_TITLE_LINES);
            $lines[self::MAX_TITLE_LINES - 1] = rtrim($lines[self::MAX_TITLE_LINES - 1], '.').'…';
        }

        return $lines;
    }

    private function width(string $text, string $font, int $size): int
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return $box[2] - $box[0];
    }

    private function color($image, string $hex): int
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return imagecolorallocate($image, $r, $g, $b);
    }
}
