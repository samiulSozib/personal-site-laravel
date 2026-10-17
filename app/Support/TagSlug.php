<?php

namespace App\Support;

/**
 * Same rule as the site's tagSlug(): "Next.js" and "next.js" both become "next-js".
 */
final class TagSlug
{
    public static function of(string $tag): string
    {
        return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($tag)), '-');
    }
}
