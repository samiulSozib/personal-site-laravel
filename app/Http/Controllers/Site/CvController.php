<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Resume;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CvController extends Controller
{
    /**
     * Sends the uploaded CV as a download and counts it.
     */
    public function __invoke(): StreamedResponse
    {
        $cv = Setting::document('cv');
        abort_unless(Resume::cvExists($cv), 404);

        Setting::store('cv', [...$cv, 'downloads' => (int) ($cv['downloads'] ?? 0) + 1]);

        return Storage::disk(Resume::CV_DISK)->download($cv['path'], Resume::downloadName(), [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'no-store',
        ]);
    }
}
