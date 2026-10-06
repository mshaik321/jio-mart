<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\User;
use App\Services\CartService;
use App\Support\CartContext;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // The storefront layout is rendered both as a Blade component and as the
        // Livewire full-page layout, so its category menu comes from a composer.
        View::composer('layouts.storefront', function ($view) {
            $view->with('navCategories', Category::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->with('translations')
                ->orderBy('sort_order')
                ->get());
        });

        // Fold a guest's cart into their customer cart on every web login path
        // (password, registration, Google). The guest cart key survives the
        // session ID regeneration that login performs; see CartContext::sessionId().
        Event::listen(function (Login $event) {
            if ($event->guard === 'web' && $event->user instanceof User && $event->user->customer) {
                app(CartService::class)->mergeGuestCart($event->user->customer, CartContext::sessionId());
            }
        });
    }
}
