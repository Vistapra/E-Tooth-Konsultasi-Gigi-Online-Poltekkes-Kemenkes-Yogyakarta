<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        try {
            $request->authenticate();
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Determine the redirect route based on user role
            $redirectRoute = $this->getRedirectRouteForUser($user);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login berhasil!',
                    'redirect' => $redirectRoute
                ]);
            }

            return redirect()->intended($redirectRoute);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login gagal. Silakan cek kembali email dan password Anda.',
                ], 422);
            }

            return back()->withErrors([
                'email' => 'Login gagal. Silakan cek kembali email dan password Anda.',
            ])->onlyInput('email');
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get the appropriate redirect route for the user based on their role.
     *
     * @param  \App\Models\User  $user
     * @return string
     */
    protected function getRedirectRouteForUser($user): string
    {
        if ($user->role === 'owner' || $user->role === 'doctor') {
            return route('dashboard');
        } elseif ($user->role === 'buyer') {
            return route('front.konsultasi');
        } else {
            return RouteServiceProvider::HOME;
        }
    }
}