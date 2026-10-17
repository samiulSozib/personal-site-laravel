<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Resume;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The CV file visitors download, and the profile photo shown on the site.
 */
class ResumeController extends Controller
{
    public function edit(): Response
    {
        $cv = Setting::document('cv');
        $hasCv = Resume::cvExists($cv);

        return Inertia::render('Admin/Resume/Edit', [
            'cv' => $hasCv ? [
                'name' => $cv['name'] ?? 'cv.pdf',
                'size' => (int) ($cv['size'] ?? 0),
                'uploaded_at' => $cv['uploaded_at'] ?? null,
                'downloads' => (int) ($cv['downloads'] ?? 0),
                'url' => route('cv.download'),
            ] : null,
            'photoUrl' => Resume::photoUrl(),
            'downloadName' => Resume::downloadName(),
        ]);
    }

    public function storeCv(Request $request): RedirectResponse
    {
        $request->validate([
            'cv' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $previous = Setting::document('cv');
        $file = $request->file('cv');
        $path = $file->store('cv', Resume::CV_DISK);

        Setting::store('cv', [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'uploaded_at' => now()->toIso8601String(),
            // Keep counting across replacements, so the dashboard shows lifetime downloads.
            'downloads' => (int) ($previous['downloads'] ?? 0),
        ]);

        $this->deleteFile(Resume::CV_DISK, $previous['path'] ?? null, $path);

        return redirect()->route('admin.resume.edit')->with('success', 'CV uploaded.');
    }

    public function destroyCv(): RedirectResponse
    {
        $cv = Setting::document('cv');
        $this->deleteFile(Resume::CV_DISK, $cv['path'] ?? null);
        Setting::store('cv', ['downloads' => (int) ($cv['downloads'] ?? 0)]);

        return redirect()->route('admin.resume.edit')->with('success', 'CV removed.');
    }

    public function storePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=200,min_height=200'],
        ]);

        $previous = Setting::document('photo');
        $path = $request->file('photo')->store('profile', Resume::PHOTO_DISK);
        Setting::store('photo', ['path' => $path, 'uploaded_at' => now()->toIso8601String()]);

        $this->deleteFile(Resume::PHOTO_DISK, $previous['path'] ?? null, $path);

        return redirect()->route('admin.resume.edit')->with('success', 'Photo updated.');
    }

    public function destroyPhoto(): RedirectResponse
    {
        $this->deleteFile(Resume::PHOTO_DISK, Setting::document('photo')['path'] ?? null);
        Setting::store('photo', []);

        return redirect()->route('admin.resume.edit')->with('success', 'Photo removed.');
    }

    private function deleteFile(string $disk, mixed $path, ?string $keep = null): void
    {
        if (is_string($path) && $path !== '' && $path !== $keep) {
            Storage::disk($disk)->delete($path);
        }
    }
}
