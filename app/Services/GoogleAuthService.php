<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser as SocialiteUser;

/**
 * Resolves the local account for a "Continue with Google" sign-in.
 *
 * 1. A user already linked to this Google account ID signs straight in.
 * 2. Otherwise an existing customer with the same email is linked, but only
 *    when Google asserts the email is verified. If that local account never
 *    verified its own email, its password and remember token are rotated on
 *    link: whoever set that password never proved they own the inbox, and
 *    Google just proved someone does (blocks pre-registration takeover).
 * 3. Otherwise a new, already-verified customer account is created.
 *
 * Staff accounts are never linked or signed in through Google, so a
 * compromised personal Google account can't reach /admin. No Google access
 * or refresh token is stored; the app only needs the identity.
 *
 * Failures are thrown as AuthenticationException with a message that is safe
 * to show the user.
 */
class GoogleAuthService
{
    public static function isConfigured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    public function resolveUser(SocialiteUser $googleUser): User
    {
        $googleId = trim((string) $googleUser->getId());
        $email = Str::lower(trim((string) $googleUser->getEmail()));

        if ($googleId === '') {
            throw new AuthenticationException(__('Google did not return your account details. Please try again.'));
        }

        if ($linked = User::withTrashed()->where('google_id', $googleId)->first()) {
            $this->ensureCanSignIn($linked);

            return $linked;
        }

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new AuthenticationException(__('Your Google account did not share an email address, so we could not sign you in.'));
        }

        if (! $this->emailIsVerified($googleUser)) {
            throw new AuthenticationException(__('Your Google email address is not verified. Verify it with Google, or register with your email and a password.'));
        }

        if ($existing = User::withTrashed()->where('email', $email)->first()) {
            $this->ensureCanSignIn($existing);

            return $this->link($existing, $googleId);
        }

        return $this->createCustomer($googleUser, $googleId, $email);
    }

    private function ensureCanSignIn(User $user): void
    {
        if ($user->trashed() || ! $user->is_active) {
            throw new AuthenticationException(__('This account is not active. Please contact support.'));
        }

        if ($user->isStaff()) {
            throw new AuthenticationException(__('Staff accounts must sign in with email and password.'));
        }
    }

    private function emailIsVerified(SocialiteUser $googleUser): bool
    {
        $raw = $googleUser->getRaw();

        return filter_var($raw['email_verified'] ?? $raw['verified_email'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    private function link(User $user, string $googleId): User
    {
        if ($user->google_id !== null && $user->google_id !== $googleId) {
            throw new AuthenticationException(__('This email is already linked to a different Google account. Please sign in with your email and password.'));
        }

        $attributes = ['google_id' => $googleId];

        if (! $user->hasVerifiedEmail()) {
            $attributes += [
                'email_verified_at' => now(),
                'password' => Str::password(32),
                'remember_token' => Str::random(60),
            ];

            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }
        }

        $user->forceFill($attributes)->save();

        $this->ensureCustomerProfile($user);

        return $user;
    }

    private function createCustomer(SocialiteUser $googleUser, string $googleId, string $email): User
    {
        try {
            $user = DB::transaction(function () use ($googleUser, $googleId, $email) {
                $user = new User;
                $user->forceFill([
                    'name' => Str::limit(trim((string) $googleUser->getName()), 255, '') ?: Str::before($email, '@'),
                    'email' => $email,
                    'google_id' => $googleId,
                    'email_verified_at' => now(),
                    // Unusable random password; "Forgot password" lets the customer set a real one later.
                    'password' => Str::password(32),
                    'is_active' => true,
                ])->save();

                $this->ensureCustomerProfile($user);

                return $user;
            });
        } catch (UniqueConstraintViolationException $e) {
            // A concurrent callback for the same Google account won the race.
            return User::query()->where('google_id', $googleId)->first() ?? throw $e;
        }

        event(new Registered($user));

        return $user;
    }

    private function ensureCustomerProfile(User $user): void
    {
        if ($user->roles()->doesntExist()) {
            $user->assignRole('Customer');
        }

        Customer::query()->firstOrCreate(['user_id' => $user->id]);
    }
}
