<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Support\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class NewsletterController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Honeypot: real visitors never fill this hidden field, so pretend success and store nothing.
        if (filled($request->input('website'))) {
            return response()->json(['message' => 'Subscribed.'], 201);
        }

        $data = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'lang' => ['nullable', Rule::in(Content::LANGUAGES)],
        ]);

        $email = strtolower(trim($data['email']));

        $subscriber = Subscriber::query()->firstOrCreate(
            ['email' => $email],
            ['lang' => $data['lang'] ?? 'en', 'source' => 'blog'],
        );

        $this->forwardToProvider($subscriber);

        return response()->json(['message' => 'Subscribed.'], 201);
    }

    /**
     * Optional: copy each new signup to an external list (Buttondown, Mailchimp, a Zapier hook).
     * A failed forward is logged but never fails the visitor's signup.
     */
    private function forwardToProvider(Subscriber $subscriber): void
    {
        $webhook = config('services.newsletter.webhook');
        if (! $webhook || ! $subscriber->wasRecentlyCreated) {
            return;
        }

        try {
            Http::timeout(5)->post($webhook, [
                'email' => $subscriber->email,
                'lang' => $subscriber->lang,
                'source' => $subscriber->source,
            ])->throw();
        } catch (\Throwable $exception) {
            Log::warning('Newsletter webhook failed', ['message' => $exception->getMessage()]);
        }
    }
}
