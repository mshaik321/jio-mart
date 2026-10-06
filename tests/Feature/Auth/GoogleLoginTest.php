<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use RuntimeException;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-client-id.apps.googleusercontent.com',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => '/auth/google/callback',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function fakeGoogleUser(array $overrides = []): GoogleUser
    {
        $user = GoogleUser::fake(array_merge([
            'id' => 'google-uid-1001',
            'name' => 'Lakshmi Reddy',
            'email' => 'lakshmi@example.com',
            'email_verified' => true,
        ], $overrides));

        Socialite::fake('google', $user);

        return $user;
    }

    public function test_redirect_sends_the_user_to_google_with_oauth_state(): void
    {
        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/auth?', $location);

        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('test-client-id.apps.googleusercontent.com', $query['client_id']);
        $this->assertSame(url('/auth/google/callback'), $query['redirect_uri']);
        $this->assertStringContainsString('email', $query['scope']);
        $this->assertNotEmpty($query['state']);
        $response->assertSessionHas('state', $query['state']);
    }

    public function test_redirect_explains_when_google_sign_in_is_not_configured(): void
    {
        config(['services.google.client_id' => null]);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_error', 'Google sign-in is not available right now. Please use your email and password.');
    }

    public function test_callback_creates_a_verified_customer_account_and_logs_in(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->fakeGoogleUser();

        $this->get(route('auth.google.callback'))->assertRedirect(route('home', absolute: false));

        $user = User::where('email', 'lakshmi@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-uid-1001', $user->google_id);
        $this->assertSame('Lakshmi Reddy', $user->name);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue($user->hasRole('Customer'));
        $this->assertNotNull($user->customer);
    }

    public function test_callback_logs_in_an_existing_google_user_without_creating_a_duplicate(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $existing = Customer::factory()->create()->user;
        $existing->forceFill(['google_id' => 'google-uid-1001'])->save();
        $this->fakeGoogleUser(['email' => $existing->email]);

        $this->get(route('auth.google.callback'))->assertRedirect(route('home', absolute: false));

        $this->assertAuthenticatedAs($existing);
        $this->assertSame(1, User::count());
        $this->assertSame(1, Customer::count());
    }

    public function test_callback_links_google_to_an_existing_verified_account_with_the_same_email(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $existing = Customer::factory()->create()->user;
        $existing->assignRole('Customer');
        $passwordHash = $existing->password;
        $this->fakeGoogleUser(['email' => strtoupper($existing->email)]);

        $this->get(route('auth.google.callback'))->assertRedirect(route('home', absolute: false));

        $this->assertAuthenticatedAs($existing);
        $this->assertSame('google-uid-1001', $existing->fresh()->google_id);
        $this->assertSame($passwordHash, $existing->fresh()->password, 'A verified account keeps its password when Google is linked.');
        $this->assertSame(1, User::count());
    }

    public function test_linking_an_unverified_account_invalidates_the_password_someone_else_may_have_set(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $squatter = Customer::factory()->create()->user;
        $squatter->forceFill(['email_verified_at' => null, 'password' => Hash::make('attacker-password')])->save();
        $this->fakeGoogleUser(['email' => $squatter->email]);

        $this->get(route('auth.google.callback'))->assertRedirect(route('home', absolute: false));

        $linked = $squatter->fresh();
        $this->assertSame('google-uid-1001', $linked->google_id);
        $this->assertTrue($linked->hasVerifiedEmail());
        $this->assertFalse(Hash::check('attacker-password', $linked->password));
    }

    public function test_callback_rejects_a_google_account_whose_email_is_not_verified(): void
    {
        $existing = Customer::factory()->create()->user;
        $this->fakeGoogleUser(['email' => $existing->email, 'email_verified' => false]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_error', 'Your Google email address is not verified. Verify it with Google, or register with your email and a password.');

        $this->assertGuest();
        $this->assertNull($existing->fresh()->google_id);
    }

    public function test_callback_rejects_a_google_profile_without_an_email(): void
    {
        $this->fakeGoogleUser(['email' => null]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_error', 'Your Google account did not share an email address, so we could not sign you in.');

        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_callback_never_links_or_signs_in_staff_accounts(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->fakeGoogleUser(['email' => $admin->email]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_error', 'Staff accounts must sign in with email and password.');

        $this->assertGuest();
        $this->assertNull($admin->fresh()->google_id);
    }

    public function test_callback_rejects_a_deactivated_account(): void
    {
        $user = Customer::factory()->create()->user;
        $user->forceFill(['google_id' => 'google-uid-1001', 'is_active' => false])->save();
        $this->fakeGoogleUser(['email' => $user->email]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_error', 'This account is not active. Please contact support.');

        $this->assertGuest();
    }

    public function test_callback_refuses_an_email_already_linked_to_a_different_google_account(): void
    {
        $user = Customer::factory()->create()->user;
        $user->forceFill(['google_id' => 'another-google-uid'])->save();
        $this->fakeGoogleUser(['email' => $user->email]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_error', 'This email is already linked to a different Google account. Please sign in with your email and password.');

        $this->assertGuest();
        $this->assertSame('another-google-uid', $user->fresh()->google_id);
    }

    public function test_cancelled_google_consent_returns_to_login_with_a_message(): void
    {
        $this->get(route('auth.google.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_error', 'Google sign-in was cancelled.');

        $this->assertGuest();
    }

    public function test_mismatched_oauth_state_is_rejected(): void
    {
        Socialite::fake('google', fn () => throw new InvalidStateException);

        $this->get(route('auth.google.callback', ['code' => 'stolen-code', 'state' => 'forged']))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_error', 'Your Google sign-in session expired. Please try again.');

        $this->assertGuest();
    }

    public function test_unexpected_oauth_errors_are_reported_and_shown_generically(): void
    {
        Exceptions::fake();
        Socialite::fake('google', fn () => throw new RuntimeException('token endpoint unreachable'));

        $this->get(route('auth.google.callback', ['code' => 'abc', 'state' => 'xyz']))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_error', 'We could not sign you in with Google. Please try again.');

        Exceptions::assertReported(RuntimeException::class);
        $this->assertGuest();
    }

    public function test_login_page_shows_the_google_error_message(): void
    {
        $this->withSession(['auth_error' => 'Google sign-in was cancelled.'])
            ->get('/login')
            ->assertOk()
            ->assertSee('Google sign-in was cancelled.');
    }
}
