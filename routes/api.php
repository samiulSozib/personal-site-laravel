<?php

use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

// Read-only content for the public site (the newsletter form posts to /api/newsletter). Only published content is returned.
Route::get('site', [SiteController::class, 'show']);
Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{slug}', [PostController::class, 'show']);
Route::get('projects', [ProjectController::class, 'index']);
Route::get('experience', [ExperienceController::class, 'index']);

Route::post('newsletter', [NewsletterController::class, 'store'])->middleware('throttle:10,1');
