<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        $locale = Session::get('locale');

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        // Signing out shouldn't also switch the shopper's chosen language back.
        if ($locale) {
            Session::put('locale', $locale);
        }
    }
}
