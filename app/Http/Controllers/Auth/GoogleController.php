<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

/**
 * "Continue with Google" via Laravel Socialite, signing into the same
 * session guard the email/password login uses. Socialite's OAuth `state`
 * parameter (stored in the session on redirect, checked on callback) stays
 * enabled; this flow is never run stateless.
 */
class GoogleController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        if (! GoogleAuthService::isConfigured()) {
            return $this->backToLogin(__('Google sign-in is not available right now. Please use your email and password.'));
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request, GoogleAuthService $google): RedirectResponse
    {
        if (! GoogleAuthService::isConfigured()) {
            return $this->backToLogin(__('Google sign-in is not available right now. Please use your email and password.'));
        }

        if ($request->filled('error')) {
            return $this->backToLogin($request->query('error') === 'access_denied'
                ? __('Google sign-in was cancelled.')
                : __('Google sign-in failed. Please try again.'));
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return $this->backToLogin(__('Your Google sign-in session expired. Please try again.'));
        } catch (Throwable $e) {
            report($e);

            return $this->backToLogin(__('We could not sign you in with Google. Please try again.'));
        }

        try {
            $user = $google->resolveUser($googleUser);
        } catch (AuthenticationException $e) {
            return $this->backToLogin($e->getMessage());
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home', absolute: false));
    }

    private function backToLogin(string $message): RedirectResponse
    {
        return redirect()->route('login')->with('auth_error', $message);
    }
}
