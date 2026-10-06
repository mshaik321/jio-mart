<?php

use App\Models\Customer;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['required', 'string', 'regex:'.PhoneNumber::MOBILE_PATTERN, Rule::unique(User::class)],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.unique' => __('An account with this email already exists. Try logging in instead.'),
            'phone.regex' => __('Enter a valid 10-digit Indian mobile number.'),
            'phone.unique' => __('This mobile number is already registered.'),
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => __('full name'),
            'email' => __('email'),
            'phone' => __('mobile number'),
            'password' => __('password'),
        ];
    }

    /**
     * Validate identity fields as the shopper leaves each one, so "email
     * already registered" shows before they've filled in the whole form.
     */
    public function updated(string $property): void
    {
        if (in_array($property, ['name', 'email', 'phone'], true)) {
            $this->normalizeInput();
            $this->validateOnly($property);
        }
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $this->ensureIsNotRateLimited();

        $this->normalizeInput();

        $validated = $this->validate();

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('Customer');
            Customer::create(['user_id' => $user->id]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);
        Session::regenerate();

        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
    }

    private function normalizeInput(): void
    {
        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));
        $this->phone = PhoneNumber::normalize($this->phone) ?? '';
    }

    /**
     * Throttle sign-up attempts per IP: blunts scripted account creation and
     * email/phone enumeration through the "already registered" errors.
     */
    private function ensureIsNotRateLimited(): void
    {
        $key = 'register|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => __('Too many attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]),
            ]);
        }

        RateLimiter::hit($key, 60);
    }
}; ?>

<div x-data>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('Create your account') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Fresh groceries, delivered to your doorstep.') }}</p>
    </div>

    <x-google-auth-button :divider="__('or register with email')" />

    <form wire:submit="register" class="space-y-4">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input wire:model.blur="name" id="name" class="block mt-1 w-full" type="text" name="name" minlength="2" maxlength="255" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model.blur="email" id="email" class="block mt-1 w-full" type="email" name="email" maxlength="255" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Mobile -->
        <div>
            <x-input-label for="phone" :value="__('Mobile number')" />
            <div class="mt-1 flex rounded-md shadow-sm">
                <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300">+91</span>
                <x-text-input wire:model.blur="phone" id="phone" class="block w-full rounded-l-none shadow-none" type="tel" name="phone"
                    inputmode="numeric" pattern="[0-9+\-\s]{10,16}" maxlength="16" required autocomplete="tel-national"
                    :title="__('Enter a valid 10-digit Indian mobile number.')" />
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('We\'ll use this to coordinate your deliveries.') }}</p>
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            minlength="8"
                            required autocomplete="new-password" />

            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('At least 8 characters.') }}</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" minlength="8" required autocomplete="new-password" />

            <p x-cloak x-show="$wire.password_confirmation && $wire.password !== $wire.password_confirmation" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ __('Passwords do not match.') }}
            </p>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full" wire:loading.attr="disabled" wire:target="register">
            {{ __('Create account') }}
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-emerald-700 hover:text-emerald-800 dark:text-emerald-400">{{ __('Log in') }}</a>
    </p>
</div>
