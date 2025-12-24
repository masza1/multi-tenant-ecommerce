<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

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
        // Load all translations from language files
        $locale = app()->getLocale();
        $translations = [
            'en' => include resource_path('lang/en/messages.php'),
            'id' => include resource_path('lang/id/messages.php'),
        ];
        
        // Convert flat array to dotted format for i18n
        $enMessages = [];
        foreach ($translations['en'] as $key => $value) {
            $enMessages["messages.$key"] = $value;
        }
        
        $idMessages = [];
        foreach ($translations['id'] as $key => $value) {
            $idMessages["messages.$key"] = $value;
        }

        // Try to get user, but handle case where tenancy context is missing
        $user = null;
        $cartCount = 0;
        
        try {
            $user = $request->user();
            if ($user) {
                $cartCount = \App\Models\Cart::where('user_id', $user->id)->sum('quantity');
            }
        } catch (\Exception $e) {
            // If user query fails (e.g., table doesn't exist in current context), return null
            $user = null;
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'cartCount' => $cartCount,
            ],
            'locale' => $locale,
            'translations' => [
                'en' => $enMessages,
                'id' => $idMessages,
            ],
            'tenant_redirect_url' => session('tenant_redirect_url'),
            'flash' => [
                'success' => session()->pull('success'),
                'error' => session()->pull('error'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
