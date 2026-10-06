<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, config('app.supported_locales', [])), 404);

        $request->session()->put('locale', $locale);

        // Only bounce back to a page on this site — never to an arbitrary Referer.
        $previous = url()->previous();
        $target = parse_url($previous, PHP_URL_HOST) === $request->getHost() ? $previous : route('home');

        return redirect()->to($target);
    }
}
