<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Resume;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    /**
     * The site identity, the About page content, and the CV and photo links.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'site' => Setting::document('site'),
            'profile' => Setting::document('profile'),
            'cv' => Resume::publicCv(),
            'photoUrl' => Resume::photoUrl(),
        ]);
    }
}
