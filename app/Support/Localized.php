<?php

namespace App\Support;

/**
 * Bilingual values are stored as {"en": ..., "bn": ...}. Bangla is optional for posts,
 * projects and trips, so the public API falls back to English when it is empty.
 */
final class Localized
{
    /**
     * @param  array<string, mixed>|null  $value
     * @return array{en: string, bn: string}
     */
    public static function text(?array $value): array
    {
        $en = (string) ($value['en'] ?? '');
        $bn = (string) ($value['bn'] ?? '');

        return ['en' => $en, 'bn' => $bn !== '' ? $bn : $en];
    }

    /**
     * @param  array<string, mixed>|null  $value
     * @return array{en: array<int, mixed>, bn: array<int, mixed>}
     */
    public static function list(?array $value): array
    {
        $en = array_values((array) ($value['en'] ?? []));
        $bn = array_values((array) ($value['bn'] ?? []));

        return ['en' => $en, 'bn' => $bn !== [] ? $bn : $en];
    }
}
