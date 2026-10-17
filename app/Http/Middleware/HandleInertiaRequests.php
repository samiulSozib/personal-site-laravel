<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'dashboard';

    /**
     * Public pages (/en and /bn) use the frontend (resources/js/frontend).
     * Everything else (admin, login, account) uses the dashboard (resources/js/dashboard).
     */
    public function rootView(Request $request): string
    {
        $isSite = $request->is('en', 'en/*', 'bn', 'bn/*');

        // Only the frontend is rendered on the server (resources/js/frontend/ssr.tsx).
        // The dashboard is client-side only, so it has no server bundle to render from.
        Inertia::disableSsr(! $isSite);

        return $isSite ? 'frontend' : $this->rootView;
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'publicSiteUrl' => url('/en'),
            'siteName' => fn () => data_get(Setting::document('site'), 'name.en', config('app.name')),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }
}
