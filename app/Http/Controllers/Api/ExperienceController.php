<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExperienceController extends Controller
{
    /**
     * The work history, current roles first.
     */
    public function index(): AnonymousResourceCollection
    {
        return ExperienceResource::collection(Experience::query()->ordered()->get());
    }
}
