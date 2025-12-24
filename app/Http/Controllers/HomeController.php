<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Stancl\Tenancy\Tenancy;

class HomeController extends Controller
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

        // Show landing page for central domains
        if (in_array($host, $centralDomains, true)) {
            return Inertia::render('Landing', [
                'appUrl' => config('app.url'),
            ]);
        }

        // For tenant subdomains, forward to shop controller
        $tenantController = new \App\Http\Controllers\Tenant\ShopController();
        return $tenantController->index($request);
    }
}
