<?php

use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ResumeController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Site\CvController;
use App\Http\Controllers\Site\SeoController;
use App\Http\Controllers\Site\OpenGraphController;
use App\Http\Controllers\Site\PublicSiteController;
use App\Support\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// The public, bilingual site. The language is the first URL segment.
// The bare root goes to Bengali if the browser prefers it, English otherwise.
Route::get('/', function (Request $request) {
    $accepted = strtolower((string) $request->header('Accept-Language'));
    $lang = str_starts_with($accepted, 'bn') || str_contains($accepted, ',bn') ? 'bn' : 'en';

    return redirect("/{$lang}")->header('Vary', 'Accept-Language');
});

Route::get('feed.xml', [SeoController::class, 'feed']);
Route::get('sitemap.xml', [SeoController::class, 'sitemap']);
Route::get('robots.txt', [SeoController::class, 'robots']);
Route::get('cv', CvController::class)->middleware('throttle:30,1')->name('cv.download');

Route::prefix('{lang}')
    ->where(['lang' => implode('|', Content::LANGUAGES)])
    ->group(function () {
        Route::get('/', [PublicSiteController::class, 'home'])->name('site.home');
        Route::get('blog', [PublicSiteController::class, 'blog'])->name('site.blog');
        Route::get('blog/{slug}', [PublicSiteController::class, 'post'])->name('site.post');
        Route::get('blog/{slug}/opengraph-image', [OpenGraphController::class, 'post'])->name('site.post.og');
        Route::get('tags/{tag}', [PublicSiteController::class, 'tag'])->name('site.tag');
        Route::get('experience', [PublicSiteController::class, 'experience'])->name('site.experience');
        Route::get('projects', [PublicSiteController::class, 'projects'])->name('site.projects');
        Route::get('projects/{slug}', [PublicSiteController::class, 'project'])->name('site.project');
        Route::get('about', [PublicSiteController::class, 'about'])->name('site.about');
        Route::get('archive', [PublicSiteController::class, 'archive'])->name('site.archive');
    });

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('posts', PostController::class)->except('show')->parameters(['posts' => 'post']);
    Route::resource('projects', ProjectController::class)->except('show')->parameters(['projects' => 'project']);
    Route::resource('experience', ExperienceController::class)->except('show')->parameters(['experience' => 'experience']);

    Route::get('resume', [ResumeController::class, 'edit'])->name('resume.edit');
    Route::post('resume/cv', [ResumeController::class, 'storeCv'])->name('resume.cv.store');
    Route::delete('resume/cv', [ResumeController::class, 'destroyCv'])->name('resume.cv.destroy');
    Route::post('resume/photo', [ResumeController::class, 'storePhoto'])->name('resume.photo.store');
    Route::delete('resume/photo', [ResumeController::class, 'destroyPhoto'])->name('resume.photo.destroy');

    Route::get('site', [SiteController::class, 'edit'])->name('site.edit');
    Route::put('site', [SiteController::class, 'update'])->name('site.update');

    Route::get('about', [AboutController::class, 'edit'])->name('about.edit');
    Route::put('about', [AboutController::class, 'update'])->name('about.update');

    Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
    Route::delete('subscribers/{subscriber}', [SubscriberController::class, 'destroy'])->name('subscribers.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/account', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/account', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/account', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
