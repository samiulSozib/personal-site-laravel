<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AboutRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/About/Edit', [
            'profile' => Setting::document('profile'),
        ]);
    }

    public function update(AboutRequest $request): RedirectResponse
    {
        Setting::store('profile', $request->validated());

        return redirect()->route('admin.about.edit')->with('success', 'About page saved.');
    }
}
