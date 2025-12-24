<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', [
            '127.0.0.1',
            'localhost',
            'localhost:8000',
            '127.0.0.1:8000',
        ]);

        // If NOT a central domain, forward to tenant shop route
        if (!in_array($host, $centralDomains, true)) {
            // Redirect to shop.index route which is in tenant routes
            return redirect()->route('shop.index');
        }

        return Inertia::render('Landing', [
            'appUrl' => config('app.url'),
        ]);
    }
}
