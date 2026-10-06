<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('home', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_navigation_menu_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response
            ->assertOk()
            ->assertSeeVolt('layout.navigation');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('layout.navigation');

        $component->call('logout');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_login_screen_offers_google_forgot_password_and_registration(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee(route('auth.google.redirect'))
            ->assertSee(route('password.request'))
            ->assertSee(route('register'));
    }

    public function test_login_email_is_case_insensitive(): void
    {
        $user = User::factory()->create(['email' => 'shopper@example.com']);

        Volt::test('pages.auth.login')
            ->set('form.email', '  Shopper@Example.com ')
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_deactivated_users_cannot_log_in(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors('form.email')
            ->assertSee('These credentials do not match our records.');

        $this->assertGuest();
    }

    public function test_storefront_logout_route_ends_the_session_but_keeps_the_chosen_language(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['locale' => 'te'])
            ->post('/logout')
            ->assertRedirect('/')
            ->assertSessionHas('locale', 'te');

        $this->assertGuest();
    }
}
