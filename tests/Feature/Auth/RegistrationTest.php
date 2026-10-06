<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, string>  $overrides
     */
    private function fillForm(array $overrides = []): Testable
    {
        $fields = array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '9876543210',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides);

        $component = Volt::test('pages.auth.register');

        foreach ($fields as $field => $value) {
            $component->set($field, $value);
        }

        return $component;
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register')
            ->assertSee('Continue with Google')
            ->assertSee('Mobile number')
            ->assertSee('Confirm Password');
    }

    public function test_new_users_can_register(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $component = $this->fillForm();

        $component->call('register');

        $component->assertRedirect(route('home', absolute: false));

        $this->assertAuthenticated();
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertSame('9876543210', $user->phone);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertNotSame('password', $user->password);
        $this->assertTrue($user->hasRole('Customer'));
        $this->assertNotNull($user->customer);
    }

    public function test_registration_normalises_email_and_mobile_number(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->fillForm(['email' => '  Test@Example.COM ', 'phone' => '+91 98765-43210'])->call('register')->assertHasNoErrors();

        $user = User::firstOrFail();
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('9876543210', $user->phone);
    }

    public function test_registration_requires_every_field(): void
    {
        Volt::test('pages.auth.register')
            ->call('register')
            ->assertHasErrors(['name' => 'required', 'email' => 'required', 'phone' => 'required', 'password' => 'required']);

        $this->assertGuest();
    }

    public function test_registration_rejects_an_invalid_mobile_number(): void
    {
        $this->fillForm(['phone' => '12345'])
            ->call('register')
            ->assertHasErrors(['phone' => 'regex'])
            ->assertSee('Enter a valid 10-digit Indian mobile number.');
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->fillForm()
            ->call('register')
            ->assertHasErrors(['email' => 'unique'])
            ->assertSee('An account with this email already exists. Try logging in instead.');

        $this->assertSame(1, User::count());
    }

    public function test_registration_rejects_a_mobile_number_already_registered_in_another_format(): void
    {
        User::factory()->create(['phone' => '9876543210']);

        $this->fillForm(['phone' => '09876543210'])
            ->call('register')
            ->assertHasErrors(['phone' => 'unique'])
            ->assertSee('This mobile number is already registered.');
    }

    public function test_registration_rejects_mismatched_password_confirmation(): void
    {
        $this->fillForm(['password_confirmation' => 'different-password'])
            ->call('register')
            ->assertHasErrors(['password' => 'confirmed']);

        $this->assertGuest();
    }

    public function test_registration_rejects_a_short_password(): void
    {
        $this->fillForm(['password' => 'short', 'password_confirmation' => 'short'])
            ->call('register')
            ->assertHasErrors('password')
            ->assertSee('The password field must be at least 8 characters.');
    }

    public function test_email_is_checked_for_duplicates_as_soon_as_the_field_is_left(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        Volt::test('pages.auth.register')
            ->set('email', 'test@example.com')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_registration_attempts_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->fillForm(['phone' => '12345'])->call('register');
        }

        $this->fillForm()
            ->call('register')
            ->assertHasErrors('email')
            ->assertSee('Too many attempts.');

        $this->assertSame(0, User::count());
    }
}
