<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Site/Edit', [
            'site' => Setting::document('site'),
        ]);
    }

    public function update(SiteRequest $request): RedirectResponse
    {
        Setting::store('site', $request->validated());

        return redirect()->route('admin.site.edit')->with('success', 'Site settings saved.');
    }
}
