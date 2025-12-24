<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            // First user in tenant becomes admin
            $isFirstUser = User::count() === 0;

            // Create user in transaction
            $user = DB::transaction(function () use ($validated, $isFirstUser) {
                return User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => $isFirstUser ? 'admin' : 'customer',
                ]);
            });

            Auth::login($user);

            return redirect()->route('shop.index')
                ->with('success', $isFirstUser
                    ? __('messages.admin_account_created')
                    : __('messages.registration_successful'));
        } catch (\Exception $e) {
            return back()->with('error', __('messages.registration_failed'))->withInput();
        }
    }

    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                // Role-based redirect
                $intendedUrl = auth()->user()->isAdmin()
                    ? route('admin.dashboard')
                    : route('shop.index');

                return redirect()->intended($intendedUrl)
                    ->with('success', __('messages.logged_in_successfully'));
            }

            return back()->with('error', __('messages.invalid_credentials'))
                ->onlyInput('email');
        } catch (\Exception $e) {
            return back()->with('error', __('messages.login_failed'));
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shop.index');
    }
}
