<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SubscriberController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Subscribers/Index', [
            'subscribers' => Subscriber::query()->latest()->paginate(25)
                ->through(fn (Subscriber $subscriber) => [
                    'id' => $subscriber->id,
                    'email' => $subscriber->email,
                    'lang' => $subscriber->lang,
                    'source' => $subscriber->source,
                    'created_at' => $subscriber->created_at?->format('Y-m-d'),
                ]),
        ]);
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return redirect()->route('admin.subscribers.index')->with('success', 'Subscriber removed.');
    }
}
