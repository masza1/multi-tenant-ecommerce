<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantRegisterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'lowercase',
                'alpha_dash',
                'max:50',
                Rule::unique('domains', 'domain')->where(function ($query) use ($request) {
                    return $query->where('domain', $request->subdomain . '.localhost');
                }),
            ],
            'email' => ['required', 'email', 'max:255'],
        ]);

        // Create tenant ID (slug dari store name)
        $tenantId = Str::slug($validated['store_name']);

        // Ensure unique tenant ID
        $originalId = $tenantId;
        $counter = 1;
        while (Tenant::where('id', $tenantId)->exists()) {
            $tenantId = $originalId . '-' . $counter;
            $counter++;
        }

        // Create tenant
        $tenant = Tenant::create([
            'id' => $tenantId,
            'name' => $validated['store_name'],
            'owner_email' => $validated['email'],
        ]);

        // Create domain
        $tenant->domains()->create([
            'domain' => $validated['subdomain'] . '.localhost',
        ]);

        // Run migrations for this tenant
        \Artisan::call('tenants:migrate', [
            '--tenants' => [$tenant->id],
        ]);

        return redirect()->away('http://' . $validated['subdomain'] . '.localhost/register')
            ->with('success', 'Toko berhasil dibuat! Silakan buat akun admin.');
    }
}
