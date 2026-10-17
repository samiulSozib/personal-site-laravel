<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * The uploaded CV and profile photo.
 *
 * The CV is kept on the private "local" disk and served by the /cv route, so every download is counted.
 * The photo is kept on the "public" disk (run `php artisan storage:link` once).
 */
final class Resume
{
    public const CV_DISK = 'local';

    public const PHOTO_DISK = 'public';

    /**
     * What the public site needs to show a download button, or null when no CV is uploaded.
     *
     * @return array{url: string, size: int, updatedAt: string}|null
     */
    public static function publicCv(): ?array
    {
        $cv = Setting::document('cv');
        if (! self::cvExists($cv)) {
            return null;
        }

        return [
            'url' => route('cv.download'),
            'size' => (int) ($cv['size'] ?? 0),
            'updatedAt' => (string) ($cv['uploaded_at'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $cv
     */
    public static function cvExists(?array $cv = null): bool
    {
        $cv ??= Setting::document('cv');
        $path = $cv['path'] ?? null;

        return is_string($path) && $path !== '' && Storage::disk(self::CV_DISK)->exists($path);
    }

    public static function photoUrl(): ?string
    {
        $path = Setting::document('photo')['path'] ?? null;
        if (! is_string($path) || $path === '' || ! Storage::disk(self::PHOTO_DISK)->exists($path)) {
            return null;
        }

        return Storage::disk(self::PHOTO_DISK)->url($path);
    }

    /**
     * The file name visitors get, for example "Samiul-Bashar-CV.pdf".
     */
    public static function downloadName(): string
    {
        $name = (string) data_get(Setting::document('site'), 'name.en', config('app.name'));
        $slug = trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-');

        return ($slug !== '' ? $slug : 'CV').'-CV.pdf';
    }
}
