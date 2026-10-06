<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the visitor's chosen language (stored in the session by the
 * language switcher). API clients have no session, so they negotiate via
 * the Accept-Language header instead.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('app.supported_locales', []));

        $locale = $request->hasSession() ? $request->session()->get('locale') : null;

        if (! in_array($locale, $supported, true) && $request->is('api/*') && $request->hasHeader('Accept-Language')) {
            $locale = $request->getPreferredLanguage($supported);
        }

        // Always set it (falling back to the default) so a long-lived worker never leaks one request's language into the next.
        App::setLocale(in_array($locale, $supported, true) ? $locale : config('app.locale'));

        return $next($request);
    }
}
