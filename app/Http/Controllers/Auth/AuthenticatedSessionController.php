<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
    public function store(LoginRequest $request): RedirectResponse
    {
        // Debug para Railway
        \Log::info('Login attempt', [
            'email' => $request->email,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'secure' => $request->isSecure(),
            'headers' => $request->headers->all()
        ]);

        try {
            $request->authenticate();
            
            \Log::info('Authentication successful for: ' . $request->email);
            
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Authentication failed', [
                'email' => $request->email,
                'errors' => $e->errors()
            ]);
            
            // Redirigir de vuelta con errores
            return back()->withErrors($e->errors())->withInput($request->only('email', 'remember'));
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
}
