<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->form->email = Str::lower(trim($this->form->email));

        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $default = auth()->user()->isStaff()
            ? route('admin.dashboard', absolute: false)
            : route('home', absolute: false);

        $this->redirectIntended(default: $default, navigate: true);
    }
}; ?>

<div>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('Welcome back') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Log in to continue shopping.') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('auth_error'))
        <div role="alert" class="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
            {{ session('auth_error') }}
        </div>
    @endif


    <form wire:submit="login" class="space-y-4">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" maxlength="255" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between gap-2">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-emerald-700 hover:text-emerald-800 dark:text-emerald-400 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <label for="remember" class="inline-flex items-center">
            <input wire:model="form.remember" id="remember" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-emerald-700 shadow-sm focus:ring-emerald-500 dark:focus:ring-offset-gray-800" name="remember">
            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
        </label>

        <x-primary-button class="w-full" wire:loading.attr="disabled" wire:target="login">
            {{ __('Log in') }}
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        {{ __('New to GroceryHub?') }}
        <a href="{{ route('register') }}" wire:navigate class="font-semibold text-emerald-700 hover:text-emerald-800 dark:text-emerald-400">{{ __('Create an account') }}</a>
    </p>
</div>
